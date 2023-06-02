<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Interweber\GraphQL\Mapper\FrozenDateTypeMapperFactory;
use Interweber\GraphQL\Mapper\SubscriptionTypeMapperFactory;
use Mouf\Composer\ClassNameMapper;
use TheCodingMachine\GraphQLite\SchemaFactory;

class SchemaGenerator {
	public static function generateSchema() {
		$cache = Cache::pool('graphql');

		$builder = new \DI\ContainerBuilder();
		if (!Configure::read('debug')) {
			$builder->enableDefinitionCache();
		}

		$container = $builder->build();

		$pluginPath = Plugin::classPath('Interweber/GraphQL');
		$path = str_replace(ROOT, '', $pluginPath);

		$classNameMapper = ClassNameMapper::createFromComposerFile(null, null, false);
		$classNameMapper->registerPsr4Namespace('Interweber\\GraphQL', $path);

		$factory = new SchemaFactory($cache, $container);
		$factory->setClassNameMapper($classNameMapper);
		$factory
			->addControllerNamespace(Configure::read('App.namespace') . '\\GraphQL\\Controller')
			->addTypeNamespace(Configure::read('App.namespace'))
			->addTypeNamespace('Interweber\\GraphQL')
			->addRootTypeMapperFactory(new FrozenDateTypeMapperFactory())
			->addRootTypeMapperFactory(new SubscriptionTypeMapperFactory());

		if (!Configure::read('debug')) {
			$factory->prodMode();
		}

		return $factory
			->setAuthenticationService(new AuthenticationService())
			->setAuthorizationService(new AuthorizationService())
			->createSchema();
	}
}
