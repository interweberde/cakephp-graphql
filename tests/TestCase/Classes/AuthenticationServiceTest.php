<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Classes;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class AuthenticationServiceTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Users'];

	public function testInjectIdentity() {
		//language=GraphQL
		$query = "
			query {
				whoami
			}
		";

		$resp = $this->query($query);

		$this->assertEquals('1', $resp['data']['whoami'] ?? null);
	}
}
