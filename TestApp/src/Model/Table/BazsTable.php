<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Bazs Model
 *
 * @property \TestApp\Model\Table\FoosTable&\Cake\ORM\Association\BelongsTo $Foos
 * @property \TestApp\Model\Table\BarsTable&\Cake\ORM\Association\BelongsToMany $Bars
 * @method \TestApp\Model\Entity\Baz newEmptyEntity()
 * @method \TestApp\Model\Entity\Baz newEntity(array $data, array $options = [])
 * @method \TestApp\Model\Entity\Baz[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\Baz get($primaryKey, $options = [])
 * @method \TestApp\Model\Entity\Baz findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \TestApp\Model\Entity\Baz patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\Baz[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\Baz|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\Baz saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\Baz[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Baz[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Baz[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\Baz[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class BazsTable extends Table {
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('bazs');
		$this->setDisplayField('title');
		$this->setPrimaryKey('id');

		$this->belongsTo('Foos', [
			'foreignKey' => 'foo_id',
			'joinType' => 'INNER',
		]);
		$this->belongsToMany('Bars', [
			'foreignKey' => 'baz_id',
			'targetForeignKey' => 'bar_id',
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
			->integer('foo_id')
			->notEmptyString('foo_id');

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
		$rules->add($rules->existsIn('foo_id', 'Foos'), ['errorField' => 'foo_id']);

		return $rules;
	}
}
