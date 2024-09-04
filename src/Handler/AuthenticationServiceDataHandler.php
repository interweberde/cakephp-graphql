<?php

namespace Interweber\GraphQL\Handler;

use Authorization\AuthorizationServiceInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Entity as E;
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
class AuthenticationServiceDataHandler extends DataHandler {
	/**
	 * @param class-string<T>|T $model
	 */
	public function __construct($model) {
		parent::__construct($model);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param AuthorizationServiceInterface $authorizationService
	 * @param UserInterface|null $user
	 * @param Filter|null $filter
	 * @param Sorter|null $sorter
	 * @param string $scope
	 * @param string $finder
	 * @param mixed ...$finderArgs
	 * @return CakeORMPaginationResult<E>
	 */
	public function fetchEntities(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		?Filter $filter = null,
		?Sorter $sorter = null,
		string $scope = 'list',
		string $finder = 'all',
		mixed ...$finderArgs
	): CakeORMPaginationResult {
		$query = $this->model->find($finder, ...$finderArgs);
		$query = QueryOptimizer::optimizeQueryWithOptionalUser($query, $resolveInfo, $authorizationService, $user, $scope, true);

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
	 * @param AuthorizationServiceInterface $authorizationService
	 * @param UserInterface|null $user
	 * @param EntityInterface $entity
	 * @param string $scope
	 * @param string $finder
	 * @param mixed ...$finderArgs
	 * @return E
	 * @throws \Exception
	 */
	public function fetchEntity(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		EntityInterface $entity,
		string $scope = 'show',
		string $finder = 'all',
		mixed ...$finderArgs
	) {
		if ($entity->get('_locale') && $this->model->hasBehavior('Translate')) {
			$this->model->setLocale($entity->get('_locale'));
		}

		return $this->fetchEntityByPK($resolveInfo, $authorizationService, $user, $entity->get($this->model->getPrimaryKey()), $scope, $finder, ...$finderArgs);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param AuthorizationServiceInterface $authorizationService
	 * @param UserInterface|null $user
	 * @param mixed $id
	 * @param string $scope
	 * @param string $finder
	 * @param mixed ...$finderArgs
	 * @return E
	 * @throws \Exception
	 */
	public function fetchEntityByPK(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		mixed $id,
		string $scope = 'show',
		string $finder = 'all',
		mixed ...$finderArgs
	) {
		$pk = $this->model->getPrimaryKey();

		if (!$pk || is_array($pk)) {
			throw new \Exception('empty or composite pks are unsupported.');
		}

		return $this->fetchEntityByField($resolveInfo, $authorizationService, $user, $pk, $id, $scope, $finder, ...$finderArgs);
	}

	/**
	 * @param ResolveInfo|null $resolveInfo Note: null is only allowed for internal purposes. Be sure to pass ResolveInfo when using result as GraphQL return.
	 * @param AuthorizationServiceInterface $authorizationService
	 * @param UserInterface|null $user
	 * @param string $field
	 * @param ID|string|int $id
	 * @param string $scope
	 * @param string $finder
	 * @param mixed ...$finderArgs
	 * @return E
	 */
	public function fetchEntityByField(
		?ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		string $field,
		ID|string|int $id,
		string $scope = 'show',
		string $finder = 'all',
		mixed ...$finderArgs
	) {
		$query = $this->model->find($finder, ...$finderArgs)->where([
			$this->model->aliasField($field) => (string) $id,
		]);

		if ($resolveInfo) {
			$query = QueryOptimizer::optimizeQueryWithOptionalUser($query, $resolveInfo, $authorizationService, $user, $scope);
		}

		/** @var E $entity */
		$entity = $query->firstOrFail();

		return $entity;
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param AuthorizationServiceInterface $authorizationService
	 * @param UserInterface|null $user
	 * @param EntityInterface $entity
	 * @param string $fetchScope
	 * @param string $finder
	 * @param mixed ...$finderArgs
	 * @return E
	 * @throws ForbiddenException
	 */
	public function createEntity(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		EntityInterface $entity,
		string $fetchScope = 'show',
		string $finder = 'all',
		mixed ...$finderArgs
	) {
		if (!$authorizationService->can($user, 'create', $entity)) {
			throw new ForbiddenException();
		}

		$this->model->saveOrFail($entity);

		return $this->fetchEntity($resolveInfo, $authorizationService, $user, $entity, $fetchScope, $finder, ...$finderArgs);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param AuthorizationServiceInterface $authorizationService
	 * @param UserInterface|null $user
	 * @param EntityInterface $entity
	 * @param string $fetchScope
	 * @param string $finder
	 * @param mixed ...$finderArgs
	 * @return E
	 * @throws ForbiddenException
	 */
	public function updateEntity(
		ResolveInfo $resolveInfo,
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		EntityInterface $entity,
		string $fetchScope = 'show',
		string $finder = 'all',
		mixed ...$finderArgs
	) {
		if (!$authorizationService->can($user, 'update', $entity)) {
			throw new ForbiddenException();
		}

		$this->model->saveOrFail($entity);

		return $this->fetchEntity($resolveInfo, $authorizationService, $user, $entity, $fetchScope, $finder, ...$finderArgs);
	}

	public function deleteEntity(
		AuthorizationServiceInterface $authorizationService,
		?UserInterface $user,
		string $field,
		ID $id
	): bool {
		$entity = $this->fetchEntityByField(null, $authorizationService, $user, $field, $id);
		if (!$authorizationService->can($user, 'delete', $entity)) {
			throw new ForbiddenException();
		}

		return $this->model->deleteOrFail($entity);
	}
}
