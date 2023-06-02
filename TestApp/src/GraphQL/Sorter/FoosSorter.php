<?php
declare(strict_types=1);

namespace TestApp\GraphQL\Sorter;

use Cake\Database\Expression\OrderClauseExpression;
use Interweber\GraphQL\Sorter\Sorter;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class FoosSorter extends Sorter {
	/**
	 * @param FooSorterFields[] $fields
	 * @return FoosSorter
	 */
	#[Factory]
	public static function factory(
		array $fields
	): FoosSorter {
		$order = [];

		foreach ($fields as $field) {
			$order[] = match ($field) {
				FooSorterFields::ID_ASC => new OrderClauseExpression('id', 'asc'),
				FooSorterFields::ID_DESC => new OrderClauseExpression('id', 'desc'),
				FooSorterFields::DATE_ASC => new OrderClauseExpression('foo_date', 'asc'),
				FooSorterFields::DATE_DESC => new OrderClauseExpression('foo_date', 'desc'),
			};
		}

		return new static($order);
	}
}
