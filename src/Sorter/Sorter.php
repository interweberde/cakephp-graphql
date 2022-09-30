<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Sorter;

use Cake\Database\Expression\OrderClauseExpression;
use Cake\ORM\Query;

class Sorter {
	/**
	 * @param array<OrderClauseExpression|\Closure> $orderExpressions
	 */
	public function __construct(protected array $orderExpressions) {
	}

	public function apply(Query $q): Query {
		return $q->order($this->orderExpressions);
	}
}
