<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Exception;

use GraphQL\Error\Error;

class ForbiddenException extends Error {
	public function __construct(string $message = 'Forbidden', ?\Cake\Http\Exception\ForbiddenException $previous = null) {
		parent::__construct(
			message: $message,
			previous: $previous,
			extensions: [
				'category' => 'authorization',
			]
		);
	}
}
