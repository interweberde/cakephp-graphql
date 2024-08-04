<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter\Matcher;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query\SelectQuery;

class StringMatcher extends BaseMatcher {
	public function __construct(
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

	public function build(SelectQuery $query, ExpressionInterface|string $field): QueryExpression | null {
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
