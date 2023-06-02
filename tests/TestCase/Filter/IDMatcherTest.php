<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Filter;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class IDMatcherTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Foos', 'app.Bazs', 'app.Users'];

	public function testEquals() {
		// language=GraphQL
		$query = "
			query {
				foos(filter: { id: { id: 1 } }) {
					count
					items {
						id
					}
				}
			}
		";

		$resp = $this->query($query);

		$id = $resp['data']['foos']['items'][0]['id'] ?? null;

		$this->assertEquals('1', $id);
	}

	public function testRelation() {
		// language=GraphQL
		$query = "
			query {
				foos(filter: { baz_id: { id: 1 } }) {
					count
					items {
						id
					}
				}
			}
		";

		$resp = $this->query($query);

		$id = $resp['data']['foos']['items'][0]['id'] ?? null;

		$this->assertEquals('1', $id);
	}
}
