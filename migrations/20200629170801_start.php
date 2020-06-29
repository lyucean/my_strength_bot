<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Start extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('chat_history', ['id' => 'chat_history_id']);
        $table->addColumn('chat_id', 'integer', ['null' => false])
            ->addColumn('first_name', 'string', ['limit' => 100])
            ->addColumn('last_name', 'string', ['limit' => 100])
            ->addColumn('user_name', 'string', ['limit' => 100])
            ->addColumn('text', 'string', ['limit' => 4096])
            ->addColumn('date_added', 'datetime')
            ->create();

        $table = $this->table('command_waiting', ['id' => false, 'primary_key' => 'chat_id']);
        $table->addColumn('chat_id', 'integer', ['null' => false])
            ->addColumn('command', 'string', ['limit' => 100])
            ->addColumn('date_added', 'datetime')
            ->create();

        $table = $this->table('message', ['id' => false, 'primary_key' => 'message_id']);
        $table->addColumn('message_id', 'integer', ['null' => false])
            ->addColumn('chat_id', 'integer', ['null' => false])
            ->addColumn('text', 'string', ['limit' => 4096])
            ->addColumn('image', 'string', ['limit' => 100])
            ->addColumn('view', 'integer', ['default' => 0])
            ->addColumn('display', 'integer', ['default' => 1])
            ->addColumn('date_reminder', 'datetime')
            ->addColumn('date_added', 'datetime')
            ->create();

        $table = $this->table('schedule', ['id' => false, 'primary_key' => 'chat_id']);
        $table->addColumn('chat_id', 'integer', ['null' => false])
            ->addColumn('hour_start', 'integer', ['default' => 9])
            ->addColumn('hour_end', 'integer', ['default' => 14])
            ->addColumn('time_zone_offset', 'integer', ['default' => 3])
            ->addColumn('quantity', 'integer', ['default' => 1])
            ->addColumn('date_modified', 'datetime')
            ->create();


        $table = $this->table('schedule_daily', ['id' => 'schedule_daily_id']);
        $table->addColumn('chat_id', 'integer', ['null' => false])
            ->addColumn('date_time', 'datetime')
            ->addColumn('status_sent', 'integer', ['default' => 3])
            ->create();
    }
}
