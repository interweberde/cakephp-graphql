<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Cake\Datasource\ResultSetInterface;

/**
 * Class CakeORMPaginationPage
 *
 * @package GraphQL\Classes
 * @template T
 * @implements \Interweber\GraphQL\Classes\PaginationPage<array-key, T>
 */
class CakeORMPaginationPage implements PaginationPage {
	/**
	 * @var \Cake\Collection\CollectionInterface
	 */
	private $resultSet;
	/**
	 * @var int
	 */
	private $offset;
	/**
	 * @var int
	 */
	private $page;
	/**
	 * @var int
	 */
	private $limit;
	/**
	 * @var int
	 */
	private $total;
	/**
	 * @var \Closure
	 */
	private $mapResults;

	public function __construct(ResultSetInterface $resultSet, int $offset, int $page, int $limit, int $total, ?\Closure $mapResults = null) {
		$this->resultSet = $resultSet;
		$this->offset = $offset;
		$this->page = $page;
		$this->limit = $limit;
		$this->total = $total;
		$this->mapResults = $mapResults;

		if ($this->mapResults) {
			$this->resultSet = $this->resultSet->map($this->mapResults);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getCurrentOffset() {
		return $this->offset;
	}

	/**
	 * @inheritDoc
	 */
	public function getCurrentPage() {
		return $this->page;
	}

	/**
	 * @inheritDoc
	 */
	public function getCurrentLimit() {
		return $this->limit;
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int {
		return $this->resultSet->count();
	}

	/**
	 * @inheritDoc
	 */
	public function totalCount() {
		return $this->total;
	}

	/**
	 * @inheritDoc
	 * @psalm-suppress ImplementedReturnTypeMismatch
	 * @return \Iterator
	 * @psalm-return \Iterator<array-key, T>
	 */
	public function getIterator(): \Iterator {
		return $this->resultSet;
	}
}
