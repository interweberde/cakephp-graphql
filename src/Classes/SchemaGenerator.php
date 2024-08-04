<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Interweber\GraphQL\Mapper\DateTypeMapperFactory;
use Interweber\GraphQL\Mapper\SubscriptionTypeMapperFactory;
use Kcs\ClassFinder\Finder\ComposerFinder;
use TheCodingMachine\GraphQLite\SchemaFactory;

class SchemaGenerator {
	public static function getSchemaFactory(): SchemaFactory {
		$cache = Cache::pool('graphql');

		$builder = new \DI\ContainerBuilder();
		if (!Configure::read('debug')) {
			$builder->enableCompilation(TMP . 'di-cache');
			$builder->enableDefinitionCache();
		}

		$container = $builder->build();

		$pluginPath = Plugin::classPath('Interweber/GraphQL');
		$path = str_replace(ROOT . DS, '', $pluginPath);

		$classNameMapper = new ComposerFinder();
		$classNameMapper
			->notInNamespace('App\\Test\\');

		$factory = new SchemaFactory($cache, $container);
		$factory->setFinder($classNameMapper);
		$factory
			->addControllerNamespace(Configure::read('App.namespace') . '\\GraphQL\\Controller')
			->addTypeNamespace(Configure::read('App.namespace'))
			->addTypeNamespace('Interweber\\GraphQL')
			->addRootTypeMapperFactory(new DateTypeMapperFactory())
			->addRootTypeMapperFactory(new SubscriptionTypeMapperFactory());

		if (!Configure::read('debug')) {
			$factory->prodMode();
		}

		$factory->addParameterMiddleware(new CakePHPParameterMiddleware());

		return $factory;
	}
}
