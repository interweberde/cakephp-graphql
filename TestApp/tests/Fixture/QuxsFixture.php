<?php
declare(strict_types=1);

namespace TestApp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuxsFixture
 */
class QuxsFixture extends TestFixture {
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
				'title' => 'Qux 1',
			],
		];
		parent::init();
	}
}
