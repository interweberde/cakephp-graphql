<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Cake\Datasource\Exception\PageOutOfBoundsException;
use Cake\Datasource\Paging\SimplePaginator;
use Cake\ORM\Query\SelectQuery;
use Closure;
use Interweber\GraphQL\Exception\RecordNotFoundException;
use Iterator;

/**
 * Class CakeORMPaginationResult
 *
 * @package GraphQL\Classes
 * @template T
 * @implements \Interweber\GraphQL\Classes\PaginationResult<array-key, T>
 */
class CakeORMPaginationResult implements PaginationResult {
	protected SelectQuery $query;

	protected ?int $count = null;

	protected bool $noPageLimit = false;

	protected Closure|null $mapResult = null;

	public function __construct(SelectQuery $query, $noPageLimit = false, ?Closure $mapResult = null) {
		$this->noPageLimit = $noPageLimit;

		if (!$noPageLimit) {
			$query = $query
				// enforce limit of max. 100 entries
				->limit(100);
		}

		$this->mapResult = $mapResult;

		$this->query = $query;
	}

	/**
	 * @inheritDoc
	 * @psalm-return \Interweber\GraphQL\Classes\CakeORMPaginationPage<T>
	 * @throws RecordNotFoundException
	 */
	public function take($offset, $limit) {
		$paginator = new SimplePaginator();

		// make sure limit is at least 1
		$limit = max($limit, 1);

		if (!$this->noPageLimit) {
			// enforce limit in [1, 100]
			$limit = min($limit, 100);
		}

		$count = $this->count();

		$page = (int) ceil((float) $offset / $limit) + 1;
		if ($offset < $count) {
			try {
				$results = $paginator->paginate($this->query, [
					'page' => $page,
					'limit' => $limit,
				]);
			} catch (\Cake\Datasource\Paging\Exception\PageOutOfBoundsException $e) {
				$results = [];
			}
		} else {
			$results = [];
		}

		return new CakeORMPaginationPage(collection($results), $offset, $page, $limit, $count, $this->mapResult);
	}

	/**
	 * @inheritDoc
	 */
	#[\ReturnTypeWillChange]
	public function count() {
		// cache count for future access
		$this->count = $this->count ?? $this->query->count();

		return $this->count;
	}

	/**
	 * @psalm-suppress ImplementedReturnTypeMismatch
	 */

	/**
	 * @inheritDoc
	 * @psalm-suppress ImplementedReturnTypeMismatch
	 * @return Iterator
	 * @psalm-return Iterator<array-key, T>
	 */
	#[\ReturnTypeWillChange]
	public function getIterator() {
		$res = $this->query->all();
		if ($this->mapResult) {
			return $res->map($this->mapResult);
		}

		return $res;
	}
}
