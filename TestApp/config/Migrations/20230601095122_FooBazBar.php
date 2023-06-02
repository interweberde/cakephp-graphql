<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class FooBazBar extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void {
		$this->table('users')
			->addColumn('title', \Phinx\Db\Table\Column::STRING)
			->create();

		$this->table('foos')
			->addColumn('foo_bool', \Phinx\Db\Table\Column::BOOLEAN, ['null' => true])
			->addColumn('foo_date', \Phinx\Db\Table\Column::DATE, ['null' => true])
			->addColumn('foo_datetime', \Phinx\Db\Table\Column::DATETIME, ['null' => true])
			->addColumn('foo_float', \Phinx\Db\Table\Column::FLOAT, ['null' => true])
			->addColumn('foo_int', \Phinx\Db\Table\Column::INTEGER, ['null' => true])
			->addColumn('foo_str', \Phinx\Db\Table\Column::STRING, ['null' => true])
			->create();

		$this->table('bazs')
			->addColumn('foo_id', \Phinx\Db\Table\Column::INTEGER)
			->addColumn('title', \Phinx\Db\Table\Column::STRING)
			->create();

		$this->table('bars')
			->addColumn('title', \Phinx\Db\Table\Column::STRING)
			->create();

		$this->table('bars_bazs')
			->addColumn('bar_id', \Phinx\Db\Table\Column::INTEGER)
			->addColumn('baz_id', \Phinx\Db\Table\Column::INTEGER)
			->create();

		$this->table('quxs')
			->addColumn('bar_id', \Phinx\Db\Table\Column::INTEGER)
			->addColumn('title', \Phinx\Db\Table\Column::STRING)
			->create();
    }
}
