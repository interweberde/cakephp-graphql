<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class BooleanMatcher extends Matcher {
	protected function __construct(
		protected bool $eq,
	) {
	}

	/**
	 * @Factory()
	 * @param bool $eq Match if Entry equals boolean
	 * @return BooleanMatcher
	 */
	public static function factory(
		bool $eq
	): BooleanMatcher {
		return new self($eq);
	}

	public function build(Query $query, ExpressionInterface|string $field): QueryExpression {
		return $query->newExpr()->eq($field, $this->eq);
	}
}
