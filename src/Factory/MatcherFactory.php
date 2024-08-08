<?php

namespace Interweber\GraphQL\Factory;

use DateTimeImmutable;
use Interweber\GraphQL\Filter\Matcher\BooleanMatcher;
use Interweber\GraphQL\Filter\Matcher\DateMatcher;
use Interweber\GraphQL\Filter\Matcher\FloatMatcher;
use Interweber\GraphQL\Filter\Matcher\IdMatcher;
use Interweber\GraphQL\Filter\Matcher\IntMatcher;
use Interweber\GraphQL\Filter\Matcher\NullMatcher;
use Interweber\GraphQL\Filter\Matcher\StringMatcher;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

class MatcherFactory {
	/**
	 * @param bool $eq Match if Entry equals boolean
	 * @return BooleanMatcher
	 */
	#[Factory]
	public static function factoryBoolean(
		bool $eq
	): BooleanMatcher {
		return new BooleanMatcher($eq);
	}

	/**
	 * @param DateTimeImmutable|null $eq Match if Entry equals DateTime
	 * @param DateTimeImmutable|null $neq Match if Entry does not equal DateTime
	 * @param DateTimeImmutable[]|null $in Match if Entry is in list
	 * @param DateTimeImmutable[]|null $nin Match if Entry is not in list
	 * @param DateTimeImmutable|null $lte Match if Entry is less or equal than DateTime
	 * @param DateTimeImmutable|null $gte Match if Entry is greater or equal than DateTime
	 * @param DateTimeImmutable|null $lt Match if Entry is less than DateTime
	 * @param DateTimeImmutable|null $gt Match if Entry is greater than DateTime
	 * @param bool|null $null Match if Entry is null
	 * @return DateMatcher
	 */
	#[Factory]
	public static function factoryDate(
		?DateTimeImmutable $eq,
		?DateTimeImmutable $neq,
		?array $in,
		?array $nin,
		?DateTimeImmutable $lte,
		?DateTimeImmutable $gte,
		?DateTimeImmutable $lt,
		?DateTimeImmutable $gt,
		?bool $null
	): DateMatcher {
		return new DateMatcher($eq, $neq, $in, $nin, $lte, $gte, $lt, $gt, $null);
	}

	/**
	 * @param int|null $eq Match if Entry equals int
	 * @param int|null $neq Match if Entry does not equal int
	 * @param int[]|null $in Match if Entry is in list
	 * @param int[]|null $nin Match if Entry is not in list
	 * @param int|null $lte Match if Entry is less or equal than int
	 * @param int|null $gte Match if Entry is greater or equal than int
	 * @param int|null $lt Match if Entry is less than int
	 * @param int|null $gt Match if Entry is greater than int
	 * @param bool|null $null
	 * @return IntMatcher
	 */
	#[Factory]
	public static function factoryInt(
		?int $eq,
		?int $neq,
		?array $in,
		?array $nin,
		?int $lte,
		?int $gte,
		?int $lt,
		?int $gt,
		?bool $null
	): IntMatcher {
		return new IntMatcher($eq, $neq, $in, $nin, $lte, $gte, $lt, $gt, $null);
	}

	/**
	 * @param float|null $eq Match if Entry equals float
	 * @param float|null $neq Match if Entry does not equal float
	 * @param float[]|null $in Match if Entry is in list
	 * @param float[]|null $nin Match if Entry is not in list
	 * @param float|null $lte Match if Entry is less or equal than float
	 * @param float|null $gte Match if Entry is greater or equal than float
	 * @param float|null $lt Match if Entry is less than float
	 * @param float|null $gt Match if Entry is greater than float
	 * @param bool|null $null
	 * @return FloatMatcher
	 */
	#[Factory]
	public static function factoryFloat(
		?float $eq,
		?float $neq,
		?array $in,
		?array $nin,
		?float $lte,
		?float $gte,
		?float $lt,
		?float $gt,
		?bool $null
	): FloatMatcher {
		return new FloatMatcher($eq, $neq, $in, $nin, $lte, $gte, $lt, $gt, $null);
	}

	/**
	 * @param ID|null $eq
	 * @param ID|null $neq
	 * @param ID[]|null $in
	 * @param ID[]|null $nin
	 * @return IdMatcher
	 */
	#[Factory]
	public static function factoryId(
		?ID $eq,
		?ID $neq,
		?array $in,
		?array $nin,
		?bool $null
	): IdMatcher {
		return new IdMatcher($eq, $neq, $in, $nin, $null);
	}

	/**
	 * @param string|null $eq Match if Entry equals string
	 * @param string|null $neq Match if Entry does not equal string
	 * @param string[]|null $in Match if Entry is in list
	 * @param string[]|null $nin Match if Entry is not in list
	 * @param string|null $startsWith Match if Entry starts with string
	 * @param string|null $endsWith Match if Entry ends with string
	 * @param string|null $contains Match if Entry contains string
	 * @param bool|null $null
	 * @return StringMatcher
	 */
	#[Factory]
	public static function factoryString(
		?string $eq,
		?string $neq,
		?array $in,
		?array $nin,
		?string $startsWith,
		?string $endsWith,
		?string $contains,
		?bool $null
	): StringMatcher {
		return new StringMatcher($eq, $neq, $in, $nin, $startsWith, $endsWith, $contains, $null);
	}

	#[Factory]
	public static function factoryNull(bool $null): NullMatcher {
		return new NullMatcher(null, null, null, null, null);
	}
}
