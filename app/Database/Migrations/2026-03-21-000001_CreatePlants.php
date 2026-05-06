<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlants extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'variety' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'date_planted' => [
                'type' => 'DATE',
            ],
            'predicted_harvest' => [
                'type' => 'DATE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('plants', true);
    }

    public function down()
    {
        $this->forge->dropTable('plants', true);
    }
}
