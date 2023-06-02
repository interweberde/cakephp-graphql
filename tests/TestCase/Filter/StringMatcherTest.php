<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Filter;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class StringMatcherTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Foos', 'app.Users'];

	protected function send(array $matcher, bool $allItems = false): mixed {
		// language=GraphQL
		$query = "
			query (\$matcher: StringMatcherInput!) {
				foos(filter: { str: \$matcher }) {
					count
					items {
						id
						str
					}
				}
			}
		";

		$resp = $this->query($query, ['matcher' => $matcher]);

		$items = $resp['data']['foos']['items'] ?? null;
		if ($items === null) {
			var_dump($resp);
		}
		$this->assertIsArray($items);

		if ($allItems) {
			return $items;
		}

		$this->assertNotEmpty($items);

		return $items[0];
	}

	public function testEqual() {
		$foo = $this->send([
			'eq' => 'Foo 1',
		]);

		$this->assertEquals('1', $foo['id']);
	}

	public function testNotEqual() {
		$foo = $this->send([
			'neq' => 'Foo 1',
		]);

		$this->assertEquals('2', $foo['id']);
	}

	public function testNull() {
		$foo = $this->send([
			'null' => true,
		]);

		$this->assertEquals('3', $foo['id']);
		$this->assertNull($foo['str']);
	}

	public function testNotNull() {
		$foo = $this->send([
			'null' => false,
		]);

		$this->assertNotEquals('3', $foo['id']);
		$this->assertNotNull($foo['str']);
	}

	public function testIn() {
		$foo = $this->send([
			'in' => ['Foo 1', 'Foo 2'],
		], true);

		$this->assertEquals('1', $foo[0]['id']);
		$this->assertEquals('2', $foo[1]['id']);
	}

	public function testNotIn() {
		$foo = $this->send([
			'nin' => ['Foo 1', 'Foo 2'],
		], true);

		$this->assertEmpty($foo);
	}

	public function testStartsWith() {
		$foo = $this->send([
			'startsWith' => 'Foo',
		], true);

		$this->assertEquals('1', $foo[0]['id']);
		$this->assertEquals('2', $foo[1]['id']);
	}

	public function testEndsWith() {
		$foo = $this->send([
			'endsWith' => 'o 1',
		]);

		$this->assertEquals('1', $foo['id']);
	}

	public function testContains() {
		$foo = $this->send([
			'contains' => 'oo',
		], true);

		$this->assertEquals('1', $foo[0]['id']);
		$this->assertEquals('2', $foo[1]['id']);
	}
}
