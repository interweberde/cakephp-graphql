<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Authorization\Policy\Result;
use Cake\Datasource\EntityInterface;

class UserPolicy extends EntityPolicy {
	public function canShow($user, EntityInterface $entity): Result {
		if ($entity->get('id') === $user->id) {
			return new Result(true, 'Own Account');
		}

		return new Result(false, 'Other Account');
	}

	public function canCreate($user, EntityInterface $entity): Result {
		return new Result(false, 'Forbidden');
	}

	public function canUpdate($user, EntityInterface $entity): Result {
		return $this->canShow($user, $entity);
	}

	public function canDelete($user, EntityInterface $entity): Result {
		return new Result(false, 'Forbidden');
	}
}
