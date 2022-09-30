<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Policy;

use Cake\ORM\Query;
use Interweber\GraphQL\Classes\UserInterface;

abstract class TablePolicy {
	abstract public function scopeShow(UserInterface $user, Query $query): Query;

	abstract public function scopeList(UserInterface $user, Query $query): Query;

	abstract public function scopeUpdate(UserInterface $user, Query $query): Query;

	abstract public function scopeDelete(UserInterface $user, Query $query): Query;
}
