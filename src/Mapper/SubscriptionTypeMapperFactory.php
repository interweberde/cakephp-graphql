<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryContext;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;

/**
 * @codeCoverageIgnore
 */
class SubscriptionTypeMapperFactory implements RootTypeMapperFactoryInterface {
	public function create(
		RootTypeMapperInterface $next,
		RootTypeMapperFactoryContext $context
	): RootTypeMapperInterface {
		return new SubscriptionTypeMapper($next);
	}
}
