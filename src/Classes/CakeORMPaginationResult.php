<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Cake\Datasource\Exception\PageOutOfBoundsException;
use Cake\Datasource\Paging\SimplePaginator;
use Cake\ORM\Query;
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
	/**
	 * @var \Cake\ORM\Query
	 */
	protected $query;
	/**
	 * @var int
	 */
	protected $count = null;
	/**
	 * @var bool
	 */
	protected $noPageLimit = false;
	/**
	 * @var \Closure
	 */
	protected $mapResult = false;

	public function __construct(Query $query, $noPageLimit = false, ?Closure $mapResult = null) {
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

		$page = (int) ceil((float) $offset / $limit) + 1;
		try {
			$results = $paginator->paginate($this->query, [
				'page' => $page,
				'limit' => $limit,
			]);
		} catch (PageOutOfBoundsException $e) {
			throw new RecordNotFoundException($e->getMessage());
		}

		return new CakeORMPaginationPage($results, $offset, $page, $limit, $this->count(), $this->mapResult);
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
		if ($this->mapResult) {
			return $this->query->map($this->mapResult);
		}

		return $this->query->getIterator();
	}
}
