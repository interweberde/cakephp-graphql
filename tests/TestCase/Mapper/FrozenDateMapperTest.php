<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Mapper;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class FrozenDateMapperTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Users', 'app.Foos'];

	public function testCreateDate() {
		//language=GraphQL
		$query = "
			mutation {
				createFoo(foo: { date: \"2023-02-05\" }) {
					id
					date
				}
			}
		";

		$resp = $this->query($query);

		$this->assertNotNull($resp['data']['createFoo'] ?? null);
		$this->assertEquals('2023-02-05T00:00:00+00:00', $resp['data']['createFoo']['date'] ?? null);
	}

	public function testCreateDateTime() {
		//language=GraphQL
		$query = "
			mutation {
				createFoo(foo: { datetime: \"2023-02-05T12:34:56+00:00\" }) {
					id
					datetime
				}
			}
		";

		$resp = $this->query($query);

		$this->assertNotNull($resp['data']['createFoo'] ?? null);
		$this->assertEquals('2023-02-05T12:34:56+00:00', $resp['data']['createFoo']['datetime'] ?? null);
	}
}
