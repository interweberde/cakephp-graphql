<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query\SelectQuery;

/**
 * @template TEnum of \BackedEnum
 */
class EnumMatcher extends BaseMatcher {
	public function build(SelectQuery $query, ExpressionInterface|string $field): QueryExpression {
		return $this->buildBasic($query, $field);
	}

	public function buildRelation(string $relation, string $field): \Closure {
		return function (SelectQuery $query, QueryExpression $exp) use ($relation, $field) {
			$pk = $query->getRepository()->aliasField($query->getRepository()->getPrimaryKey());

			return $query
				->where($exp->in(
					$pk,
					$query->getRepository()->find()
						->select($pk)
						->leftJoinWith($relation)
						->where($this->build(
							$query,
							$query->getRepository()->getAssociation($relation)->aliasField($field)
						))
				));
		};
	}
}
