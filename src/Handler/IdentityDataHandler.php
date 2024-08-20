<?php

namespace Interweber\GraphQL\Handler;

use Authorization\AuthorizationServiceInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Entity as E;
use Cake\ORM\TableRegistry;
use GraphQL\Type\Definition\ResolveInfo;
use Interweber\GraphQL\Classes\CakeORMPaginationResult;
use Interweber\GraphQL\Classes\QueryOptimizer;
use Interweber\GraphQL\Classes\UserInterface;
use Interweber\GraphQL\Exception\ForbiddenException;
use Interweber\GraphQL\Filter\Filter;
use Interweber\GraphQL\Sorter\Sorter;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @template T of \Cake\ORM\Table
 * @template E of \Cake\ORM\Entity
 * @template-extends DataHandler<T, E>
 */
class IdentityDataHandler extends DataHandler {
	/**
	 * @param class-string<T>|T $model
	 */
	public function __construct($model) {
		parent::__construct($model);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param Filter|null $filter
	 * @param Sorter|null $sorter
	 * @param string $scope
	 * @return CakeORMPaginationResult<E>
	 */
	public function fetchEntities(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		?Filter $filter = null,
		?Sorter $sorter = null,
		string $scope = 'list'
	): CakeORMPaginationResult {
		$query = $this->model->find();
		$query = QueryOptimizer::optimizeQuery($query, $resolveInfo, $user, $scope, true);

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
	public function fetchEntity(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		EntityInterface $entity,
		string $scope = 'show'
	) {
		if ($entity->get('_locale') && $this->model->hasBehavior('Translate')) {
			$this->model->setLocale($entity->get('_locale'));
		}

		return $this->fetchEntityByPK($resolveInfo, $user, $entity->get($this->model->getPrimaryKey()), $scope);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param mixed $id
	 * @param string $scope
	 * @return E
	 * @throws \Exception
	 */
	public function fetchEntityByPK(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		mixed $id,
		string $scope = 'show'
	) {
		$pk = $this->model->getPrimaryKey();

		if (!$pk || is_array($pk)) {
			throw new \Exception('empty or composite pks are unsupported.');
		}

		return $this->fetchEntityByField($resolveInfo, $user, $pk, $id, $scope);
	}

	/**
	 * @param ResolveInfo|null $resolveInfo Note: null is only allowed for internal purposes. Be sure to pass ResolveInfo when using result as GraphQL return.
	 * @param UserInterface $user
	 * @param string $field
	 * @param ID|string|int $id
	 * @param string $scope
	 * @return E
	 */
	public function fetchEntityByField(
		?ResolveInfo $resolveInfo,
		UserInterface $user,
		string $field,
		ID|string|int $id,
		string $scope = 'show'
	) {
		$query = $this->model->find()->where([
			$this->model->aliasField($field) => (string) $id,
		]);

		if ($resolveInfo) {
			$query = QueryOptimizer::optimizeQuery($query, $resolveInfo, $user, $scope);
		}

		/** @var E $entity */
		$entity = $query->firstOrFail();

		return $entity;
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param EntityInterface $entity
	 * @param string $fetchScope
	 * @return E
	 * @throws ForbiddenException
	 */
	public function createEntity(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		EntityInterface $entity,
		string $fetchScope = 'show'
	) {
		if (!$user->can('create', $entity)) {
			throw new ForbiddenException();
		}

		$this->model->saveOrFail($entity);

		return $this->fetchEntity($resolveInfo, $user, $entity, $fetchScope);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param UserInterface $user
	 * @param EntityInterface $entity
	 * @param string $fetchScope
	 * @return E
	 * @throws ForbiddenException
	 */
	public function updateEntity(
		ResolveInfo $resolveInfo,
		UserInterface $user,
		EntityInterface $entity,
		string $fetchScope = 'show'
	) {
		if (!$user->can('update', $entity)) {
			throw new ForbiddenException();
		}

		$this->model->saveOrFail($entity);

		return $this->fetchEntity($resolveInfo, $user, $entity, $fetchScope);
	}

	public function deleteEntity(
		UserInterface $user,
		string $field,
		ID $id
	): bool {
		$entity = $this->fetchEntityByField(null, $user, $field, $id);
		if (!$user->can('delete', $entity)) {
			throw new ForbiddenException();
		}

		return $this->model->deleteOrFail($entity);
	}
}
