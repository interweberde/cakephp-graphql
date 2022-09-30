<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use Cake\I18n\FrozenTime;
use TheCodingMachine\GraphQLite\Types\DateTimeType;

class FrozenTimeType extends DateTimeType {
	/**
	 * @var string
	 */
	public $name = 'FrozenTime';

	/**
	 * @var string
	 */
	public $description = 'The `FrozenTime` scalar type represents time data, represented as an ISO-8601 encoded UTC date string.';

	public function parseValue($value): ?FrozenTime {
		$date = parent::parseValue($value);

		if (!$date) {
			return null;
		}

		return new FrozenTime($date);
	}
}
