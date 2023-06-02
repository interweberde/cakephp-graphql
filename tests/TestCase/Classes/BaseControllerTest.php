<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Classes;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class BaseControllerTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Users', 'app.Foos', 'app.Bazs', 'app.Bars', 'app.BarsBazs', 'app.Quxs'];

	public function testDeleteEntity() {
		//language=GraphQL
		$query = "
			mutation {
				deleteFoo(id: 1)
			}
		";

		$resp = $this->query($query);

		$this->assertTrue($resp['data']['deleteFoo'] ?? null);

		//language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					id
				}
			}
		";

		$resp = $this->query($query);

		$this->assertNull($resp['data']['foo'] ?? null);
		$this->assertNotNull($resp['errors'] ?? null);
	}

	public function testCreateEntity() {
		//language=GraphQL
		$query = "
			mutation {
				createFoo(foo: { str: \"Tester 123\" }) {
					id
					str
				}
			}
		";

		$resp = $this->query($query);

		$this->assertNotNull($resp['data']['createFoo'] ?? null);
		$this->assertEquals('Tester 123', $resp['data']['createFoo']['str'] ?? null);
	}

	public function testUpdateEntity() {
		//language=GraphQL
		$query = "
			mutation {
				updateFoo(foo: { id: 1, str: \"Tester 123\" }) {
					id
					str
				}
			}
		";

		$resp = $this->query($query);

		$this->assertNotNull($resp['data']['updateFoo'] ?? null);
		$this->assertEquals('Tester 123', $resp['data']['updateFoo']['str'] ?? null);

		//language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					id
					str
				}
			}
		";

		$resp = $this->query($query);

		$this->assertNotNull($resp['data']['foo'] ?? null);
		$this->assertEquals('Tester 123', $resp['data']['foo']['str'] ?? null);
	}
}
