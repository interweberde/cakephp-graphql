<?php

namespace Interweber\GraphQL\Command\Bake;

use Bake\Command\BakeCommand;
use Bake\Utility\TableScanner;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Command for generating all model files.
 */
class GraphqlAllCommand extends BakeCommand {
	use LocatorAwareTrait;

	protected GraphqlCommand $command;

	/**
	 * @inheritDoc
	 */
	public static function defaultName(): string {
		return 'bake graphql all';
	}

	/**
	 * initialize
	 *
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->command = new GraphqlCommand();
	}

	/**
	 * Gets the option parser instance and configures it.
	 *
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to configure
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser = $this->command->buildOptionParser($parser);
		$parser
			->setDescription('Bake all graphql files.')
			->setEpilog('')
			->addOption('allow_unauthenticated', [
				'multiple' => true,
				'choices' => [
					'index',
					'view',
					'create',
					'update',
					'delete',
				]
			]);

		return $parser;
	}

	/**
	 * Execute the command.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return int|null The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$namespace = Configure::read('App.namespace');
		if ($this->plugin) {
			$namespace = $this->_pluginNamespace($this->plugin);
		}

		$plugin = $this->plugin;
		if ($plugin) {
			$plugin .= '.';
		}

		$this->extractCommonProperties($args);
		/** @var \Cake\Database\Connection $connection */
		$connection = ConnectionManager::get($this->connection);
		$scanner = new TableScanner($connection);
		foreach ($scanner->listUnskipped() as $table) {
			$table = $this->_modelNameFromKey($table);

			$this->getTableLocator()->clear();
			$model = $this->getTableLocator()->get($plugin . $table);

			if (!str_starts_with($model::class, $namespace)) {
				$io->info(sprintf("Skipping %s. Class %s is not in namespace %s\\", $plugin . $table, $model::class, $namespace));
				continue;
			}

			$modelArgs = new Arguments([$table], $args->getOptions(), ['name']);
			$this->command->execute($modelArgs, $io);
		}

		return static::CODE_SUCCESS;
	}
}
