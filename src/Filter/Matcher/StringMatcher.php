<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class StringMatcher extends BaseMatcher {
	private function __construct(
		?string $eq,
		?string $neq,
		?array $in,
		?array $nin,
		protected ?string $startsWith,
		protected ?string $endsWith,
		protected ?string $contains,
		?bool $null
	) {
		parent::__construct($eq, $neq, $in, $nin, $null);
	}

	/**
	 * @Factory()
	 * @param string|null $eq Match if Entry equals string
	 * @param string|null $neq Match if Entry does not equal string
	 * @param string[]|null $in Match if Entry is in list
	 * @param string[]|null $nin Match if Entry is not in list
	 * @param string|null $startsWith Match if Entry starts with string
	 * @param string|null $endsWith Match if Entry ends with string
	 * @param string|null $contains Match if Entry contains string
	 * @param bool|null $null
	 * @return StringMatcher
	 */
	public static function factory(
		?string $eq,
		?string $neq,
		?array $in,
		?array $nin,
		?string $startsWith,
		?string $endsWith,
		?string $contains,
		?bool $null
	): StringMatcher {
		return new self($eq, $neq, $in, $nin, $startsWith, $endsWith, $contains, $null);
	}

	public function build(Query $query, ExpressionInterface|string $field): QueryExpression | null {
		$expr = $this->buildBasic($query, $field);

		if ($this->startsWith) {
			$expr = $expr->like($field, $this->startsWith . '%');
		}

		if ($this->endsWith) {
			$expr = $expr->like($field, '%' . $this->endsWith);
		}

		if ($this->contains) {
			$expr = $expr->like($field, '%' . $this->contains . '%');
		}

		return $expr;
	}
}
