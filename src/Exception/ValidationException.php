<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Exception;

use Cake\ORM\Exception\PersistenceFailedException;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;

class ValidationException extends GraphQLException {
	public function __construct(string $message = 'Invalid data', ?string $field = null, ?string $type = null, int $code = 400) {
		parent::__construct($message, $code, null, 'Validate', [
			'field' => $field,
			'type' => $type,
		]);
	}

	public static function makeFromEntityError(PersistenceFailedException $exception) {
		$errors = $exception->getEntity()->getErrors();

		$aggregate = new GraphQLAggregateException();

		foreach ($errors as $field => $error) {
			foreach ($error as $type => $messages) {
				$messages = (array) $messages;

				foreach ($messages as $message) {
					if (is_string($message)) {
						$aggregate->add(new ValidationException($message, $field, (string) $type));
					}

					if (!is_array($message)) {
						continue;
					}

					foreach ($message as $item) {
						if (!is_string($item)) {
							continue;
						}

						$aggregate->add(new ValidationException($item, $field, (string) $type));
					}
				}
			}
		}

		return $aggregate;
	}

	public static function makeFromValidator($errors) {
		$aggregate = new GraphQLAggregateException();

		foreach ($errors as $field => $error) {
			foreach ($error as $type => $messages) {
				$messages = (array) $messages;

				foreach ($messages as $message) {
					$aggregate->add(new ValidationException($message, $field, $type));
				}
			}
		}

		return $aggregate;
	}

	public function isClientSafe(): bool {
		return true;
	}
}
