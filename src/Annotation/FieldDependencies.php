<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("dependencies", type="array<string|string[]>"),
 *   @Attribute("remapFields", type="string|null"),
 * })
 */
#[Attribute(Attribute::TARGET_METHOD)]
class FieldDependencies {
	/**
	 * @var array<string|string[]>
	 */
	protected array $dependencies;

	protected ?string $remapFields;

	/**
	 * @param array $attributes
	 * @param array<string|string[]> $dependencies
	 * @param string|null $remapFields
	 */
	public function __construct(array $attributes = [], ?array $dependencies = null, ?string $remapFields = null) {
		$this->dependencies = $dependencies ?? $attributes['dependencies'] ?? [];
		$this->remapFields = $remapFields ?? $attributes['remapFields'] ?? null;
	}

	/**
	 * @return array<string|string[]>
	 */
	public function getDependencies(): array {
		return $this->dependencies;
	}

	public function getRemapFields(): ?string {
		return $this->remapFields;
	}
}
