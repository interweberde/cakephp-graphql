<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Classes;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StaticRequestHandler implements RequestHandlerInterface {
	/**
	 * @var \Psr\Http\Message\ResponseInterface
	 */
	private $response;

	public function __construct(ResponseInterface $response) {
		$this->response = $response;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface {
		return $this->response;
	}
}
