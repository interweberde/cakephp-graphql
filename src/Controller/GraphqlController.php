<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Controller;

use Authentication\Controller\Component\AuthenticationComponent;
use Authorization\Controller\Component\AuthorizationComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\ORM\Exception\PersistenceFailedException;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\Error;
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
	protected $modelClass = null;

	/**
	 * @var \Psr\Http\Server\MiddlewareInterface
	 */
	protected MiddlewareInterface $graphqlMiddleware;

	protected Schema $schema;

	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Authentication.Authentication');
		$this->loadComponent('Authorization.Authorization');
		$this->loadComponent('RequestHandler');

		$this->RequestHandler->renderAs($this, 'json');
		$this->RequestHandler->respondAs('json');

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
							null,
							$error->getExtensions()
						);
					} else {
						$err = new \Interweber\GraphQL\Exception\RecordNotFoundException();
					}

					return $err;
				}

				return $error;
			}, $errors);

			return WebonyxErrorHandler::errorHandler($errors, $formatter);
		};

		$schema = SchemaGenerator::generateSchema();

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

		$builder
			->setUrl('/__graphql');

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

		$handler = new StaticRequestHandler($this->getResponse());
		$graphqlResponse = $this->graphqlMiddleware->process($request, $handler);

		// cake requires us to return a Cake Response.
		// so we have to transfer every attribute over to one.
		$response = $this->getResponse()
			->withStatus($graphqlResponse->getStatusCode(), $graphqlResponse->getReasonPhrase())
			->withBody($graphqlResponse->getBody())
			->withProtocolVersion($graphqlResponse->getProtocolVersion());

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

		foreach ($graphqlResponse->getHeaders() as $key => $value) {
			$response = $response->withHeader($key, $value);
		}

		return $response;
	}
}
