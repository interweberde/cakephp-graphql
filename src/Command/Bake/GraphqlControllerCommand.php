<?php

namespace Interweber\GraphQL\Command\Bake;

use Bake\Command\SimpleBakeCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Type\EnumType;
use Cake\Database\TypeFactory;
use Cake\ORM\Association;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class GraphqlControllerCommand extends SimpleBakeCommand {
	protected string $pathFragment = 'GraphQL/Controller/';

	public function name(): string {
		return 'graphql_controller';
	}

	public function fileName(string $name): string {
		return sprintf("%sController.php", $name);
	}

	public function template(): string {
		return 'Interweber/GraphQL.graphqlController.php';
	}

	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser = parent::buildOptionParser($parser);

		$parser
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

	public function templateData(Arguments $arguments): array {
		['namespace' => $namespace] = parent::templateData($arguments);

 		$name = $this->_getName($arguments->getArgumentAt(0));
		$name = Inflector::camelize($name);

		$modelName = $this->_modelNameFromKey($name);
		$tableName = $modelName . 'Table';
		$entityName = $this->_entityName($name);
		$singularHumanName = $this->_singularHumanName($name);
		$pluralHumanName = $this->_pluralHumanName($name);
		$singularVariable = $this->_singularName($name);

		$identityVariable = $singularVariable === 'identity' ? 'authIdentity' : 'identity';

		$model = TableRegistry::getTableLocator()->get($modelName);
		$hasTranslate = $model->hasBehavior('Translate');

		$allow = $arguments->getMultipleOption('allow_unauthenticated');
		$allowUnauthenticatedIndex = $allow && in_array('index', $allow);
		$allowUnauthenticatedView = $allow && in_array('view', $allow);
		$allowUnauthenticatedCreate = $allow && in_array('create', $allow);
		$allowUnauthenticatedUpdate = $allow && in_array('update', $allow);
		$allowUnauthenticatedDelete = $allow && in_array('delete', $allow);

		return compact(
			'namespace',
			'modelName',
			'tableName',
			'entityName',
			'singularHumanName',
			'pluralHumanName',
			'singularVariable',
			'identityVariable',
			'hasTranslate',
			'allowUnauthenticatedIndex',
			'allowUnauthenticatedView',
			'allowUnauthenticatedCreate',
			'allowUnauthenticatedUpdate',
			'allowUnauthenticatedDelete',
		);
	}
}
