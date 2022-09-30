<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Cake\Datasource\EntityInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use GraphQL\Type\Definition\ResolveInfo;
use Interweber\GraphQL\Exception\ForbiddenException;
use Interweber\GraphQL\Filter\Filter;
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
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param Filter|null $filter
	 * @param Sorter|null $sorter
	 * @return CakeORMPaginationResult<E>
	 */
	protected function _fetchEntities(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		?Filter $filter = null,
		?Sorter $sorter = null
	): CakeORMPaginationResult {
		$query = $this->model->find();
		$query = QueryOptimizer::optimizeQuery($query, $resolveInfo, $user, 'list', true);

		if ($filter) {
			$query = $filter->apply($query);
		}

		if ($sorter) {
			$query = $sorter->apply($query);
		}

		return new CakeORMPaginationResult($query);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param E|EntityInterface $entity
	 * @return E
	 * @throws \Exception
	 */
	protected function _fetchEntity(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		EntityInterface $entity
	) {
		return $this->_fetchEntityByPK($resolveInfo, $user, $entity->get($this->model->getPrimaryKey()));
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param mixed $id
	 * @return E
	 * @throws \Exception
	 */
	protected function _fetchEntityByPK(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		mixed $id
	) {
		$pk = $this->model->getPrimaryKey();

		if (!$pk || is_array($pk)) {
			throw new \Exception('empty or composite pks are unsupported.');
		}

		$query = $this->model->find()->where([
			$this->model->aliasField($pk) => (string) $id,
		]);

		$query = QueryOptimizer::optimizeQuery($query, $resolveInfo, $user, 'show');

		/** @var E $entity */
		$entity = $query->firstOrFail();

		return $entity;
	}

	/**
	 * @param ResolveInfo|null $resolveInfo Note: null is only allowed for internal purposes. Be sure to pass ResolveInfo when using result as GraphQL return.
	 * @param UserInterface $user
	 * @param string $field
	 * @param ID $id
	 * @return E
	 */
	protected function _fetchEntityByField(
		?ResolveInfo $resolveInfo,
		UserInterface $user,
		string $field,
		ID $id
	) {
		$query = $this->model->find()->where([
			$this->model->aliasField($field) => (string) $id,
		]);

		if ($resolveInfo) {
			$query = QueryOptimizer::optimizeQuery($query, $resolveInfo, $user, 'show');
		}

		/** @var E $entity */
		$entity = $query->firstOrFail();

		return $entity;
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param E $entity
	 * @return E
	 * @throws ForbiddenException
	 */
	protected function _createEntity(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		EntityInterface $entity
	) {
		if (!$user->can('create', $entity)) {
			throw new ForbiddenException();
		}

		$this->model->saveOrFail($entity);

		return $this->_fetchEntity($resolveInfo, $user, $entity);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param E $entity
	 * @return E
	 * @throws ForbiddenException
	 */
	protected function _updateEntity(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		EntityInterface $entity
	) {
		if (!$user->can('update', $entity)) {
			throw new ForbiddenException();
		}

		$this->model->saveOrFail($entity);

		return $this->_fetchEntity($resolveInfo, $user, $entity);
	}

	protected function _deleteEntity(
		UserInterface $user,
		string $field,
		ID $id
	): bool {
		$entity = $this->_fetchEntityByField(null, $user, $field, $id);
		if (!$user->can('delete', $entity)) {
			throw new ForbiddenException();
		}

		return $this->model->deleteOrFail($entity);
	}
}
