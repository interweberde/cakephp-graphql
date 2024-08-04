<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query\SelectQuery;
use TheCodingMachine\GraphQLite\Types\ID;

class IdMatcher extends BaseMatcher {
	public function __construct(
		?ID $eq,
		?ID $neq,
		?ID $in,
		?ID $nin,
		?bool $null
	) {
		parent::__construct($eq, $neq, $in, $nin, $null);
	}

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
