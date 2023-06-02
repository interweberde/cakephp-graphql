<?php
declare(strict_types=1);

namespace TestApp\Identifier\Resolver;

use Authentication\Identifier\Resolver\ResolverInterface;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class TokenResolver implements ResolverInterface {
	/**
	 * @inheritDoc
	 */
	public function find(array $conditions, string $type = self::TYPE_AND) {
		$token = Configure::read('Development.token', null);

		if (!$token) {
			return null;
		}

		return TableRegistry::getTableLocator()->get('Users')->defaultUser();
	}
}
