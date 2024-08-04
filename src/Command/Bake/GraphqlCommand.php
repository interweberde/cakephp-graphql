<?php

namespace Interweber\GraphQL\Command\Bake;

use Bake\Command\BakeCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class GraphqlCommand extends BakeCommand {
	public function execute(Arguments $args, ConsoleIo $io) {
		$command = new GraphqlFilterCommand();
		$command->execute($args, $io);

		$command = new GraphqlSorterCommand();
		$command->execute($args, $io);

		$command = new GraphqlFactoryCommand();
		$command->execute($args, $io);

		$command = new GraphqlControllerCommand();
		$command->execute($args, $io);
	}

	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->_setCommonOptions($parser);
        $parser->setDescription(
            sprintf('Bake GraphQL class files.')
        )->addArgument('name', [
            'help' => 'Name of the entity to bake for. Can use Plugin.name to bake files into plugins.',
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test skeleton.',
        ])->addOption('allow_unauthenticated', [
			'multiple' => true,
			'choices' => [
				'index',
				'view',
				'create',
				'update',
				'delete',
			]
		]);

        return $parser;
    }
}
