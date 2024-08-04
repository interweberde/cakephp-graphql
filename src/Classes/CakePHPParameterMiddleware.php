<?php

namespace Interweber\GraphQL\Classes;

use Authorization\AuthorizationServiceInterface;
use Cake\Http\ServerRequest;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterHandlerInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewareInterface;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class CakePHPParameterMiddleware implements ParameterMiddlewareInterface
{
    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $next): ParameterInterface
    {
        $parameterType = $parameter->getType();
        if ($parameterType instanceof ReflectionNamedType) {
			if (
				$parameterType->getName() === ServerRequest::class
				|| $parameterType->getName() === ServerRequestInterface::class
			) {
				return new RequestParameter();
			}
        }

		$parameterType = $parameter->getType();
        if ($parameterType instanceof ReflectionNamedType) {
			if (
				$parameterType->getName() === AuthorizationServiceInterface::class
			) {
				return new AuthorizationServiceParameter();
			}
        }

        return $next->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
    }
}
