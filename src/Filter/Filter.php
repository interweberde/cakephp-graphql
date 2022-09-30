<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Filter;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\Query;
use Interweber\GraphQL\Filter\Matcher\Matcher;

class Filter {
	/**
	 * @param array<QueryExpression|Matcher|\Closure|null> $filters
	 */
	protected function __construct(
		protected array $filters
	) {
	}

	public function apply(Query $q): Query {
		foreach ($this->filters as $field => $filter) {
			if (!$filter) {
				continue;
			}

			$expr = $filter;
			if ($filter instanceof Matcher) {
				$expr = $filter->build($q, $filter->aliasField($q, $field));
			} elseif ($filter instanceof \Closure) {
				$res = $filter($q, $q->newExpr());

				if ($res instanceof Query) {
					$q = $res;
					$expr = $q->newExpr();
				} elseif ($res instanceof ExpressionInterface) {
					$expr = $res;
				} else {
					throw new InternalErrorException('Invalid Filter return type. Filters have to return values of type Query or ExpressionInterface.');
				}
			}

			if (!$expr) {
				continue;
			}

			$q = $q->where($expr);
		}

		return $q;
	}
}
