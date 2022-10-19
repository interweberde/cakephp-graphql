<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Policy;

use Cake\ORM\Query;
use Interweber\GraphQL\Classes\UserInterface;

/**
 * @template TUser of UserInterface
 */
abstract class TablePolicy {
	/**
	 * @param TUser $user
	 * @param Query $query
	 * @return Query
	 */
	abstract public function scopeShow($user, Query $query): Query;

	/**
	 * @param TUser $user
	 * @param Query $query
	 * @return Query
	 */
	abstract public function scopeList($user, Query $query): Query;

	/**
	 * @param TUser $user
	 * @param Query $query
	 * @return Query
	 */
	abstract public function scopeUpdate($user, Query $query): Query;

	/**
	 * @param TUser $user
	 * @param Query $query
	 * @return Query
	 */
	abstract public function scopeDelete($user, Query $query): Query;
}
