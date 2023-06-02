<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class IntMatcher extends BaseMatcher {
	protected function __construct(
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

	/**
	 * @Factory()
	 * @param int|null $eq Match if Entry equals int
	 * @param int|null $neq Match if Entry does not equal int
	 * @param int[]|null $in Match if Entry is in list
	 * @param int[]|null $nin Match if Entry is not in list
	 * @param int|null $lte Match if Entry is less or equal than int
	 * @param int|null $gte Match if Entry is greater or equal than int
	 * @param int|null $lt Match if Entry is less than in
	 * @param int|null $gt Match if Entry is greater than int
	 * @param bool|null $null
	 * @return IntMatcher
	 */
	public static function factory(
		?int $eq,
		?int $neq,
		?array $in,
		?array $nin,
		?int $lte,
		?int $gte,
		?int $lt,
		?int $gt,
		?bool $null
	): IntMatcher {
		return new self($eq, $neq, $in, $nin, $lte, $gte, $lt, $gt, $null);
	}

	public function build(Query $query, ExpressionInterface|string $field): QueryExpression | null {
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
