<?php

namespace Interweber\GraphQL\Command\Bake;

use Bake\Command\SimpleBakeCommand;
use Cake\Console\Arguments;
use Cake\Database\Type\EnumType;
use Cake\Database\TypeFactory;
use Cake\ORM\Association;
use Cake\ORM\Behavior\TranslateBehavior;
use Cake\ORM\Behavior\TreeBehavior;
use Cake\Utility\Inflector;

class GraphqlFilterCommand extends SimpleBakeCommand {
	protected string $pathFragment = 'GraphQL/Filter/';

	public function name(): string {
		return 'graphql_filter';
	}

	public function fileName(string $name): string {
		return sprintf("%sFilter.php", $name);
	}

	public function template(): string {
		return 'Interweber/GraphQL.graphqlFilter.php';
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

		$hasUuid = $pk !== 'uuid' && $schema->hasColumn('uuid');
		if ($hasUuid) {
			// we'll use uuid as id.
			$fields = array_filter($fields, fn($f) => $f !== 'uuid');
		}

		$assocs = $modelObj->associations();

		$skipFields = collection($assocs)
			->indexBy(fn (Association $assoc) => $assoc->getName())
			->map(fn (Association $assoc) => $assoc instanceof Association\HasMany || $assoc instanceof Association\BelongsToMany ? $assoc->getBindingKey() : $assoc->getForeignKey())
			->filter(fn ($k) => $k !== 'id')
			->toArray();

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

				$assocs = collection($assocs)->filter(
					fn (Association $assoc) =>
						$assoc->getName() !== 'Parent' . $pluralName
						&& $assoc->getName() !== 'Child' . $pluralName
				);
			}
		}

		if ($modelObj->hasBehavior('Translate')) {
			$Translate = $modelObj->getBehavior('Translate');

			if ($Translate instanceof TranslateBehavior) {
				$TranslateAssocAlias = $Translate->getStrategy()->getTranslationTable()->getAlias();
				$assocs = collection($assocs)->filter(
					fn (Association $assoc) => $assoc->getName() !== $TranslateAssocAlias
				);

			}
		}

		$fields = array_filter($fields, fn ($field) => !in_array($field, $skipFields));

		$fields = array_map(function ($field) use ($pk, $schema) {
			$info = $schema->getColumn($field);

			$type = $info['type'];

			$type = match ($type) {
				'integer' => 'Int',
				'float', 'double', 'decimal' => 'Float',
				'boolean' => 'Boolean',
				'char', 'string', 'text' => 'String',
				default => 'Null',
			};

			$ret = [
				'type' => $type,
				'name' => $field,
			];

			if (
				$info['type'] === 'date'
				|| $info['type'] === 'time'
				|| str_starts_with($info['type'], 'datetime')
				|| str_starts_with($info['type'], 'timestamp')
			) {
				$ret['type'] = 'Date';
			} elseif (str_starts_with($info['type'], 'enum')) {
				$ret['type'] = 'Enum';
				/** @var EnumType $type */
				$type = TypeFactory::build($info['type']);
				$parts = explode('\\', $type->getEnumClassName());
				$ret['enum'] = array_pop($parts);
			}

			if ($field === $pk) {
				$ret['type'] = 'primary';
			}

			return $ret;
		}, $fields);

		foreach ($assocs as $association) {
			$fields[] = [
				'type' => 'relation',
				'name' => Inflector::underscore($association->getName()),
				'relation_name' => $association->getName(),
			];
		}

		return compact(
			'namespace',
			'fields',
			'pluralName',
			'singularName',
			'singularHumanName',
			'pluralHumanName',
			'hasUuid',
		);
	}
}
