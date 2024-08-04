<?php

namespace Interweber\GraphQL\Command\Bake;

use Bake\Command\SimpleBakeCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\Association;
use Cake\Utility\Inflector;

class GraphqlSorterCommand extends SimpleBakeCommand {
	protected string $pathFragment = 'GraphQL/Sorter/';

	public function name(): string {
		return 'graphql_sorter';
	}

	public function fileName(string $name): string {
		return '';
	}

	public function template(): string {
		return '';
	}

	protected function bake(string $name, Arguments $args, ConsoleIo $io): void
    {
        $contents = $this->createTemplateRenderer()
            ->set('name', $name)
            ->set($this->templateData($args))
            ->generate('Interweber/GraphQL.graphqlSorter.php');

        $filename = $this->getPath($args) . sprintf("%sSorter.php", $name);
        $io->createFile($filename, $contents, $this->force);

		$contents = $this->createTemplateRenderer()
            ->set('name', $name)
            ->set($this->templateData($args))
            ->generate('Interweber/GraphQL.graphqlSorterFields.php');

        $filename = $this->getPath($args) . sprintf("%sSorterFields.php", $name);
        $io->createFile($filename, $contents, $this->force);

        $emptyFile = $this->getPath($args) . '.gitkeep';
        $this->deleteEmptyFile($emptyFile, $io);
    }

	public function templateData(Arguments $arguments): array {
		['namespace' => $namespace] = parent::templateData($arguments);

 		$name = $this->_getName($arguments->getArgumentAt(0));
		$name = Inflector::camelize($name);

		$currentModelName = $name;
		$plugin = $this->plugin;
		if ($plugin) {
			$plugin .= '.';
		}

		if ($this->getTableLocator()->exists($plugin . $currentModelName)) {
			$modelObj = $this->getTableLocator()->get($plugin . $currentModelName);
		} else {
			$modelObj = $this->getTableLocator()->get($plugin . $currentModelName, [
				'connectionName' => $this->connection,
			]);
		}

		$pluralName = $this->_modelNameFromKey($currentModelName);
		$singularName = $this->_entityName($currentModelName);
		$singularHumanName = $this->_singularHumanName($name);
		$pluralHumanName = $this->_variableName($name);

		$pk = $modelObj->getPrimaryKey();
		$schema = $modelObj->getSchema();
		$fields = $schema->columns();

		$assocs = $modelObj->associations();

		$assocKeys = collection($assocs)
			->indexBy(fn (Association $assoc) => $assoc->getName())
			->map(fn (Association $assoc) => $assoc instanceof Association\HasMany || $assoc instanceof Association\BelongsToMany ? $assoc->getBindingKey() : $assoc->getForeignKey())
			->filter(fn ($k) => $k !== 'id')
			->toArray();

		$fields = array_filter($fields, function ($field) use ($pk, $schema, $assocKeys) {
			// we do not want the primary key as sort field
			if ($field === $pk) {
				return false;
			}

			// we do not want foreign_keys as sort fields
			if (in_array($field, $assocKeys)) {
				return false;
			}

			$info = $schema->getColumn($field);
			$type = $info['type'];

			// exclude types that should not be sorted by default
			if (
				$type === 'boolean'
				|| $type === 'binary'
				|| $type === 'text'
				|| $type === 'json'
				|| $type === 'uuid'
				|| $type === 'binaryuuid'
				|| str_starts_with($type, 'enum')
			) {
				return false;
			}

			return true;
		});

		return compact(
			'namespace',
			'fields',
			'pluralName',
			'singularName',
			'singularHumanName',
			'pluralHumanName',
		);
	}
}
