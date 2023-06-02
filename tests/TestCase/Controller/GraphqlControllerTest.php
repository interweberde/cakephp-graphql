<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Controller;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class GraphqlControllerTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Foos', 'app.Users'];

	public function testGraphQlAPiAvailable() {
		// language=GraphQL
		$query = "
			query {
				__typename
			}
		";

		$this->query($query);

		$this->assertResponseOk();
		$this->assertContentType('application/json');
		$this->assertResponseEquals('{"data":{"__typename":"Query"}}');
	}

	public function testFetchSingle() {
		// language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					id
					bool
					int
					float
					str
					date
					datetime
				}
			}
		";

		$body = $this->query($query);

		$this->assertFoo($body['data']['foo']);
	}

	public function testFetch() {
		// using the filter here ensures we get a Foo-Entity that has a fields filled
		// this allows for checking the types.

		// language=GraphQL
		$query = "
			query {
				foos(filter: { id: { id: 1 } }) {
					count
					items {
						id
						bool
						int
						float
						str
						date
						datetime
					}
				}
			}
		";

		$body = $this->query($query);

		$this->assertResponseOk();
		$this->assertContentType('application/json');

		$this->assertArrayHasKey('data', $body);
		$this->assertArrayHasKey('foos', $body['data']);
		$this->assertArrayHasKey('count', $body['data']['foos']);

		$this->assertArrayHasKey('items', $body['data']['foos']);

		$this->assertFoo($body['data']['foos']['items'][0]);
	}

	protected function assertFoo($foo): void {
		$this->assertArrayHasKey('id', $foo);
		$this->assertIsScalar($foo['id']);

		$this->assertArrayHasKey('bool', $foo);
		$this->assertIsBool($foo['bool']);

		$this->assertArrayHasKey('int', $foo);
		$this->assertIsInt($foo['int']);

		$this->assertArrayHasKey('float', $foo);
		$this->assertIsFloat($foo['float']);

		$this->assertArrayHasKey('str', $foo);
		$this->assertIsString($foo['str']);

		$this->assertArrayHasKey('date', $foo);
		$this->assertIsString($foo['date']);
		$this->assertEquals(
			\DateTimeImmutable::createFromFormat(
				\DateTimeInterface::ATOM,
				$foo['date']
			)->format(\DateTimeInterface::ATOM),
			$foo['date']
		);

		$this->assertArrayHasKey('datetime', $foo);
		$this->assertIsString($foo['datetime']);
		$this->assertEquals(
			\DateTimeImmutable::createFromFormat(
				\DateTimeInterface::ATOM,
				$foo['date']
			)->format(\DateTimeInterface::ATOM),
			$foo['date']
		);
	}

	public function testSorter() {
		// language=GraphQL
		$query = "
			query {
				foos(filter: { date: { null: false } }, sorter: { fields: [DATE_ASC] }) {
					count
					items {
						date
					}
				}
			}
		";

		$resp = $this->query($query);

		$this->assertEquals('2023-05-01T00:00:00+00:00', $resp['data']['foos']['items'][0]['date'] ?? null);

		// language=GraphQL
		$query = "
			query {
				foos(sorter: { fields: [DATE_DESC] }) {
					count
					items {
						date
					}
				}
			}
		";

		$resp = $this->query($query);

		$this->assertEquals('2023-06-01T00:00:00+00:00', $resp['data']['foos']['items'][0]['date'] ?? null);
	}
}
