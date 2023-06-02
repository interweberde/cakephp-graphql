<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

class IdMatcher extends Matcher {
	private function __construct(protected ID $id) {
	}

	/**
	 * @Factory()
	 * @param ID $id
	 * @return IdMatcher
	 */
	public static function factory(
		ID $id
	): IdMatcher {
		return new self($id);
	}

	public function build(Query $query, ExpressionInterface|string $field): QueryExpression {
		return $query->newExpr()->eq($field, $this->id->val());
	}

	public function buildRelation(string $relation, string $field): \Closure {
		return function (Query $query, QueryExpression $exp) use ($relation, $field) {
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
