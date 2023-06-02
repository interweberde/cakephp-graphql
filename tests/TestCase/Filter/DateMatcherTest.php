<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Filter;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class DateMatcherTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Foos', 'app.Users'];

	protected function send(array $matcher, bool $allItems = false): mixed {
		// language=GraphQL
		$query = "
			query (\$matcher: DateMatcherInput!) {
				foos(filter: { date: \$matcher }) {
					count
					items {
						id
						date
					}
				}
			}
		";

		$resp = $this->query($query, ['matcher' => $matcher]);

		$items = $resp['data']['foos']['items'];
		$this->assertIsArray($items);

		if ($allItems) {
			return $items;
		}

		$this->assertNotEmpty($items);

		return $items[0];
	}

	public function testEqual() {
		$foo = $this->send([
			'eq' => '2023-05-01',
		]);

		$this->assertEquals('2', $foo['id']);
	}

	public function testNotEqual() {
		$foo = $this->send([
			'neq' => '2023-05-01',
		]);

		$this->assertEquals('1', $foo['id']);
	}

	public function testLessOrEqual() {
		$foo = $this->send([
			'lte' => '2023-05-01',
		]);

		$this->assertEquals('2', $foo['id']);
	}

	public function testGreaterOrEqual() {
		$foo = $this->send([
			'gte' => '2023-06-01',
		]);

		$this->assertEquals('1', $foo['id']);
	}

	public function testStrictLess() {
		$foo = $this->send([
			'lt' => '2023-06-01',
		]);

		$this->assertEquals('2', $foo['id']);
	}

	public function testStrictGreater() {
		$foo = $this->send([
			'gt' => '2023-05-01',
		]);

		$this->assertEquals('1', $foo['id']);
	}

	public function testNull() {
		$foo = $this->send([
			'null' => true,
		]);

		$this->assertEquals('3', $foo['id']);
		$this->assertNull($foo['date']);
	}

	public function testNotNull() {
		$foo = $this->send([
			'null' => false,
		]);

		$this->assertNotEquals('3', $foo['id']);
		$this->assertNotNull($foo['date']);
	}

	public function testIn() {
		$foo = $this->send([
			'in' => ['2023-05-01', '2023-06-01'],
		], true);

		$this->assertEquals('1', $foo[0]['id']);
		$this->assertEquals('2', $foo[1]['id']);
	}

	public function testNotIn() {
		$foo = $this->send([
			'nin' => ['2023-05-01', '2023-06-01'],
		], true);

		$this->assertEmpty($foo);
	}
}
