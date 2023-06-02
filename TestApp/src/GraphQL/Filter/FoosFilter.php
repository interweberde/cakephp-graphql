<?php
declare(strict_types=1);

namespace TestApp\GraphQL\Filter;

use Interweber\GraphQL\Filter\Filter;
use Interweber\GraphQL\Filter\Matcher\BooleanMatcher;
use Interweber\GraphQL\Filter\Matcher\DateMatcher;
use Interweber\GraphQL\Filter\Matcher\FloatMatcher;
use Interweber\GraphQL\Filter\Matcher\IdMatcher;
use Interweber\GraphQL\Filter\Matcher\IntMatcher;
use Interweber\GraphQL\Filter\Matcher\StringMatcher;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class FoosFilter extends Filter {
	#[Factory]
	public static function factory(
		?IdMatcher $id,
		?BooleanMatcher $bool,
		?DateMatcher $date,
		?DateMatcher $datetime,
		?FloatMatcher $float,
		?IntMatcher $int,
		?StringMatcher $str,
		?IdMatcher $baz_id,
	): FoosFilter {
		return new static([
			'id' => $id,
			'foo_bool' => $bool,
			'foo_date' => $date,
			'foo_datetime' => $datetime,
			'foo_float' => $float,
			'foo_int' => $int,
			'foo_str' => $str,
			'bazs' => $baz_id?->buildRelation('Bazs', 'id'),
		]);
	}
}
