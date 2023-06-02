<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Test\TestCase;

use Cake\TestSuite\IntegrationTestTrait;

trait GraphQLTestTrait {
	use IntegrationTestTrait;

	protected function prepareRequest() {
		$Users = $this->getTableLocator()->get('Users');
		try {
			$user = $Users->get(1);
		} catch (\Throwable $e) {
			throw new \Exception('User not found. Ensure that fixture "app.Users" is loaded.');
		}

		if ($user->isNew()) {
			$Users->saveOrFail($user);
		}

		$this->session([
			'Auth' => $user,
		]);
	}

	protected function processRequest() {
		return json_decode($this->_response->getBody()->getContents(), true);
	}

	public function query(string $query, array $variables = []): array {
		$this->prepareRequest();

		$this->post('/__graphql', json_encode([
			'query' => $query,
			'variables' => $variables,
		]));

		return $this->processRequest();
	}
}
