<?php

namespace Interweber\GraphQL\Classes;

use Cake\Http\ServerRequest;

interface CakePHPRequestContextInterface {
	/**
     * @return ServerRequest
     */
    public function getRequest(): ServerRequest;
}
