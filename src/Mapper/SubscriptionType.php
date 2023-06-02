<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Mapper;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

class SubscriptionType extends ScalarType {
	/**
	 * @var string
	 */
	public string $name = 'Subscription';

	/**
	 * @var string|null
	 */
	public string|null $description = 'Empty Stub';

	public function parseValue($value): null {
		return null;
	}

	public function serialize($value) {
		return null;
	}

	public function parseLiteral(Node $valueNode, ?array $variables = null) {
		return null;
	}
}
