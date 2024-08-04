<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Authentication\IdentityInterface;
use Authorization\AuthorizationServiceInterface;
use Cake\Http\Exception\InternalErrorException;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;

class AuthenticationService implements AuthenticationServiceInterface {
	public function __construct(protected readonly ServerRequestInterface $request) {
	}

	private \Authentication\AuthenticationServiceInterface|null $authentication = null;

	private AuthorizationServiceInterface|null $authorization = null;

	protected function getAuthenticationService(): \Authentication\AuthenticationServiceInterface {
		if ($this->authentication) {
			return $this->authentication;
		}

		$authentication = $this->request->getAttribute('authentication');

		if (!$authentication) {
			throw new InternalErrorException('Authentication Service is missing!');
		}

		return $this->authentication = $authentication;
	}

	protected function getAuthorizationService(): AuthorizationServiceInterface {
		if ($this->authorization) {
			return $this->authorization;
		}

		$authorization = $this->request->getAttribute('authorization');

		if (!$authorization) {
			throw new InternalErrorException('Authorization Service is missing!');
		}

		return $this->authorization = $authorization;
	}

	/**
	 * @inheritDoc
	 */
	public function isLogged(): bool {
		$result = $this->getAuthenticationService()->getResult();
		if (!$result || !$result->isValid()) {
			return false;
		}

		return (bool) $this->getAuthenticationService()->getIdentity();
	}

	/**
	 * Returns an object representing the current logged user.
	 * Can return null if the user is not logged.
	 *
	 * @return IdentityInterface|null
	 */
	public function getUser(): ?IdentityInterface {
		if (!$this->isLogged()) {
			return null;
		}

		return $this->getAuthenticationService()->getIdentity();
	}
}
