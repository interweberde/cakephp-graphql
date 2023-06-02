<?php
declare(strict_types=1);

namespace TestApp\Services;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Cake\Core\Configure;
use Psr\Http\Message\ServerRequestInterface;
use TestApp\Identifier\Resolver\TokenResolver;

class AuthenticationServiceProvider implements \Authentication\AuthenticationServiceProviderInterface {
	public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface {
		$service = new AuthenticationService();

		$service->loadAuthenticator('Authentication.Token', [
			'header' => Configure::read('Authentication.tokenHeader', 'Authorization'),
		]);

		$service->loadAuthenticator('Authentication.Session');

		$service->loadIdentifier('Authentication.Token', [
			'resolver' => [
				'className' => TokenResolver::class,
			],
		]);

		return $service;
	}
}
