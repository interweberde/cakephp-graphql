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
	 * @param string $scope
	 * @return CakeORMPaginationResult<E>
	 */
	protected function _fetchEntities(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		?Filter $filter = null,
		?Sorter $sorter = null,
		string $scope = 'list'
	): CakeORMPaginationResult {
		$query = $this->model->find();
		$query = QueryOptimizer::optimizeQuery($query, $resolveInfo, $authorizationService, $user, $scope, true);

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
	 * @param EntityInterface $entity
	 * @param string $scope
	 * @return E
	 * @throws \Exception
	 */
	protected function _fetchEntity(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		EntityInterface $entity,
		string $scope = 'show'
	) {
		if ($entity->get('_locale') && $this->model->hasBehavior('Translate')) {
			$this->model->setLocale($entity->get('_locale'));
		}

		return $this->_fetchEntityByPK($resolveInfo, $authorizationService, $user, $entity->get($this->model->getPrimaryKey()), $scope);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param mixed $id
	 * @param string $scope
	 * @return E
	 * @throws \Exception
	 */
	protected function _fetchEntityByPK(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		mixed $id,
		string $scope = 'show'
	) {
		$pk = $this->model->getPrimaryKey();

		if (!$pk || is_array($pk)) {
			throw new \Exception('empty or composite pks are unsupported.');
		}

		return $this->_fetchEntityByField($resolveInfo, $authorizationService, $user, $pk, $id, $scope);
	}

	/**
	 * @param ResolveInfo|null $resolveInfo Note: null is only allowed for internal purposes. Be sure to pass ResolveInfo when using result as GraphQL return.
	 * @param UserInterface $user
	 * @param string $field
	 * @param ID|string|int $id
	 * @param string $scope
	 * @return E
	 */
	protected function _fetchEntityByField(
		?ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		string $field,
		ID|string|int $id,
		string $scope = 'show'
	) {
		$query = $this->model->find()->where([
			$this->model->aliasField($field) => (string) $id,
		]);

		if ($resolveInfo) {
			$query = QueryOptimizer::optimizeQuery($query, $resolveInfo, $authorizationService, $user, $scope);
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
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		EntityInterface $entity,
		string $fetchScope = 'show'
	) {
		if (!$authorizationService->can($user, 'create', $entity)) {
			throw new ForbiddenException();
		}

		$this->model->saveOrFail($entity);

		return $this->_fetchEntity($resolveInfo, $authorizationService, $user, $entity, $fetchScope);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param AuthorizationServiceInterface $authorizationService
	 * @param UserInterface $user
	 * @param EntityInterface $entity
	 * @param string $fetchScope
	 * @return E
	 * @throws ForbiddenException
	 */
	protected function _updateEntity(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		EntityInterface $entity,
		string $fetchScope = 'show'
	) {
		if (!$authorizationService->can($user, 'update', $entity)) {
			throw new ForbiddenException();
		}

		$this->model->saveOrFail($entity);

		return $this->_fetchEntity($resolveInfo, $authorizationService, $user, $entity, $fetchScope);
	}

	protected function _deleteEntity(
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		string $field,
		ID $id
	): bool {
		$entity = $this->_fetchEntityByField(null, $authorizationService, $user, $field, $id);
		if (!$authorizationService->can($user, 'delete', $entity)) {
			throw new ForbiddenException();
		}

		return $this->model->deleteOrFail($entity);
	}
}
