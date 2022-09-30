<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Factory;

use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;

/**
 * @template T of \Cake\ORM\Table
 */
class BaseFactory {
	use LocatorAwareTrait;

	/**
	 * @var \Cake\ORM\Table|T
	 */
	protected Table $model;
	public function __construct() {
		$this->model = $this->fetchTable();
	}
}
