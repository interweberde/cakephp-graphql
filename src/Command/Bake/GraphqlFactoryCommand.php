<?php

namespace Interweber\GraphQL\Command\Bake;

use Bake\Command\SimpleBakeCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Association;
use Cake\ORM\Behavior\TreeBehavior;
use Cake\Utility\Inflector;

class GraphqlFactoryCommand extends SimpleBakeCommand {
	protected string $pathFragment = 'GraphQL/Factory/';

	public function name(): string {
		return 'graphql_factory';
	}

	public function fileName(string $name): string {
		return sprintf("%sFactory.php", $this->_entityName($name));
	}

	public function template(): string {
		return 'Interweber/GraphQL.graphqlFactory.php';
	}

	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser = parent::buildOptionParser($parser);

		$parser
			->addOption('allow_unauthenticated', [
				'multiple' => true,
				'choices' => [
					'create',
					'update',
				]
			]);

		return $parser;
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

		$modelName = $this->_modelNameFromKey($name);
		$tableName = $modelName . 'Table';
		$entityName = $this->_entityName($name);
		$singularHumanName = $this->_singularHumanName($name);
		$pluralHumanName = $this->_pluralHumanName($name);
		$singularVariable = $this->_singularName($name);

		$entityObj = $modelObj->newEmptyEntity();
		$accessible = $entityObj->getAccessible();
		$pk = $modelObj->getPrimaryKey();
		$schema = $modelObj->getSchema();

		$assocs = collection($modelObj->associations());

		$assocKeys = $assocs
			->indexBy(fn (Association $assoc) => $assoc->getName())
			->map(fn (Association $assoc) => $assoc instanceof Association\HasMany || $assoc instanceof Association\BelongsToMany ? $assoc->getBindingKey() : $assoc->getForeignKey())
			->filter(fn ($k) => $k !== 'id')
			->toArray();

		$skipFields = [
			$pk,
			'uuid',
			'created',
			'modified',
			'deleted',
		];

		if ($modelObj->hasBehavior('Tree')) {
			$treeBehavior = $modelObj->getBehavior('Tree');

			if ($treeBehavior instanceof TreeBehavior) {
				$config = $treeBehavior->getConfig();

				$skipFields[] = $config['left'];
				$skipFields[] = $config['right'];

				$level = $config['level'] ?? null;
				if ($level) {
					$skipFields[] = $level;
				}
			}
		}

		$properties = [];
        foreach ($schema->columns() as $column) {
			if (in_array($column, $skipFields)) {
				continue;
			}

			if (!($accessible[$column] ?? false)) {
				continue;
			}

            $columnSchema = $schema->getColumn($column);

			$type = $columnSchema['type'];

			$assoc = array_search($column, $assocKeys) ?: null;

            $properties[$column] = [
                'kind' => $assoc ? 'assoc' : 'column',
                'type' => $assoc ? 'ID' : $type,
                'null' => $columnSchema['null'],
				'assoc' => $assoc,
            ];
        }

		$hasTranslate = $modelObj->hasBehavior('Translate');

		$allow = $arguments->getMultipleOption('allow_unauthenticated');
		$allowUnauthenticatedCreate = $allow && in_array('create', $allow);
		$allowUnauthenticatedUpdate = $allow && in_array('update', $allow);

		return compact(
			'namespace',
			'properties',
			'hasTranslate',
			'modelName',
			'tableName',
			'entityName',
			'singularHumanName',
			'pluralHumanName',
			'singularVariable',
			'allowUnauthenticatedCreate',
			'allowUnauthenticatedUpdate',
		);
	}
}
