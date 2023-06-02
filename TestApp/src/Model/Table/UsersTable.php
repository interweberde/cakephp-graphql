<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @method \TestApp\Model\Entity\User newEmptyEntity()
 * @method \TestApp\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \TestApp\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\User get($primaryKey, $options = [])
 * @method \TestApp\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \TestApp\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UsersTable extends Table {
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('users');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');
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

	public function defaultUser() {
		try {
			return $this->get(1);
		} catch (\Throwable $e) {
			$u = $this->newEntity(['title' => 'User']);
			$this->saveOrFail($u);

			return $u;
		}
	}
}
