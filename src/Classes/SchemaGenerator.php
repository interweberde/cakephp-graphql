<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventManager;
use DI\Definition\Source\SourceCache;
use Interweber\GraphQL\Mapper\DateTypeMapperFactory;
use Interweber\GraphQL\Mapper\SubscriptionTypeMapperFactory;
use Kcs\ClassFinder\Finder\ComposerFinder;
use TheCodingMachine\GraphQLite\SchemaFactory;

class SchemaGenerator {
	public static function getSchemaFactory(): SchemaFactory {
		$cache = Cache::pool('graphql');

		$builder = new \DI\ContainerBuilder();
		if (!Configure::read('debug')) {
			$builder->addDefinitions(TMP . 'di-classes.php');
			$builder->enableCompilation(TMP . 'di-cache');
			if (SourceCache::isSupported()) {
				$builder->enableDefinitionCache();
			}
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

		EventManager::instance()->dispatch(new Event('onCreateGraphQlSchemaFactory', null, ['factory' => $factory]));

		return $factory;
	}
}
