<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query\SelectQuery;

class NullMatcher extends BaseMatcher {
	public function build(SelectQuery $query, ExpressionInterface|string $field): QueryExpression {
		return $this->buildBasic($query, $field);
	}
}
