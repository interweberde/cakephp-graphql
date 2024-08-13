<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use Cake\Chronos\ChronosDate;
use Cake\I18n\Date;
use DateTimeImmutable;
use DateTimeInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class DateType extends ScalarType {
	/**
	 * @var string
	 */
	public string $name = 'Date';

	public function serialize(mixed $value): string {
		if ($value instanceof DateTimeImmutable) {
			return $value->format(DateTimeInterface::ATOM);
		}

		if (!$value instanceof ChronosDate) {
			throw new InvariantViolation('DateTime is not an instance of DateTimeImmutable: ' . Utils::printSafe($value));
		}

		return $value->toIso8601String();
	}

	/**
	 * @var string|null
	 */
	public string|null $description = 'The `Date` scalar type represents time data, represented as an ISO-8601 encoded UTC date string.';

	public function parseValue($value): ?Date {
		if ($value === null) {
			return null;
		}

		if ($value instanceof Date) {
			return $value;
		}

		return new Date($value);
	}

	public function parseLiteral($valueNode, array|null $variables = null): string {
		if ($valueNode instanceof StringValueNode) {
			return $valueNode->value;
		}

		// Intentionally without message, as all information already in wrapped Exception
		throw new GraphQLRuntimeException();
	}
}
