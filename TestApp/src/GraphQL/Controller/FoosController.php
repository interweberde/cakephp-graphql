<?php
declare(strict_types=1);

namespace TestApp\GraphQL\Controller;

use GraphQL\Type\Definition\ResolveInfo;
use Interweber\GraphQL\Classes\BaseController;
use Interweber\GraphQL\Classes\CakeORMPaginationResult;
use TestApp\GraphQL\Filter\FoosFilter;
use TestApp\GraphQL\Sorter\FoosSorter;
use TestApp\Model\Entity\Foo;
use TestApp\Model\Entity\User;
use TestApp\Model\Table\FoosTable;
use TheCodingMachine\GraphQLite\Annotations\InjectUser;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @template-extends BaseController<FoosTable, Foo>
 */
class FoosController extends BaseController {
	#[Query]
	public function getFoo(
		ResolveInfo $resolveInfo,
		#[InjectUser]
		User $identity,
		ID $id
	): Foo {
		return $this->_fetchEntityByPK($resolveInfo, $identity, $id);
	}

	/**
	 * @param ResolveInfo $resolveInfo
	 * @param User $identity
	 * @param FoosFilter|null $filter
	 * @param FoosSorter|null $sorter
	 * @return Foo[]|CakeORMPaginationResult
	 * @psalm-return CakeORMPaginationResult<Foo>
	 */
	#[Query]
	public function getFoos(
		ResolveInfo $resolveInfo,
		#[InjectUser]
		User $identity,
		?FoosFilter $filter,
		?FoosSorter $sorter
	): CakeORMPaginationResult {
		return $this->_fetchEntities($resolveInfo, $identity, $filter, $sorter);
	}

	#[Mutation]
	public function createFoo(
		ResolveInfo $resolveInfo,
		#[InjectUser]
		User $identity,
		#[UseInputType(inputType: 'CreateFoo')]
		Foo $foo
	): Foo {
		return $this->_createEntity($resolveInfo, $identity, $foo);
	}

	#[Mutation]
	public function updateFoo(
		ResolveInfo $resolveInfo,
		#[InjectUser]
		User $identity,
		#[UseInputType(inputType: 'UpdateFoo')]
		Foo $foo
	): Foo {
		return $this->_updateEntity($resolveInfo, $identity, $foo);
	}

	#[Mutation]
	public function deleteFoo(
		#[InjectUser]
		User $identity,
		ID $id
	): bool {
		return $this->_deleteEntity($identity, 'id', $id);
	}
}
