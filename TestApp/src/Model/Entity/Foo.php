<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;
use Interweber\GraphQL\Annotation\FieldDependencies;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Foo Entity
 *
 * @property int $id
 * @property bool $foo_bool
 * @property \Cake\I18n\FrozenDate $foo_date
 * @property \Cake\I18n\FrozenTime $foo_datetime
 * @property float $foo_float
 * @property int $foo_int
 * @property string $foo_str
 *
 * @property \TestApp\Model\Entity\Baz[] $bazs
 */
#[Type]
#[MagicField(name: 'id', outputType: 'ID!')]
#[MagicField(name: 'bool', phpType: 'bool|null', sourceName: 'foo_bool')]
#[MagicField(name: 'float', phpType: 'float|null', sourceName: 'foo_float')]
#[MagicField(name: 'int', phpType: 'int|null', sourceName: 'foo_int')]
#[MagicField(name: 'str', phpType: 'string|null', sourceName: 'foo_str')]
#[MagicField(name: 'date', phpType: '\Cake\I18n\FrozenDate|null', sourceName: 'foo_date')]
#[MagicField(name: 'datetime', phpType: '\Cake\I18n\FrozenTime|null', sourceName: 'foo_datetime')]
#[MagicField(name: 'bazs', phpType: '\TestApp\Model\Entity\Baz[]')]
class Foo extends Entity {
	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array<string, bool>
	 */
	protected $_accessible = [
		'foo_bool' => true,
		'foo_date' => true,
		'foo_datetime' => true,
		'foo_float' => true,
		'foo_int' => true,
		'foo_str' => true,
		'bazs' => true,
	];

	#[Field]
	#[FieldDependencies(dependencies: ['foo_str'])]
	public function depSimple(): string|null {
		return $this->foo_str;
	}

	#[Field]
	#[FieldDependencies(dependencies: ['Bazs.Bars.Quxs.title'])]
	public function depField(): string {
		return $this->bazs[0]?->bars[0]?->quxs[0]->title;
	}

	#[Field]
	#[FieldDependencies(dependencies: ['Bazs.Bars.Quxs.title'])]
	public function depFieldForce(): bool {
		return (bool) $this->bazs[0]?->bars[0]?->quxs[0]->id;
	}

	#[Field]
	#[FieldDependencies(dependencies: ['Bazs.Bars.Quxs' => ['id', 'title']])]
	public function depFieldSelect(): string {
		$qux = $this->bazs[0]?->bars[0]?->quxs[0];

		return sprintf('%s: %s', $qux->id, $qux->title);
	}

	#[Field]
	#[FieldDependencies(dependencies: ['Bazs.Bars.Quxs.*'])]
	public function depFieldAsterisk(): string {
		$qux = $this->bazs[0]?->bars[0]?->quxs[0];

		return sprintf('%s: %s', $qux->id, $qux->title);
	}

	/**
	 * @return Qux[]
	 */
	#[Field]
	#[FieldDependencies(dependencies: ['Bazs.Bars.Quxs'], remapFields: true)]
	public function depFieldRemap(): array {
		return $this->bazs[0]?->bars[0]?->quxs ?? [];
	}
}
