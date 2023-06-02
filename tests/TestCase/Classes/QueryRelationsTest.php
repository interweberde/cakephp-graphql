<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Classes;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class QueryRelationsTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Users', 'app.Foos', 'app.Bazs', 'app.Bars', 'app.BarsBazs', 'app.Quxs'];

	public function testHasMany() {
		// language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					bazs {
						id
					}
				}
			}
		";

		$body = $this->query($query);

		$foo = $body['data']['foo'] ?? null;

		$this->assertNotNull($foo);

		$bazs = $foo['bazs'] ?? null;
		$this->assertIsArray($bazs);
		$this->assertNotEmpty($bazs);

		$this->assertEquals('1', $bazs[0]['id']);
	}

	public function testBelongsTo() {
		// language=GraphQL
		$query = "
			query {
				baz(id: 1) {
					foo {
						id
					}
				}
			}
		";

		$body = $this->query($query);

		$baz = $body['data']['baz'] ?? null;

		$this->assertNotNull($baz);
		$this->assertNotNull($baz['foo']);

		$this->assertEquals('1', $baz['foo']['id']);
	}
}
