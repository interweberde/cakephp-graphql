<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query\SelectQuery;

class BooleanMatcher extends Matcher {
	public function __construct(
		protected bool $eq,
	) {
	}

	public function build(SelectQuery $query, ExpressionInterface|string $field): QueryExpression {
		return $query->newExpr()->eq($field, $this->eq);
	}
}
