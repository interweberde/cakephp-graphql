<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Factory;

use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * @template T of \Cake\ORM\Table
 */
class BaseFactory {
	use LocatorAwareTrait;

	/**
	 * @var T
	 */
	protected $model;
	public function __construct() {
		/** @psalm-suppress InvalidPropertyAssignmentValue */
		$this->model = $this->fetchTable();
	}
}
