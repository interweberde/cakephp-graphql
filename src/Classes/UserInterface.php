<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Authentication\IdentityInterface as AuthenticationIdentityInterface;
use Authorization\IdentityInterface as AuthorizationIdentityInterface;

interface UserInterface extends AuthenticationIdentityInterface, AuthorizationIdentityInterface {
	/**
	 * Checks if the User has `$right`. This is the value passed to the "@Right"-annotation of GraphQLite.
	 *
	 * @param string $right
	 * @return bool
	 * @see https://graphqlite.thecodingmachine.io/docs/authentication-authorization
	 */
	public function isAllowedTo(string $right): bool;
}
