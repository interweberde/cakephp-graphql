<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;
use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Bar Entity
 *
 * @property int $id
 * @property string $title
 *
 * @property \TestApp\Model\Entity\Qux[] $quxs
 * @property \TestApp\Model\Entity\Baz[] $bazs
 */
#[Type]
#[MagicField(name: 'id', outputType: 'ID!')]
#[MagicField(name: 'title', phpType: 'string')]
#[MagicField(name: 'quxs', phpType: '\TestApp\Model\Entity\Qux[]')]
#[MagicField(name: 'bazs', phpType: '\TestApp\Model\Entity\Baz[]')]
class Bar extends Entity {
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
		'title' => true,
		'quxs' => true,
		'bazs' => true,
	];
}
