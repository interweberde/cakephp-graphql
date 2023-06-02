<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;
use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Baz Entity
 *
 * @property int $id
 * @property int $foo_id
 * @property string $title
 *
 * @property \TestApp\Model\Entity\Foo $foo
 * @property \TestApp\Model\Entity\Bar[] $bars
 */
#[Type]
#[MagicField(name: 'id', outputType: 'ID!')]
#[MagicField(name: 'title', phpType: 'string')]
#[MagicField(name: 'foo', phpType: '\TestApp\Model\Entity\Foo')]
#[MagicField(name: 'bars', phpType: '\TestApp\Model\Entity\Bar[]')]
class Baz extends Entity {
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
		'foo_id' => true,
		'title' => true,
		'foo' => true,
		'bars' => true,
	];
}
