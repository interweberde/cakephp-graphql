<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Policy;

use Authorization\Policy\Result;
use Cake\Datasource\EntityInterface;
use Interweber\GraphQL\Classes\UserInterface;

abstract class EntityPolicy {
	abstract public function canShow(UserInterface $user, EntityInterface $entity): Result;

	abstract public function canCreate(UserInterface $user, EntityInterface $entity): Result;

	abstract public function canUpdate(UserInterface $user, EntityInterface $entity): Result;

	abstract public function canDelete(UserInterface $user, EntityInterface $entity): Result;
}
