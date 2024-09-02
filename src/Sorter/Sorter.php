<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Sorter;

use Cake\Database\Expression\OrderClauseExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query\SelectQuery;

class Sorter {
	/**
	 * @param array<OrderClauseExpression|callable(QueryExpression, SelectQuery): OrderClauseExpression> $orderExpressions
	 */
	public function __construct(protected array $orderExpressions = []) {
	}

	public function apply(SelectQuery $q): SelectQuery {
		foreach ($this->orderExpressions as $key => $expr) {
			if (!$expr instanceof OrderClauseExpression) {
				$this->orderExpressions[$key] = $expr($q->newExpr(), $q);
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
