<?php

namespace Interweber\GraphQL\Classes;

use Cake\Http\ServerRequest;
use TheCodingMachine\GraphQLite\Context\Context;

class CakePHPRequestContext extends Context implements CakePHPRequestContextInterface {
	public function __construct(
		protected readonly ServerRequest $request,
	) {
		parent::__construct();
	}

	/**
	 * @inheritDoc
	 */
	public function getRequest(): ServerRequest {
		return $this->request;
	}
}
