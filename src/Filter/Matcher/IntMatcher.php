<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query\SelectQuery;

class IntMatcher extends BaseMatcher {
	public function __construct(
		?int $eq,
		?int $neq,
		?array $in,
		?array $nin,
		protected ?int $lte,
		protected ?int $gte,
		protected ?int $lt,
		protected ?int $gt,
		?bool $null
	) {
		parent::__construct($eq, $neq, $in, $nin, $null);
	}

	public function build(SelectQuery $query, ExpressionInterface|string $field): QueryExpression | null {
		$expr = $this->buildBasic($query, $field);

		if ($this->lte) {
			$expr = $expr->lte($field, $this->lte);
		}

		if ($this->gte) {
			$expr = $expr->gte($field, $this->gte);
		}

		if ($this->lt) {
			$expr = $expr->lt($field, $this->lt);
		}

		if ($this->gt) {
			$expr = $expr->gt($field, $this->gt);
		}

		return $expr;
	}
}
