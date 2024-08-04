<?php

namespace Interweber\GraphQL\Classes;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException as GraphQLException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class RequestParameter implements ParameterInterface {

	/**
	 * @param object|null $source
	 * @param array<string, mixed> $args
	 * @param mixed $context
	 * @param ResolveInfo $info
	 * @return \Cake\Http\ServerRequest
	 */
	public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): \Cake\Http\ServerRequest {
		if (!$context instanceof CakePHPRequestContextInterface) {
			throw new GraphQLException('Cannot type-hint on a CakePHP Request object in your query/mutation/field. The request context must implement CakePHPRequestContextInterface.');
		}

		return $context->getRequest();
	}
}
