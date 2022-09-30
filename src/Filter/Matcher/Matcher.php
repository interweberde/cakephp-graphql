<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;

abstract class Matcher {
	abstract public function build(Query $query, ExpressionInterface|string $field): QueryExpression | null;

	public function aliasField(Query $query, string $field): string {
		return $query->getRepository()->aliasField($field);
	}
}
