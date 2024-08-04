<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Sorter;

use Cake\Database\Expression\OrderClauseExpression;
use Cake\ORM\Query\SelectQuery;

class Sorter {
	/**
	 * @param array<OrderClauseExpression|\Closure> $orderExpressions
	 */
	public function __construct(protected array $orderExpressions = []) {
	}

	public function apply(SelectQuery $q): SelectQuery {
		foreach ($this->orderExpressions as $expr) {
			if (!$expr instanceof OrderClauseExpression) {
				continue;
			}

			$expr->setField($q->getRepository()->aliasField($expr->getField()));
		}

		return $q->orderBy($this->orderExpressions);
	}

	public static function createOrderExpression(string $field, Direction $direction): OrderClauseExpression {
		return new OrderClauseExpression($field, $direction->value);
	}
}
