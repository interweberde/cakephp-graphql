<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;
use DateTimeImmutable;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class DateMatcher extends BaseMatcher {
	protected function __construct(
		?DateTimeImmutable $eq,
		?DateTimeImmutable $neq,
		?array $in,
		?array $nin,
		protected ?DateTimeImmutable $lte,
		protected ?DateTimeImmutable $gte,
		protected ?DateTimeImmutable $lt,
		protected ?DateTimeImmutable $gt,
		?bool $null
	) {
		parent::__construct($eq, $neq, $in, $nin, $null);
	}

	/**
	 * @Factory()
	 * @param DateTimeImmutable|null $eq Match if Entry equals DateTime
	 * @param DateTimeImmutable|null $neq Match if Entry does not equal DateTime
	 * @param DateTimeImmutable[]|null $in Match if Entry is in list
	 * @param DateTimeImmutable[]|null $nin Match if Entry is not in list
	 * @param DateTimeImmutable|null $lte Match if Entry is less or equal than DateTime
	 * @param DateTimeImmutable|null $gte Match if Entry is greater or equal than DateTime
	 * @param DateTimeImmutable|null $lt Match if Entry is less than DateTime
	 * @param DateTimeImmutable|null $gt Match if Entry is greater than DateTime
	 * @param bool|null $null Match if Entry is null
	 * @return DateMatcher
	 */
	public static function factory(
		?DateTimeImmutable $eq,
		?DateTimeImmutable $neq,
		?array $in,
		?array $nin,
		?DateTimeImmutable $lte,
		?DateTimeImmutable $gte,
		?DateTimeImmutable $lt,
		?DateTimeImmutable $gt,
		?bool $null
	): DateMatcher {
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

		if ($this->null) {
			$expr = $expr->isNull($field);
		}

		return $expr;
	}
}
