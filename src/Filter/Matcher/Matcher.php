<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\Database\Query\SelectQuery as DbSelectQuery;

abstract class Matcher {
	abstract public function build(SelectQuery $query, ExpressionInterface|string $field): QueryExpression | null;

	public function aliasField(SelectQuery|DbSelectQuery $query, string $field, ?string $alias = null): string {
		if ($query instanceof SelectQuery) {
			return $query->getRepository()->aliasField($field);
		}

		if (!$alias) {
			throw new \InvalidArgumentException('alias has to be provided when using DbSelectQuery.');
		}

		if (str_contains($field, '.')) {
            return $field;
        }

        return $alias . '.' . $field;
	}
}
