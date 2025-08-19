<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query\SelectQuery;

abstract class BaseMatcher extends Matcher {
	public function __construct(
		protected $eq,
		protected $neq,
		protected $in,
		protected $nin,
		protected ?bool $null
	) {
	}

	/**
	 * @param SelectQuery $query
	 * @param ExpressionInterface|string $field
	 * @return QueryExpression
	 */
	protected function buildBasic(SelectQuery $query, ExpressionInterface|string $field): QueryExpression {
		$expr = $query->newExpr();

		if ($this->eq !== null) {
			$expr = $expr->eq($field, $this->eq);
		}

		if ($this->neq !== null) {
			$expr = $expr->notEq($field, $this->neq);
		}

		if ($this->in !== null) {
			if (!$this->in) {
				$expr = $expr->add('1 = 0');
			} else {
				$expr = $expr->in($field, $this->in);
			}
		}

		if ($this->nin) {
			$expr = $expr->notIn($field, $this->nin);
		}

		if ($this->null) {
			$expr = $expr->isNull($field);
		}

		if ($this->null === false) {
			$expr = $expr->isNotNull($field);
		}

		return $expr;
	}
}
