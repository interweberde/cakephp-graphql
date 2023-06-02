<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Authorization\Policy\Result;
use Cake\Datasource\EntityInterface;

class BazPolicy extends EntityPolicy {
	public function canShow($user, EntityInterface $entity): Result {
		return new Result(true, 'Foo!');
	}

	public function canCreate($user, EntityInterface $entity): Result {
		return $this->canShow($user, $entity);
	}

	public function canUpdate($user, EntityInterface $entity): Result {
		return $this->canShow($user, $entity);
	}

	public function canDelete($user, EntityInterface $entity): Result {
		return $this->canShow($user, $entity);
	}
}
