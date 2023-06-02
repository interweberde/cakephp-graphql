<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Authorization\AuthorizationServiceInterface;
use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\ResultInterface;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Interweber\GraphQL\Classes\UserInterface;

/**
 * User Entity
 *
 * @property int $id
 * @property string $title
 */
class User extends Entity implements UserInterface {
	protected AuthorizationServiceInterface $authorization;

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array<string, bool>
	 */
	protected $_accessible = [
		'id' => false,
		'title' => true,
	];

	public function can(string $action, $resource): bool {
		return $this->canResult($action, $resource)->getStatus();
	}

	public function canResult(string $action, $resource): ResultInterface {
		return $this->authorization->canResult($this, $action, $resource);
	}

	public function applyScope(string $action, $resource) {
		try {
			return $this->authorization->applyScope($this, $action, $resource);
		} catch (MissingPolicyException $e) {
			if (!($resource instanceof Query)) {
				throw $e;
			}

			throw new MissingPolicyException(
				sprintf("Missing Policy for '%s'", get_class($resource->getRepository())),
				(int) $e->getCode(),
				$e
			);
		}
	}

	public function getIdentifier() {
		return $this->id;
	}

	public function getOriginalData() {
		return $this;
	}

	public function isAllowedTo(string $right): bool {
		return false;
	}

	public function setAuthorization(AuthorizationServiceInterface $service) {
		$this->authorization = $service;

		return $this;
	}
}
