<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BarsBazs Model
 *
 * @property \TestApp\Model\Table\BarsTable&\Cake\ORM\Association\BelongsTo $Bars
 * @property \TestApp\Model\Table\BazsTable&\Cake\ORM\Association\BelongsTo $Bazs
 * @method \TestApp\Model\Entity\BarsBaz newEmptyEntity()
 * @method \TestApp\Model\Entity\BarsBaz newEntity(array $data, array $options = [])
 * @method \TestApp\Model\Entity\BarsBaz[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\BarsBaz get($primaryKey, $options = [])
 * @method \TestApp\Model\Entity\BarsBaz findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \TestApp\Model\Entity\BarsBaz patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\BarsBaz[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\BarsBaz|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\BarsBaz saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\BarsBaz[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\BarsBaz[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\BarsBaz[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\BarsBaz[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class BarsBazsTable extends Table {
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('bars_bazs');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->belongsTo('Bars', [
			'foreignKey' => 'bar_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Bazs', [
			'foreignKey' => 'baz_id',
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
			->integer('baz_id')
			->notEmptyString('baz_id');

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
		$rules->add($rules->existsIn('baz_id', 'Bazs'), ['errorField' => 'baz_id']);

		return $rules;
	}
}
