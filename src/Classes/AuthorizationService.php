<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;

class AuthorizationService implements AuthorizationServiceInterface {
	/**
	 * @inheritDoc
	 */
	public function isAllowed(string $right, $subject = null): bool {
		if (!($subject instanceof UserInterface)) {
			return false;
		}

		return $subject->isAllowedTo($right);
	}
}
