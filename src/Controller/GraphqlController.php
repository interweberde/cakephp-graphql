<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Controller;

use Authentication\Controller\Component\AuthenticationComponent;
use Authorization\AuthorizationServiceInterface;
use Authorization\Controller\Component\AuthorizationComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\Http\ResponseFactory;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\View\JsonView;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\Error;
use Interweber\GraphQL\Classes\AuthenticationService;
use Interweber\GraphQL\Classes\AuthorizationService;
use Interweber\GraphQL\Classes\CakePHPRequestContext;
use Interweber\GraphQL\Classes\SchemaGenerator;
use Interweber\GraphQL\Classes\StaticRequestHandler;
use Interweber\GraphQL\Exception\ValidationException;
use Psr\Http\Server\MiddlewareInterface;
use TheCodingMachine\GraphQLite\Exceptions\WebonyxErrorHandler;
use TheCodingMachine\GraphQLite\Http\Psr15GraphQLMiddlewareBuilder;
use TheCodingMachine\GraphQLite\Schema;

/**
 * @property AuthenticationComponent $Authentication
 * @property AuthorizationComponent $Authorization
 */
class GraphqlController extends Controller {
	protected ?string $modelClass = null;

	/**
	 * @var \Psr\Http\Server\MiddlewareInterface
	 */
	protected MiddlewareInterface $graphqlMiddleware;

	protected Schema $schema;

	public function viewClasses(): array
    {
        return [JsonView::class];
    }

	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Authentication.Authentication');
		$this->loadComponent('Authorization.Authorization');

		if (Configure::read('GraphQL.allow_unauthenticated', false)) {
			$this->Authentication->allowUnauthenticated(['handle']);
		}

		/**
		 * @param Error[] $errors
		 * @param callable(\Throwable): array{ message: string, locations?: array<int, array{line: int, column: int}>, path?: array<int, int|string>, extensions?: array<string, mixed> } $formatter
		 * @return array<array{ message: string, locations?: array<int, array{line: int, column: int}>, path?: array<int, int|string>, extensions?: array<string, mixed> }>
		 */
		$myErrorHandler = function (array $errors, callable $formatter) {
			$errors = array_map(function (\Throwable $error) {
				$event = $this->getEventManager()->dispatch(
					new Event('onGraphqlError', $this, ['error' => $error])
				);

				$res = $event->getResult();
				if ($res instanceof \Throwable) {
					return $res;
				}

				/** @var \Throwable|null $prev */
				$prev = $error->getPrevious();

				if ($prev instanceof PersistenceFailedException) {
					$aggregate = ValidationException::makeFromEntityError($prev);

					return new Error(message: $aggregate->getMessage(), previous: $aggregate);
				}

				if ($prev instanceof RecordNotFoundException) {
					if ($error instanceof Error) {
						$err = new \Interweber\GraphQL\Exception\RecordNotFoundException(
							'Record not found',
							$error->nodes,
							$error->getSource(),
							$error->getPositions(),
							$error->path,
							$prev,
							$error->getExtensions()
						);
					} else {
						$err = new \Interweber\GraphQL\Exception\RecordNotFoundException(previous: $prev);
					}

					return $err;
				}

				if ($prev instanceof ForbiddenException) {
					return new \Interweber\GraphQL\Exception\ForbiddenException($prev->getMessage(), $prev);
				}

				return $error;
			}, $errors);

			return WebonyxErrorHandler::errorHandler($errors, $formatter);
		};

		$container = SchemaGenerator::makeContainer();
		$container->set(AuthorizationServiceInterface::class, $this->request->getAttribute('authorization'));

		$schemaFactory = SchemaGenerator::getSchemaFactory($container);

		$schemaFactory
			->setAuthenticationService(new AuthenticationService($this->request))
			->setAuthorizationService(new AuthorizationService());

		$this->getEventManager()->dispatch(new Event('beforeCreateGraphQlSchema', $this, ['factory' => $schemaFactory]));

		$schema = $schemaFactory->createSchema();

		$builder = new Psr15GraphQLMiddlewareBuilder($schema);

		$config = $builder->getConfig();

		// psalm does not play nicely with phpstan-types used there
		/** @psalm-suppress InvalidArgument */
		$config->setErrorsHandler($myErrorHandler);

		$config
			->setDebugFlag(
				Configure::read('debug')
					? DebugFlag::RETHROW_UNSAFE_EXCEPTIONS
					: DebugFlag::NONE
			)
			->setQueryBatching(true);

		$config->setContext(
			new CakePHPRequestContext($this->request)
		);

		$builder
			->setUrl('/__graphql');

		$builder->setResponseFactory(new ResponseFactory());

		$this->getEventManager()->dispatch(new Event('onCreateGraphQlBuilder', $this, ['builder' => $builder]));

		$this->graphqlMiddleware = $builder->createMiddleware();
	}

	public function handle() {
		$this->Authorization->skipAuthorization();

		$request = $this->getRequest();

		$bodySize = $request->getBody()->getSize();

		if ($bodySize === 0 || ($bodySize == null && strlen($request->getBody()->getContents()) == 0)) {
			return $this->getResponse()->withStatus(400);
		}

		if (!$request->contentType()) {
			$request = $request->withHeader('Content-Type', 'application/json');
		}

		$request->getBody()->rewind();

		$handler = new StaticRequestHandler($this->getResponse());
		$response = $this->graphqlMiddleware->process($request, $handler);

		assert($response instanceof Response);

		if (
			$response->getStatusCode() === 400
			|| $response->getStatusCode() === 404
			|| $response->getStatusCode() === 500
		) {
			// HACK: apollo only reads graphql errors when status code is 400.
			// we will need this until the graphql-over-http-spec is final, and webonyx/php-graphql is adopted.
			// https://github.com/APIs-guru/graphql-over-http

			$response = $response->withStatus(200);
		}

		return $response;
	}
}
