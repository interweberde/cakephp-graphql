<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class FloatMatcher extends BaseMatcher {
	protected function __construct(
		?float $eq,
		?float $neq,
		?array $in,
		?array $nin,
		protected ?float $lte,
		protected ?float $gte,
		protected ?float $lt,
		protected ?float $gt,
		?bool $null
	) {
		parent::__construct($eq, $neq, $in, $nin, $null);
	}

	/**
	 * @Factory()
	 * @param float|null $eq Match if Entry equals float
	 * @param float|null $neq Match if Entry does not equal float
	 * @param float[]|null $in Match if Entry is in list
	 * @param float[]|null $nin Match if Entry is not in list
	 * @param float|null $lte Match if Entry is less or equal than float
	 * @param float|null $gte Match if Entry is greater or equal than float
	 * @param float|null $lt Match if Entry is less than float
	 * @param float|null $gt Match if Entry is greater than float
	 * @param bool|null $null
	 * @return FloatMatcher
	 */
	public static function factory(
		?float $eq,
		?float $neq,
		?array $in,
		?array $nin,
		?float $lte,
		?float $gte,
		?float $lt,
		?float $gt,
		?bool $null
	): FloatMatcher {
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

		if ($this->gte) {
			$expr = $expr->gt($field, $this->gt);
		}

		return $expr;
	}
}
