<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Policy;

use Cake\ORM\Query\SelectQuery;
use Interweber\GraphQL\Classes\UserInterface;

/**
 * @template TUser of UserInterface
 */
abstract class TablePolicy {
	/**
	 * @param TUser|null $user
	 * @param SelectQuery $query
	 * @return SelectQuery
	 */
	abstract public function scopeShow($user, SelectQuery $query): SelectQuery;

	/**
	 * @param TUser|null $user
	 * @param SelectQuery $query
	 * @return SelectQuery
	 */
	abstract public function scopeList($user, SelectQuery $query): SelectQuery;

	/**
	 * @param TUser|null $user
	 * @param SelectQuery $query
	 * @return SelectQuery
	 */
	abstract public function scopeUpdate($user, SelectQuery $query): SelectQuery;

	/**
	 * @param TUser|null $user
	 * @param SelectQuery $query
	 * @return SelectQuery
	 */
	abstract public function scopeDelete($user, SelectQuery $query): SelectQuery;
}
