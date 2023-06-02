<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Classes;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;

class FieldDependenciesTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Users', 'app.Foos', 'app.Bazs', 'app.Bars', 'app.BarsBazs', 'app.Quxs'];

	public function testSimpleDependencyResolution() {
		// language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					depSimple
				}
			}
		";

		$body = $this->query($query);

		$this->assertEquals('Foo 1', $body['data']['foo']['depSimple'] ?? null);
	}

	public function testNestedDependencyResolution() {
		// language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					depField
				}
			}
		";

		$body = $this->query($query);

		$this->assertEquals('Qux 1', $body['data']['foo']['depField'] ?? null);
	}

	public function testNestedDependencyForceFieldResolution() {
		// language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					depFieldForce
				}
			}
		";

		$body = $this->query($query);

		$this->assertTrue($body['data']['foo']['depFieldForce'] ?? null);
	}

	public function testNestedDependencyMultiFieldResolution() {
		// language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					depFieldSelect
				}
			}
		";

		$body = $this->query($query);

		$this->assertEquals('1: Qux 1', $body['data']['foo']['depFieldSelect'] ?? null);
	}

	public function testNestedDependencyAsteriskResolution() {
		// language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					depFieldAsterisk
				}
			}
		";

		$body = $this->query($query);

		$this->assertEquals('1: Qux 1', $body['data']['foo']['depFieldAsterisk'] ?? null);
	}

	public function testDependencyRemap() {
		// language=GraphQL
		$query = "
			query {
				foo(id: 1) {
					depFieldRemap {
						id
						title
					}
				}
			}
		";

		$body = $this->query($query);

		$quxs = $body['data']['foo']['depFieldRemap'] ?? null;
		$this->assertIsArray($quxs);
		$this->assertNotEmpty($quxs);

		$qux = $quxs[0];

		$this->assertEquals('1', $qux['id']);
		$this->assertEquals('Qux 1', $qux['title']);
	}
}
