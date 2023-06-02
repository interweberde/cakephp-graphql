<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Filter;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class IntMatcherTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Foos', 'app.Users'];

	protected function send(array $matcher, bool $allItems = false): mixed {
		// language=GraphQL
		$query = "
			query (\$matcher: IntMatcherInput!) {
				foos(filter: { int: \$matcher }) {
					count
					items {
						id
						int
					}
				}
			}
		";

		$resp = $this->query($query, ['matcher' => $matcher]);

		$items = $resp['data']['foos']['items'] ?? null;
		$this->assertIsArray($items);

		if ($allItems) {
			return $items;
		}

		$this->assertNotEmpty($items);

		return $items[0];
	}

	public function testEqual() {
		$foo = $this->send([
			'eq' => 1,
		]);

		$this->assertEquals('1', $foo['id']);
	}

	public function testNotEqual() {
		$foo = $this->send([
			'neq' => 1,
		]);

		$this->assertEquals('2', $foo['id']);
	}

	public function testLessOrEqual() {
		$foo = $this->send([
			'lte' => 1,
		]);

		$this->assertEquals('1', $foo['id']);
	}

	public function testGreaterOrEqual() {
		$foo = $this->send([
			'gte' => 2,
		]);

		$this->assertEquals('2', $foo['id']);
	}

	public function testStrictLess() {
		$foo = $this->send([
			'lt' => 2,
		]);

		$this->assertEquals('1', $foo['id']);
	}

	public function testStrictGreater() {
		$foo = $this->send([
			'gt' => 1,
		]);

		$this->assertEquals('2', $foo['id']);
	}

	public function testNull() {
		$foo = $this->send([
			'null' => true,
		]);

		$this->assertEquals('3', $foo['id']);
		$this->assertNull($foo['int']);
	}

	public function testNotNull() {
		$foo = $this->send([
			'null' => false,
		]);

		$this->assertNotEquals('3', $foo['id']);
		$this->assertNotNull($foo['int']);
	}

	public function testIn() {
		$foo = $this->send([
			'in' => [1, 2],
		], true);

		$this->assertEquals('1', $foo[0]['id']);
		$this->assertEquals('2', $foo[1]['id']);
	}

	public function testNotIn() {
		$foo = $this->send([
			'nin' => [1, 2],
		], true);

		$this->assertEmpty($foo);
	}
}
