<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;

abstract class BaseMatcher extends Matcher {
	protected function __construct(
		protected $eq,
		protected $neq,
		protected $in,
		protected $nin,
		protected ?bool $null
	) {
	}

	/**
	 * @param Query $query
	 * @param ExpressionInterface|string $field
	 * @return QueryExpression
	 */
	protected function buildBasic(Query $query, ExpressionInterface|string $field): QueryExpression {
		$expr = $query->newExpr();

		if ($this->eq !== null) {
			$expr = $expr->eq($field, $this->eq);
		}

		if ($this->neq !== null) {
			$expr = $expr->notEq($field, $this->neq);
		}

		if ($this->in) {
			$expr = $expr->in($field, $this->in);
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
