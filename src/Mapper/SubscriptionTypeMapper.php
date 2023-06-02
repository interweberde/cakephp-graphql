<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;

/**
 * @codeCoverageIgnore
 */
class SubscriptionTypeMapper implements RootTypeMapperInterface {
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
	): OutputType&GraphQLType {
		return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
	}

	public function toGraphQLInputType(
		Type $type,
		?InputType $subType,
		string $argumentName,
		ReflectionMethod|ReflectionProperty $reflector,
		DocBlock $docBlockObj
	): InputType&GraphQLType {
		return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
	}

	public function mapNameToType(string $typeName): NamedType&GraphQLType {
		if ($typeName === 'Subscription') {
			return new SubscriptionType();
		}

		return $this->next->mapNameToType($typeName);
	}
}
