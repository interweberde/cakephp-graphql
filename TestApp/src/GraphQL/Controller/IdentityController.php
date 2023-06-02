<?php
declare(strict_types=1);

namespace TestApp\GraphQL\Controller;

use TestApp\Model\Entity\User;
use TheCodingMachine\GraphQLite\Annotations\InjectUser;
use TheCodingMachine\GraphQLite\Annotations\Query;

class IdentityController {
	#[Query(outputType: 'ID!')]
	public function whoami(
		#[InjectUser] User $user
	) {
		return $user->id;
	}
}
