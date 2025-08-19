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
use Kcs\ClassFinder\Finder\FinderInterface;
use TheCodingMachine\GraphQLite\SchemaFactory;

class SchemaGenerator {
	public static function makeContainer(): \DI\Container {
		$builder = new \DI\ContainerBuilder();
		if (!Configure::read('debug')) {
			$builder->addDefinitions(TMP . 'di-classes.php');
			$builder->enableCompilation(TMP . 'di-cache');
			if (SourceCache::isSupported()) {
				$builder->enableDefinitionCache();
			}
		}

		return $builder->build();
	}

	public static function getSchemaFactory(\Psr\Container\ContainerInterface $container): SchemaFactory {
		$cache = Cache::pool('graphql');

		$pluginPath = Plugin::classPath('Interweber/GraphQL');
		$path = str_replace(ROOT . DS, '', $pluginPath);

		$result = EventManager::instance()->dispatch(
			new Event('getGraphQlClassNameMapper', null)
		)->getResult();

		if ($result) {
			if (!$result instanceof FinderInterface) {
				throw new \InvalidArgumentException('Event must return FinderInterface instance or null');
			}

			$classNameMapper = $result;
		} else {
			$classNameMapper = new ComposerFinder();
			$classNameMapper
				->notInNamespace('App\\Test\\');
		}

		$factory = new SchemaFactory($cache, $container);
		$factory->setFinder($classNameMapper);
		$factory
			->addNamespace(Configure::read('App.namespace') . '\\GraphQL\\Controller')
			->addNamespace(Configure::read('App.namespace'))
			->addNamespace('Interweber\\GraphQL')
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
