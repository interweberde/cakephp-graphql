<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Cake\ORM\Query;

class BarsTablePolicy extends TablePolicy {
	public function scopeShow($user, Query $query): Query {
		return $query;
	}

	public function scopeList($user, Query $query): Query {
		return $this->scopeShow($user, $query);
	}

	public function scopeUpdate($user, Query $query): Query {
		return $this->scopeShow($user, $query);
	}

	public function scopeDelete($user, Query $query): Query {
		return $this->scopeShow($user, $query);
	}
}
