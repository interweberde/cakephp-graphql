<?php
declare(strict_types=1);

namespace TestApp\Services;

use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\Policy\OrmResolver;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationServiceProvider implements \Authorization\AuthorizationServiceProviderInterface {
	public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface {
		$ormResolver = new OrmResolver();

		return new AuthorizationService($ormResolver);
	}
}
