<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Policy;

use Authorization\Policy\Result;
use Cake\Datasource\EntityInterface;
use Interweber\GraphQL\Classes\UserInterface;

/**
 * @template TUser of UserInterface
 */
abstract class EntityPolicy {
	/**
	 * @param TUser $user
	 * @param EntityInterface $entity
	 * @return Result
	 */
	abstract public function canShow($user, EntityInterface $entity): Result;

	/**
	 * @param TUser $user
	 * @param EntityInterface $entity
	 * @return Result
	 */
	abstract public function canCreate($user, EntityInterface $entity): Result;

	/**
	 * @param TUser $user
	 * @param EntityInterface $entity
	 * @return Result
	 */
	abstract public function canUpdate($user, EntityInterface $entity): Result;

	/**
	 * @param TUser $user
	 * @param EntityInterface $entity
	 * @return Result
	 */
	abstract public function canDelete($user, EntityInterface $entity): Result;
}
