<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Exception;

use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;

class ForbiddenException extends GraphQLException {
	public function __construct(string $message = 'Forbidden', int $code = 403) {
		parent::__construct($message, $code, null, 'authorization');
	}
}
