<?php
declare(strict_types=1);

namespace TestApp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FoosFixture
 */
class FoosFixture extends TestFixture {
	/**
	 * Init method
	 *
	 * @return void
	 */
	public function init(): void {
		$this->records = [
			[
				'id' => 1,
				'foo_bool' => 1,
				'foo_date' => '2023-06-01',
				'foo_datetime' => '2023-06-01 10:09:42',
				'foo_float' => 1.5,
				'foo_int' => 1,
				'foo_str' => 'Foo 1',
			],
			[
				'id' => 2,
				'foo_bool' => 0,
				'foo_date' => '2023-05-01',
				'foo_datetime' => '2023-05-01 10:09:42',
				'foo_float' => 3.0,
				'foo_int' => 2,
				'foo_str' => 'Foo 2',
			],
			[
				'id' => 3,
				'foo_bool' => null,
				'foo_date' => null,
				'foo_datetime' => null,
				'foo_float' => null,
				'foo_int' => null,
				'foo_str' => null,
			],
		];
		parent::init();
	}
}
