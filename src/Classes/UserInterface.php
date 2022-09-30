<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Authentication\Identifier\IdentifierInterface as AuthenticationIdentifierInterface;
use Authorization\IdentityInterface as AuthorizationIdentifierInterface;

interface UserInterface extends AuthenticationIdentifierInterface, AuthorizationIdentifierInterface {
	public function isAllowedTo(string $action): bool;
}
