<?php

namespace Interweber\GraphQL\Handler;

use Cake\ORM\TableRegistry;

/**
 * @template T of \Cake\ORM\Table
 * @template E of \Cake\ORM\Entity
 */
class DataHandler {
	/**
	 * @var T
	 */
	protected \Cake\ORM\Table $model;

	/**
	 * @param class-string<T>|T $model
	 */
	public function __construct($model) {
		if ($model instanceof \Cake\ORM\Table) {
			$this->model = $model;

			return;
		}

		$table = TableRegistry::getTableLocator()->get($model);
		$this->model = $table;
	}
}
