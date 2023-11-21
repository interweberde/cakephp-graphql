<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use GraphQL\Type\Definition\ResolveInfo;
use Interweber\GraphQL\Annotation\FieldDependencies;

// CAUTION!
// Only change things here if you are 100% sure what you are doing!
// While this class may not be perfect, a lot of thoughts and considerations have gone
// into it. As this class should be able to handle all (TM) queries, a little change
// may fix or ease one case, but break 5 others.
// That being said: Happy hacking!
class QueryOptimizer {
	public static function optimizeQuery(Query $query, ResolveInfo $info, IdentityInterface $user, string $authorizationScope, bool $pagination = false): Query {
		[
			'select' => $select,
			'contain' => $contain,
		] = QueryOptimizer::getRequestedQueryFields($info, $query, $user, $authorizationScope, $pagination);

		$query = $query
			->select($select)
			->enableAutoFields()
			->contain($contain);

		return $user->applyScope($authorizationScope, $query);
	}

	/**
	 * @template T of \Cake\Datasource\EntityInterface
	 * @psalm-param T $entity
	 * @psalm-return T
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \GraphQL\Type\Definition\ResolveInfo $info
	 * @param \Authorization\IdentityInterface $user
	 * @param string $authorizationScope
	 * @param bool $pagination
	 * @return \Cake\Datasource\EntityInterface
	 */
	public static function loadRequestedFieldsIntoEntity(EntityInterface $entity, ResolveInfo $info, IdentityInterface $user, string $authorizationScope, bool $pagination = false): EntityInterface {
		/** @var \Cake\ORM\Table $Model */
		$Model = FactoryLocator::get('Table')->get($entity->getSource());
		['contain' => $contain] = static::getRequestedQueryFields($info, $Model->query(), $user, $authorizationScope, $pagination);

		/** @psalm-var T $entity */
		$entity = $Model->loadInto($entity, $contain);

		return $entity;
	}

	/**
	 * @template T of \Cake\Datasource\EntityInterface
	 * @psalm-param T[] $entities
	 * @psalm-return T[]
	 * @param \Cake\Datasource\EntityInterface[] $entities
	 * @param \GraphQL\Type\Definition\ResolveInfo $info
	 * @param \Authorization\IdentityInterface $user
	 * @param string $authorizationScope
	 * @param bool $pagination
	 * @return \Cake\Datasource\EntityInterface[]
	 */
	public static function loadRequestedFieldsIntoEntities(array $entities, ResolveInfo $info, IdentityInterface $user, string $authorizationScope, bool $pagination = false): array {
		if (count($entities) === 0) {
			return $entities;
		}

		/** @var \Cake\ORM\Table $Model */
		$Model = FactoryLocator::get('Table')->get($entities[0]->getSource());
		['contain' => $contain] = static::getRequestedQueryFields($info, $Model->query(), $user, $authorizationScope, $pagination);

		/** @psalm-var T[] $entities */
		$entities = $Model->loadInto($entities, $contain);

		return $entities;
	}

	protected static function _getFieldGetterReflection(\ReflectionClass $entityReflection, string $field): \ReflectionMethod|null {
		$possibleNames = [$field, 'get' . ucfirst($field)];

		/** @var \ReflectionMethod|null $methodReflection */
		$methodReflection = null;
		foreach ($possibleNames as $methodName) {
			if (!$entityReflection->hasMethod($methodName)) {
				continue;
			}

			$methodReflection = $entityReflection->getMethod($methodName);
		}

		return $methodReflection;
	}

	protected static function _generateFields(array $_fields, \Cake\ORM\Table $Model) {
		$results = [];
		$forceFields = $Model->forceFields ?? [];
		foreach ($forceFields as $forceField) {
			if ($_fields[$forceField] ?? false) {
				continue;
			}

			$results[$forceField] = true;
		}

		$entityReflection = new \ReflectionClass($Model->getEntityClass());
		foreach ($_fields as $field => $value) {
			if (is_numeric($field)) {
				$field = $value;
				$value = true;
			}

			if ($field == '__typename') {
				continue;
			}

			if ($value === false) {
				continue;
			}

			$fieldsRemapped = false;

			$methodReflection = static::_getFieldGetterReflection($entityReflection, $field);
			if ($methodReflection) {
				$dependencyAttribute = $methodReflection->getAttributes(FieldDependencies::class)[0] ?? null;

				if ($dependencyAttribute) {
					$dependency = new FieldDependencies($dependencyAttribute->getArguments());
					$remapFields = $dependency->getRemapFields();
					$dependencies = $dependency->getDependencies();
					foreach ($dependencies as $dependencyKey => $dependencyValue) {
						if (is_numeric($dependencyKey)) {
							if (!is_string($dependencyValue)) {
								throw new \Exception(
									sprintf(
										'malformed dependency in field: %s::%s',
										$Model->getEntityClass(),
										$field
									)
								);
							}

							$dependencyKey = $dependencyValue;
							$dependencyValue = true;
						}

						if ($remapFields === true || $remapFields === $dependencyKey) {
							$fieldsRemapped = true;
							$dependencyValue = $value;
						}

						if (is_string($dependencyValue) && $dependencyValue !== '*') {
							$dependencyKey = $dependencyKey . '.' . $dependencyValue;
							$dependencyValue = true;
						}

						$nested = Hash::expand([$dependencyKey => $dependencyValue]);

						foreach ($nested as $k => $v) {
							if (is_array($v)) {
								$results[$k] = array_merge($results[$k] ?? [], $v);

								continue;
							}

							$results[$k] = true;
						}
					}
				}
			}

			if ($fieldsRemapped) {
				continue;
			}

			$results[$field] = $value;
		}

		return $results;
	}

	public static function getRequestedQueryFields(ResolveInfo $info, Query $query, IdentityInterface $user, string $authorizationScope, bool $pagination = false): array {
		$_fields = $info->getFieldSelection(6);

		$select = [
			'id',
		];
		$contain = [];

		if ($pagination) {
			$_fields = $_fields['items'] ?? $_fields;
		}

		/** @var \Cake\ORM\Table $Model */
		$Model = FactoryLocator::get('Table')->get($query->getRepository()->getRegistryAlias());

		$fields = static::_generateFields($_fields, $Model);

		foreach ($fields as $field => $value) {
			if (is_array($value)) {
				$contain[$field] = $value;
				continue;
			}

			/** @var callable|null $virtualField */
			$virtualField = $Model->virtualFields[$field] ?? false;
			if ($virtualField) {
				$select[$field] = $virtualField($query);
				continue;
			}

			if (!$Model->hasField($field)) {
				continue;
			}

			$select[] = $field;
		}

		$contain = QueryOptimizer::getContainKeys($Model, $contain);

		// add necessary contain fields
		$contain = array_filter($contain, function ($key) use ($Model) {
			try {
				return $Model->hasAssociation($key);
			} catch (\InvalidArgumentException $e) {
				return false;
			}
		}, ARRAY_FILTER_USE_KEY);

		foreach ($contain as $key => $value) {
			// ensure id is always selected when custom fields are specified
			if (isset($value['fields']) && !in_array('id', $value['fields'])) {
				$value['fields'][] = 'id';
				$contain[$key] = $value;
			}

			$Assoc = $Model->getAssociation($key);

			/*
				only select relevant info when not directly related (BelongsToMany),
				or the property is accessed through a special method (for paginating - that's the case when it's not mentioned in direct_assoc)
			*/
			if (
				$Assoc instanceof BelongsToMany
				|| (
					$Assoc instanceof HasMany
					&& !in_array($Assoc->getProperty(), $Model->direct_assoc ?? [])
				)
			) {
				// do not change fields of assoc because they are in a join table and not on the assoc model itself.
				$select = static::checkAndAddSelect($select, $Assoc->getBindingKey());
			}

			// swap keys in case of BelongsTo assoc
			$selectKey = $Assoc instanceof BelongsTo ? $Assoc->getForeignKey() : $Assoc->getBindingKey();
			$containKey = $Assoc instanceof BelongsTo ? $Assoc->getBindingKey() : $Assoc->getForeignKey();

			// select keys in correct place (in select or another association)
			if ($Assoc->getSource()->getRegistryAlias() == $Model->getRegistryAlias()) {
				$select = static::checkAndAddSelect($select, $selectKey);
			} else {
				$assocSourceKey = join(
					'.',
					array_slice(
						explode('.', $key),
						0,
						-1
					)
				);

				if (
					($contain[$assocSourceKey] ?? false)
					&& ($contain[$assocSourceKey]['fields'] ?? false)
				) {
					$contain[$assocSourceKey]['fields'] = static::checkAndAddSelect($contain[$assocSourceKey]['fields'], $selectKey);
				}
			}

			$contain[$key]['fields'] = static::checkAndAddSelect($value['fields'] ?? [], $containKey);
		}

		foreach ($contain as $key => $value) {
			$contain[$key] = function (Query $q) use ($query, $authorizationScope, $user, $value) {
				/** @var Query $q */
				$q = $user->applyScope($authorizationScope, $q);

				$AssocModel = $q->getRepository();
				$fields = array_filter($value['fields'] ?? [], function ($field) use ($AssocModel) {
					return $AssocModel->hasField($field);
				});

				$forceFields = $AssocModel->forceFields ?? [];
				foreach ($forceFields as $forceField) {
					$fields = static::checkAndAddSelect($fields, $forceField);
				}

				$virtualFields = $AssocModel->virtualFields ?? [];
				foreach ($value['fields'] ?? [] as $field) {
					$field = str_replace($AssocModel->getAlias() . '.', '', $field);
					/** @var callable|null $virtualField */
					$virtualField = $virtualFields[$field] ?? false;
					if (!$virtualField) {
						continue;
					}

					$fields[$field] = $virtualField($q);
				}

				return $q->select($fields);
			};
		}

		return [
			'select' => $select,
			'contain' => $contain,
		];
	}

	/**
	 * @param \Cake\ORM\Table $Model
	 * @param array<array-key, array|string|bool> $items
	 * @param string $key
	 * @return array Array compatible with Query::contain()
	 * @see Query::contain()
	 */
	protected static function getContainKeys(\Cake\ORM\Table $Model, array $items, string $key = ''): array {
		$result = [];

		$fields = static::_generateFields($items, $Model);

		foreach ($fields as $itemKey => $item) {
			if ($itemKey == '__typename') {
				continue;
			}

			if ($key && $item === true) {
				if ($itemKey !== '*') {
					$result[$key]['fields'][] = $itemKey;

					continue;
				}

				$columns = $Model->getSchema()->columns();
				foreach ($columns as $column) {
					$result[$key]['fields'][] = $column;
				}

				continue;
			}

			if (is_string($item)) {
				$setKey = $key ? $key . '.' . $item : $item;
				$result[$setKey] = true;

				continue;
			}

			if (!is_array($item)) {
				continue;
			}

			$itemKey = Inflector::camelize($itemKey);

			if (!$Model->hasAssociation($itemKey)) {
				$itemKey = Inflector::pluralize($itemKey);

				if (!$Model->hasAssociation($itemKey)) {
					continue;
				}
			}

			$result = array_merge($result, QueryOptimizer::getContainKeys(
				$Model->getAssociation($itemKey)->getTarget(),
				$item,
				$key
					? $key . '.' . $itemKey
					: $itemKey
			));
		}

		return $result;
	}

	protected static function checkAndAddSelect(array $select, string $item): array {
		if (in_array($item, $select)) {
			return $select;
		}

		$select[] = $item;

		return $select;
	}
}
