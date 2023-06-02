<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Filter;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class BooleanMatcherTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Foos', 'app.Users'];

	protected function send(bool $b): array {
		// language=GraphQL
		$query = "
			query (\$bool: Boolean!) {
				foos(filter: { bool: { eq: \$bool } }) {
					count
					items {
						bool
					}
				}
			}
		";

		return $this->query($query, ['bool' => $b]);
	}

	public function testEqualTrue() {
		$resp = $this->send(true);
		$this->assertTrue($resp['data']['foos']['items'][0]['bool'] ?? null);
	}

	public function testEqualFalse() {
		$resp = $this->send(false);
		$this->assertFalse($resp['data']['foos']['items'][0]['bool'] ?? null);
	}
}
