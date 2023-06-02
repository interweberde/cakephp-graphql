<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Bars Model
 *
 * @property \TestApp\Model\Table\QuxsTable&\Cake\ORM\Association\HasMany $Quxs
 * @property \TestApp\Model\Table\BazsTable&\Cake\ORM\Association\BelongsToMany $Bazs
 * @method \TestApp\Model\Entity\Bar newEmptyEntity()
 * @method \TestApp\Model\Entity\Bar newEntity(array $data, array $options = [])
 * @method \TestApp\Model\Entity\Bar[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\Bar get($primaryKey, $options = [])
 * @method \TestApp\Model\Entity\Bar findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \TestApp\Model\Entity\Bar patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\Bar[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\Bar|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\Bar saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\Bar[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Bar[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Bar[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Bar[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class BarsTable extends Table {
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('bars');
		$this->setDisplayField('title');
		$this->setPrimaryKey('id');

		$this->hasMany('Quxs', [
			'foreignKey' => 'bar_id',
		]);
		$this->belongsToMany('Bazs', [
			'foreignKey' => 'bar_id',
			'targetForeignKey' => 'baz_id',
			'joinTable' => 'bars_bazs',
		]);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->scalar('title')
			->requirePresence('title', 'create')
			->notEmptyString('title');

		return $validator;
	}
}
