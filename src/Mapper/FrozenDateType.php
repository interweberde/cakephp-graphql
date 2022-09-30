<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use Cake\I18n\FrozenDate;
use TheCodingMachine\GraphQLite\Types\DateTimeType;

class FrozenDateType extends DateTimeType {
	/**
	 * @var string
	 */
	public $name = 'FrozenDate';

	/**
	 * @var string
	 */
	public $description = 'The `FrozenDate` scalar type represents time data, represented as an ISO-8601 encoded UTC date string.';

	public function parseValue($value): ?FrozenDate {
		$date = parent::parseValue($value);

		if (!$date) {
			return null;
		}

		return new FrozenDate($date);
	}
}
