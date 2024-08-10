<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityInterface;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use GraphQL\Type\Definition\ResolveInfo;
use Interweber\GraphQL\Annotation\FieldDependencies;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Annotations\MagicField;

// CAUTION!
// Only change things here if you are 100% sure what you are doing!
// While this class may not be perfect, a lot of thoughts and considerations have gone
// into it. As this class should be able to handle all (TM) queries, a little change
// may fix or ease one case, but break 5 others.
// That being said: Happy hacking!
class QueryOptimizer {
	/**
	 * @param SelectQuery $query Query to be adjusted. The query instance will be mutated.
	 * @param ResolveInfo $info GraphQL ResolveInfo
	 * @param AuthorizationServiceInterface $authorizationService Authorization Service for authorization
	 * @param IdentityInterface|null $identity Identity for authorization
	 * @param string|array<string, string> $authorizationScopes Set the main authorization scope that will be applied to the query and associations. Alternatively, pass an array with authorization scopes: default, main query (key: 'query'), all associations (key: 'assoc') or select associations (key: individual association name).
	 * @param bool $pagination Enable this flag when applying the optimizer on a paginated query
	 * @return SelectQuery
	 */
	public static function optimizeQuery(SelectQuery $query, ResolveInfo $info, AuthorizationServiceInterface $authorizationService, ?IdentityInterface $identity, string|array $authorizationScopes, bool $pagination = false): SelectQuery {
		$authorizationScopes = static::_prepareAuthorizationScopes($authorizationScopes);

		[
			'select' => $select,
			'contain' => $contain,
		] = QueryOptimizer::getRequestedQueryFields($info, $query, $authorizationService, $identity, $authorizationScopes, $pagination);

		$query = $query
			->select($select)
			->enableAutoFields()
			->contain($contain);

		return $authorizationService->applyScope($identity, $authorizationScopes['query'] ?? $authorizationScopes['default'], $query);
	}

	/**
	 * @template T of \Cake\Datasource\EntityInterface
	 * @psalm-param T $entity
	 * @psalm-return T
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \GraphQL\Type\Definition\ResolveInfo $info
	 * @param \Authorization\IdentityInterface $identity
	 * @param string|array<string, string> $authorizationScopes
	 * @param bool $pagination
	 * @return \Cake\Datasource\EntityInterface
	 * @see QueryOptimizer::optimizeQuery()
	 */
	public static function loadRequestedFieldsIntoEntity(EntityInterface $entity, ResolveInfo $info, IdentityInterface $identity, string|array $authorizationScopes, bool $pagination = false): EntityInterface {
		$authorizationScopes = static::_prepareAuthorizationScopes($authorizationScopes);

		/** @var \Cake\ORM\Table $Model */
		$Model = FactoryLocator::get('Table')->get($entity->getSource());
		['contain' => $contain] = static::getRequestedQueryFields($info, $Model->query(), $identity, $authorizationScope, $pagination);

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
	 * @param \Authorization\IdentityInterface $identity
	 * @param string|array<string, string> $authorizationScopes
	 * @param bool $pagination
	 * @return \Cake\Datasource\EntityInterface[]
	 * @see QueryOptimizer::optimizeQuery()
	 */
	public static function loadRequestedFieldsIntoEntities(array $entities, ResolveInfo $info, IdentityInterface $identity, string|array $authorizationScopes, bool $pagination = false): array {
		if (count($entities) === 0) {
			return $entities;
		}

		$authorizationScopes = static::_prepareAuthorizationScopes($authorizationScopes);

		/** @var \Cake\ORM\Table $Model */
		$Model = FactoryLocator::get('Table')->get($entities[0]->getSource());
		['contain' => $contain] = static::getRequestedQueryFields($info, $Model->query(), $identity, $authorizationScope, $pagination);

		/** @psalm-var T[] $entities */
		$entities = $Model->loadInto($entities, $contain);

		return $entities;
	}

	protected static function _prepareAuthorizationScopes(string|array $authorizationScopes): array {
		if (is_string($authorizationScopes)) {
			$authorizationScopes = [
				'default' => $authorizationScopes,
			];
		}

		if (!isset($authorizationScopes['default'])) {
			throw new \InvalidArgumentException('Authorization scopes require "default" key');
		}

		return $authorizationScopes;
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

	protected static function _getFieldDependency(string $entityClass, string $field): FieldDependencies|null {
		return Cache::remember(static::_escapeCacheKey($entityClass . '::' . $field), function () use ($field, $entityClass) {
			$entityReflection = new ReflectionClass($entityClass);
			$methodReflection = static::_getFieldGetterReflection($entityReflection, $field);
			if (!$methodReflection) {
				return null;
			}

			$dependencyAttribute = $methodReflection->getAttributes(FieldDependencies::class)[0] ?? null;

			if (!$dependencyAttribute) {
				return null;
			}

			return new FieldDependencies($dependencyAttribute->getArguments());
		}, 'graphql');
	}

	protected static function _generateFields(array $_fields, \Cake\ORM\Table $Model) {
		$forceFields = $Model->forceFields ?? [];
		foreach ($forceFields as $forceField) {
			if ($_fields[$forceField] ?? false) {
				continue;
			}

			yield $forceField => true;
		}

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

			$dependency = static::_getFieldDependency($Model->getEntityClass(), $field);
			if ($dependency) {
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
							yield $k => $v;

							continue;
						}

						yield $k => true;
					}
				}
			}

			if ($fieldsRemapped) {
				continue;
			}

			yield $field => $value;
		}
	}

	protected static function _getModelFieldsAndContain(array $_fields, \Cake\ORM\Table $Model, bool $pagination) {
		$select = [
			'id',
		];
		$contain = [];

		if ($pagination) {
			$_fields = $_fields['items'] ?? $_fields;
		}

		$fields = static::_generateFields($_fields, $Model);

		foreach ($fields as $field => $value) {
			if (is_array($value)) {
				$contain[$field] = $value;
				continue;
			}

			/** @var callable|null $virtualField */
			$virtualField = $Model->virtualFields[$field] ?? false;
			if ($virtualField) {
				$select[$field] = '__virtual_field__';
				continue;
			}

			if ($Model->hasField($field)) {
				$select[] = $field;
				continue;
			}

			$sourceName = self::getSourceNameForField($Model, $field, [$Model, 'hasField']);
			if (!$sourceName) {
				continue;
			}

			$select[] = $sourceName;
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

		return [
			'select' => $select,
			'contain' => $contain,
		];
	}

	/**
	 * @param array $select
	 * @param Table $Model
	 * @param SelectQuery $query
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected static function _applyVirtualFields(array $select, Table $Model, SelectQuery $query): mixed {
		foreach ($select as $field => $value) {
			if ($value !== '__virtual_field__') {
				continue;
			}

			/** @var callable|null $virtualField */
			$virtualField = $Model->virtualFields[$field] ?? false;
			if (!$virtualField) {
				throw new \RuntimeException('could not resolve virtual Field: ' . $field);
			}

			$select[$field] = $virtualField($query);
		}

		return $select;
	}

	/**
	 * @param ResolveInfo $info
	 * @param Table $Model
	 * @param bool $pagination
	 * @param SelectQuery $query
	 * @return array
	 * @throws \RuntimeException
	 */
	protected static function _getModelFieldsAndContainCached(ResolveInfo $info, Table $Model, bool $pagination, SelectQuery $query): array {
		$_fields = $info->getFieldSelection(6);
		$key = 'cake-query-fields-' . static::_escapeCacheKey($Model->getEntityClass()) . '-' . hash('xxh128', serialize($_fields));
		['select' => $select, 'contain' => $contain] = Cache::remember($key, fn() => static::_getModelFieldsAndContain($_fields, $Model, $pagination), 'graphql');

		try {
			$select = static::_applyVirtualFields($select, $Model, $query);
		} catch (\RuntimeException $e) {
			// A virtual field could not be resolved. Try again without cache...
			static::_getModelFieldsAndContain($_fields, $Model, $pagination);
			$select = static::_applyVirtualFields($select, $Model, $query);
		}

		return [
			'select' => $select,
			'contain' => $contain,
		];
	}

	protected static function getRequestedQueryFields(ResolveInfo $info, SelectQuery $query, AuthorizationServiceInterface $authorizationService, ?IdentityInterface $identity, array $authorizationScopes, bool $pagination = false): array {
		/** @var \Cake\ORM\Table $Model */
		$Model = FactoryLocator::get('Table')->get($query->getRepository()->getRegistryAlias());

		['select' => $select, 'contain' => $contain] = static::_getModelFieldsAndContainCached($info, $Model, $pagination, $query);

		foreach ($contain as $key => $value) {
			$contain[$key] = function (SelectQuery $q) use ($authorizationService, $query, $authorizationScopes, $identity, $value, $key) {
				/** @var SelectQuery $q */
				$q = $authorizationService->applyScope($identity, $authorizationScopes[$key] ?? $authorizationScopes['assoc'] ?? $authorizationScopes['default'], $q);

				$AssocModel = $q->getRepository();
				$fields = array_filter(
					array_map(
						function ($field) use ($AssocModel) {
							if ($AssocModel->hasField($field)) {
								return $field;
							}

							return self::getSourceNameForField($AssocModel, $field, [$AssocModel, 'hasField']);
						},
						$value['fields'] ?? []
					)
				);

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
	 * @throws \Exception
	 * @see Query::contain()
	 */
	protected static function getContainKeys(\Cake\ORM\Table $Model, array $items, string $key = ''): array {
		$result = [];

		$fields = static::_generateFields($items, $Model);

		foreach ($fields as $itemKey => $item) {
			if ($itemKey == '__typename') {
				continue;
			}

			if ($item === true) {
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

			$itemKey = static::getRealContainItemKey($Model, $itemKey);

			if (!$itemKey) {
				continue;
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

	protected static function getRealContainItemKey(Table $Model, string $itemKey): ?string {
		$tryKey = Inflector::camelize($itemKey);

		if ($Model->hasAssociation($tryKey)) {
			return $tryKey;
		}

		$tryKey = Inflector::pluralize($tryKey);

		if ($Model->hasAssociation($tryKey)) {
			return $tryKey;
		}

		return self::getSourceNameForField($Model, $itemKey, function (string $itemKey) use ($Model) {
			$tryKey = Inflector::camelize($itemKey);

			if ($Model->hasAssociation($tryKey)) {
				return $tryKey;
			}

			$tryKey = Inflector::pluralize($tryKey);

			if ($Model->hasAssociation($tryKey)) {
				return $tryKey;
			}

			return null;
		});
	}

	protected static function checkAndAddSelect(array $select, string $item): array {
		if (in_array($item, $select)) {
			return $select;
		}

		$select[] = $item;

		return $select;
	}

	/**
	 * @param \Cake\ORM\Table $Model
	 * @param string $field
	 * @param callable(string $field): boolean|string|null $validateField
	 * @return string|null
	 * @throws \ReflectionException
	 */
	protected static function getSourceNameForField(\Cake\ORM\Table $Model, string $field, callable $validateField): ?string {
		$class = $Model->getEntityClass();
		$magicFieldArgs = Cache::remember(static::_escapeCacheKey('cake-query-optimizer_' . $class), function () use ($class) {
			$entityReflection = new \ReflectionClass($class);
			return array_map(fn (\ReflectionAttribute $a) => $a->getArguments(), $entityReflection->getAttributes(MagicField::class));
		}, 'graphql');

		foreach ($magicFieldArgs as $args) {
			$name = $args['name'] ?? null;

			if (!$name || $name !== $field) {
				continue;
			}

			$sourceName = $args['sourceName'] ?? null;
			if ($sourceName === null) {
				continue;
			}

			$res = $validateField($sourceName);
			if ($res === false || $res === null) {
				continue;
			}

			if (is_string($res)) {
				return $res;
			}

			return $sourceName;
		}

		return null;
	}

	protected static function _escapeCacheKey(string $key): string {
		return str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', $key);
	}
}
