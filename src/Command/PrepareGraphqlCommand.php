<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Command;

use App\Classes\CountryMap;
use App\Classes\Enum\Country;
use App\Classes\Enum\UserTokenType;
use App\Model\Entity\Classification;
use Cake\Cache\Cache;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Finder\FinderInterface;
use ReflectionMethod;
use function DI\autowire;

/**
 * Test command.
 */
class PrepareGraphqlCommand extends Command {
	/**
	 * Hook method for defining this command's option parser.
	 *
	 * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
	 * @return \Cake\Console\ConsoleOptionParser The built parser.
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser = parent::buildOptionParser($parser);

		return $parser;
	}

	protected function buildClassList() {
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

		$content = '';
		foreach ($classNameMapper as $class) {
			if (!$class->isInstantiable()) {
				continue;
			}

			if (!$class->getAttributes()) {
				if (!array_filter($class->getMethods(), fn (\ReflectionMethod $method) => $method->getAttributes())) {
					continue;
				}
			}

			try {
				$builder = new \DI\ContainerBuilder();
				$builder->enableCompilation(TMP, 'TmpContainerCompile');
				$builder->addDefinitions([
					$class->name => autowire(),
				]);
				$container = $builder->build();

				$inst = $container->get($class->name);

				unlink(TMP . 'TmpContainerCompile.php');

				$name = $class->name;
				$name = str_replace('\\', '\\\\', $name);
				$content .= "'$name' => autowire(),\n";
			} catch (\Throwable $t) {
			}
		}

		file_put_contents(
			TMP . 'di-classes.php',
			"<?php
use function DI\\autowire;
return [
$content
];"
		);
	}

	/**
	 * Implement this method with your command's logic.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io) {
		$this->buildClassList();

		$builder = new \DI\ContainerBuilder();
		$builder->addDefinitions(TMP . 'di-classes.php');
		$builder->enableCompilation(TMP . 'di-cache');

		$container = $builder->build();
	}
}
