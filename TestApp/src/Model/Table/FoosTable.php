<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Foos Model
 *
 * @property \TestApp\Model\Table\BazsTable&\Cake\ORM\Association\HasMany $Bazs
 * @method \TestApp\Model\Entity\Foo newEmptyEntity()
 * @method \TestApp\Model\Entity\Foo newEntity(array $data, array $options = [])
 * @method \TestApp\Model\Entity\Foo[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\Foo get($primaryKey, $options = [])
 * @method \TestApp\Model\Entity\Foo findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \TestApp\Model\Entity\Foo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\Foo[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\Foo|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\Foo saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\Foo[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Foo[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Foo[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Foo[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FoosTable extends Table {
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('foos');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->hasMany('Bazs', [
			'foreignKey' => 'foo_id',
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
			->boolean('foo_bool')
			->requirePresence('foo_bool', 'create')
			->allowEmptyString('foo_bool');

		$validator
			->date('foo_date')
			->requirePresence('foo_date', 'create')
			->allowEmptyDate('foo_date');

		$validator
			->dateTime('foo_datetime')
			->requirePresence('foo_datetime', 'create')
			->allowEmptyDateTime('foo_datetime');

		$validator
			->numeric('foo_float')
			->requirePresence('foo_float', 'create')
			->allowEmptyString('foo_float');

		$validator
			->integer('foo_int')
			->requirePresence('foo_int', 'create')
			->allowEmptyString('foo_int');

		$validator
			->scalar('foo_str')
			->requirePresence('foo_str', 'create')
			->allowEmptyString('foo_str');

		return $validator;
	}
}
