<?php
declare(strict_types=1);

namespace TestApp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BazsFixture
 */
class BazsFixture extends TestFixture {
	/**
	 * Init method
	 *
	 * @return void
	 */
	public function init(): void {
		$this->records = [
			[
				'id' => 1,
				'foo_id' => 1,
				'title' => 'Baz 1',
			],
		];
		parent::init();
	}
}
