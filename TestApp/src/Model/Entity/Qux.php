<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;
use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Qux Entity
 *
 * @property int $id
 * @property int $bar_id
 * @property string $title
 *
 * @property \TestApp\Model\Entity\Bar $bar
 */
#[Type]
#[MagicField(name: 'id', outputType: 'ID!')]
#[MagicField(name: 'title', phpType: 'string')]
#[MagicField(name: 'bar', phpType: '\TestApp\Model\Entity\Bar')]
class Qux extends Entity {
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
		'bar_id' => true,
		'title' => true,
		'bar' => true,
	];
}
