<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Controller;

use Authentication\Controller\Component\AuthenticationComponent;
use Authorization\Controller\Component\AuthorizationComponent;
use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\ORM\Exception\PersistenceFailedException;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\Error;
use Interweber\GraphQL\Classes\AuthenticationService;
use Interweber\GraphQL\Classes\AuthorizationService;
use Interweber\GraphQL\Classes\StaticRequestHandler;
use Interweber\GraphQL\Exception\ValidationException;
use Interweber\GraphQL\Mapper\FrozenDateTypeMapperFactory;
use Psr\Http\Server\MiddlewareInterface;
use TheCodingMachine\GraphQLite\Exceptions\WebonyxErrorHandler;
use TheCodingMachine\GraphQLite\Http\Psr15GraphQLMiddlewareBuilder;
use TheCodingMachine\GraphQLite\SchemaFactory;

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

	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Authentication.Authentication');
		$this->loadComponent('Authorization.Authorization');
		$this->loadComponent('RequestHandler');

		$this->RequestHandler->renderAs($this, 'json');
		$this->RequestHandler->respondAs('json');

		$cache = Cache::pool('graphql');

		$myErrorHandler = function (array $errors, callable $formatter) {
			$errors = array_map(function (\Throwable $error) {
				/** @var \Throwable|null $prev */
				$prev = $error->getPrevious();

				if ($prev instanceof PersistenceFailedException) {
					$aggregate = ValidationException::makeFromEntityError($prev);

					return new Error(message: $aggregate->getMessage(), previous: $aggregate);
				}

				return $error;
			}, $errors);

			return WebonyxErrorHandler::errorHandler($errors, $formatter);
		};

		$builder = new \DI\ContainerBuilder();
		if (!Configure::read('debug')) {
			$builder->enableDefinitionCache();
		}

		$container = $builder->build();

		$factory = new SchemaFactory($cache, $container);
		$factory
			->addControllerNamespace(Configure::read('App.namespace') . '\\GraphQL\\Controller')
			->addTypeNamespace(Configure::read('App.namespace'))
			->addTypeNamespace('Interweber\\GraphQL')
			->addRootTypeMapperFactory(new FrozenDateTypeMapperFactory());

		if (!Configure::read('debug')) {
			$factory->prodMode();
		}

		$schema = $factory
			->setAuthenticationService(new AuthenticationService())
			->setAuthorizationService(new AuthorizationService())
			->createSchema();

		$builder = new Psr15GraphQLMiddlewareBuilder($schema);
		$builder
			->setConfig(
				$builder
					->getConfig()
					->setErrorsHandler($myErrorHandler)
					->setDebugFlag(Configure::read('debug') ? DebugFlag::RETHROW_UNSAFE_EXCEPTIONS : DebugFlag::NONE)
					->setQueryBatching(true)
			)
			->setUrl('/__graphql');

		$this->graphqlMiddleware = $builder->createMiddleware();
	}

	public function handle() {
		$this->Authorization->skipAuthorization();

		$request = $this->getRequest();

		$length = (int) ($request->getHeader('content-length')[0] ?? 0);
		$bodySize = $request->getBody()->getSize();

		if ($bodySize === 0 || $length == 0 || ($bodySize == null && strlen($request->getBody()->getContents()) == 0)) {
			return $this->getResponse()->withStatus(400);
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
