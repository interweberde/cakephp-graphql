<?php
declare(strict_types=1);

namespace TestApp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BarsBazsFixture
 */
class BarsBazsFixture extends TestFixture {
	/**
	 * Init method
	 *
	 * @return void
	 */
	public function init(): void {
		$this->records = [
			[
				'id' => 1,
				'bar_id' => 1,
				'baz_id' => 1,
			],
		];
		parent::init();
	}
}
