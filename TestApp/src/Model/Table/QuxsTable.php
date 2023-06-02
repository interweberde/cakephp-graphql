<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Quxs Model
 *
 * @property \TestApp\Model\Table\BarsTable&\Cake\ORM\Association\BelongsTo $Bars
 * @method \TestApp\Model\Entity\Qux newEmptyEntity()
 * @method \TestApp\Model\Entity\Qux newEntity(array $data, array $options = [])
 * @method \TestApp\Model\Entity\Qux[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\Qux get($primaryKey, $options = [])
 * @method \TestApp\Model\Entity\Qux findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \TestApp\Model\Entity\Qux patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\Qux[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\Qux|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\Qux saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\Qux[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Qux[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Qux[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Qux[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class QuxsTable extends Table {
	public array $forceFields = ['id'];

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('quxs');
		$this->setDisplayField('title');
		$this->setPrimaryKey('id');

		$this->belongsTo('Bars', [
			'foreignKey' => 'bar_id',
			'joinType' => 'INNER',
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
			->integer('bar_id')
			->notEmptyString('bar_id');

		$validator
			->scalar('title')
			->requirePresence('title', 'create')
			->notEmptyString('title');

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): RulesChecker {
		$rules->add($rules->existsIn('bar_id', 'Bars'), ['errorField' => 'bar_id']);

		return $rules;
	}
}
