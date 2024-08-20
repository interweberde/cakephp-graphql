<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Authorization\AuthorizationServiceInterface;
use Cake\Datasource\EntityInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use GraphQL\Type\Definition\ResolveInfo;
use Interweber\GraphQL\Exception\ForbiddenException;
use Interweber\GraphQL\Filter\Filter;
use Interweber\GraphQL\Handler\AuthenticationServiceDataHandler;
use Interweber\GraphQL\Handler\IdentityDataHandler;
use Interweber\GraphQL\Sorter\Sorter;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @template T of \Cake\ORM\Table
 * @template E of \Cake\ORM\Entity
 */
class BaseController {
	use LocatorAwareTrait;
	use LogTrait;

	public string $modelName;

	/**
	 * @var T
	 */
	public Table $model;

	/**
	 * @var AuthenticationServiceDataHandler<T, E>
	 */
	protected AuthenticationServiceDataHandler $dataHandler;

	public function __construct() {
		if (empty($this->modelName)) {
			[, $name] = namespaceSplit(static::class);
			$this->modelName = substr($name, 0, -10);
		}

		$modelClass = $this->modelName;
		/** @var T $table */
		$table = $this->getTableLocator()->get($modelClass);
		$this->model = $table;

		$this->initialize();
	}

	public function initialize(): void {
		$this->dataHandler = new AuthenticationServiceDataHandler($this->model);
	}
}
