<?php
declare(strict_types=1);

namespace Interweber\GraphQL\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use GraphQL\Utils\SchemaPrinter;
use Interweber\GraphQL\Classes\SchemaGenerator;

/**
 * PrintSchema command.
 */
class PrintSchemaCommand extends Command {
	/**
	 * Hook method for defining this command's option parser.
	 *
	 * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
	 * @return \Cake\Console\ConsoleOptionParser The built parser.
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser = parent::buildOptionParser($parser);

		$parser
			->addArgument('file', ['required' => true]);

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
		$schema = SchemaPrinter::doPrint(SchemaGenerator::generateSchema());

		file_put_contents($args->getArgument('file'), $schema);
	}
}
