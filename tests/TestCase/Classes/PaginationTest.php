<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase\Classes;

use Cake\TestSuite\TestCase;
use Interweber\GraphQL\Classes\CakeORMPaginationResult;
use Interweber\GraphQL\Test\TestCase\GraphQLTestTrait;
use TestApp\Model\Entity\Foo;

class PaginationTest extends TestCase {
	use GraphQLTestTrait;

	protected $fixtures = ['app.Users', 'app.Foos', 'app.Bazs', 'app.Bars', 'app.BarsBazs', 'app.Quxs'];

	protected function send(int $offset) {
		//language=GraphQL
		$query = "
			query (\$offset: Int!) {
				foos(sorter: { fields: [ID_ASC] }) {
					items(limit: 1, offset: \$offset) {
						id
					}
				}
			}
		";

		$resp = $this->query($query, ['offset' => $offset]);

		return $resp['data']['foos']['items'] ?? null;
	}

	public function testPagination() {
		$p = $this->send(0);

		$this->assertIsArray($p);
		$this->assertCount(1, $p);
		$this->assertEquals('1', $p[0]['id']);

		$p = $this->send(1);

		$this->assertIsArray($p);
		$this->assertCount(1, $p);
		$this->assertEquals('2', $p[0]['id']);

		$p = $this->send(2);

		$this->assertIsArray($p);
		$this->assertCount(1, $p);
		$this->assertEquals('3', $p[0]['id']);

		$p = $this->send(3);
		$this->assertNull($p);
	}

	public function testResultClass() {
		$Foos = $this->getTableLocator()->get('Foos');

		$query = $Foos->find();

		$result = new CakeORMPaginationResult($query, mapResult: fn (Foo $f) => $f->id);

		$arr = collection($result->getIterator())->toArray();

		$this->assertCount(3, $arr);
		$this->assertEquals('1', $arr[0]);
		$this->assertEquals('2', $arr[1]);
		$this->assertEquals('3', $arr[2]);
	}

	public function testPageClass() {
		$Foos = $this->getTableLocator()->get('Foos');

		$query = $Foos->find();

		$result = new CakeORMPaginationResult($query, mapResult: fn (Foo $f) => $f->id);

		$page = $result->take(2, 2);

		$this->assertEquals(3, $page->totalCount());
		$this->assertEquals(1, $page->count());
		$this->assertEquals(2, $page->getCurrentLimit());
		$this->assertEquals(2, $page->getCurrentPage());
		$this->assertEquals(2, $page->getCurrentOffset());
	}
}
