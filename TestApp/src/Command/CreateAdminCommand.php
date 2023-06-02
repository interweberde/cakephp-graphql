<?php
declare(strict_types=1);

namespace TestApp\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * CreateAdmin command.
 */
class CreateAdminCommand extends Command {
	/**
	 * Hook method for defining this command's option parser.
	 *
	 * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
	 * @return \Cake\Console\ConsoleOptionParser The built parser.
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser = parent::buildOptionParser($parser);

		return $parser;
	}

	/**
	 * Implement this method with your command's logic.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io) {
		$Users = $this->getTableLocator()->get('Users');

		$email = $io->ask('E-Mail: ', 'test@example.org');
		$password = $io->ask('Password: ', 'password');

		$first_name = $io->ask('First Name: ', 'Tester');
		$middle_name = $io->ask('Middle Name: ', '');
		$last_name = $io->ask('Last Name: ', 'Example');

		$user = $Users->newEntity([
			'first_name' => $first_name,
			'middle_name' => $middle_name ?: null,
			'last_name' => $last_name,
			'email' => $email,
			'password' => $password,
		]);

		$Users->saveOrFail($user);

		$io->success('User created');
	}
}
