<?php
declare(strict_types=1);

namespace TestApp\GraphQL\Factory;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Interweber\GraphQL\Factory\BaseFactory;
use TestApp\Model\Entity\Foo;
use TestApp\Model\Table\FoosTable;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @template-extends BaseFactory<FoosTable>
 */
class FooFactory extends BaseFactory {
	protected $defaultTable = FoosTable::class;

	#[Factory(name: 'CreateFoo', default: true)]
	public function create(
		?bool $bool,
		?FrozenDate $date,
		?FrozenTime $datetime,
		?float $float,
		?int $int,
		?string $str
	): Foo {
		return $this->model->newEntity([
			'foo_bool' => $bool,
			'foo_date' => $date,
			'foo_datetime' => $datetime,
			'foo_float' => $float,
			'foo_int' => $int,
			'foo_str' => $str,
		]);
	}

	#[Factory(name: 'UpdateFoo', default: false)]
	public function update(
		ID $id,
		?bool $bool,
		?FrozenDate $date,
		?FrozenTime $datetime,
		?float $float,
		?int $int,
		?string $str
	): Foo {
		$entity = $this->model->get((string) $id);

		return $this->model->patchEntity($entity, [
			'foo_bool' => $bool,
			'foo_date' => $date,
			'foo_datetime' => $datetime,
			'foo_float' => $float,
			'foo_int' => $int,
			'foo_str' => $str,
		]);
	}
}
