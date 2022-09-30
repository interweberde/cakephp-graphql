<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;

class FrozenDateTypeMapper implements RootTypeMapperInterface {
	private static FrozenTimeType|null $dateTimeType = null;
	private static FrozenDateType|null $dateType = null;

	/**
	 * @param RootTypeMapperInterface $next
	 */
	public function __construct(
		private RootTypeMapperInterface $next
	) {
	}

	public function toGraphQLOutputType(
		Type $type,
		?OutputType $subType,
		$reflector,
		DocBlock $docBlockObj
	): OutputType {
		$mapped = $this->mapBaseType($type);

		if ($mapped !== null) {
			return $mapped;
		}

		return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
	}

	public function toGraphQLInputType(
		Type $type,
		?InputType $subType,
		string $argumentName,
		$reflector,
		DocBlock $docBlockObj
	): InputType {
		$mapped = $this->mapBaseType($type);

		if ($mapped !== null) {
			return $mapped;
		}

		return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
	}

	private function mapBaseType(Type $type): FrozenTimeType|FrozenDateType|null {
		if (!$type instanceof Object_) {
			return null;
		}

		$fqcn = (string) $type->getFqsen();
		if ($fqcn === '\\' . FrozenTime::class) {
			return self::getDateTimeType();
		}

		if ($fqcn === '\\' . FrozenDate::class) {
			return self::getDateType();
		}

		 return null;
	}

	private static function getDateType(): FrozenDateType {
		if (self::$dateType === null) {
			self::$dateType = new FrozenDateType();
		}

		return self::$dateType;
	}

	private static function getDateTimeType(): FrozenTimeType {
		if (self::$dateTimeType === null) {
			self::$dateTimeType = new FrozenTimeType();
		}

		return self::$dateTimeType;
	}

	public function mapNameToType(string $typeName): NamedType {
		if ($typeName === 'FrozenTime') {
			return self::getDateTimeType();
		}

		if ($typeName === 'FrozenDate') {
			return self::getDateType();
		}

		return $this->next->mapNameToType($typeName);
	}
}
