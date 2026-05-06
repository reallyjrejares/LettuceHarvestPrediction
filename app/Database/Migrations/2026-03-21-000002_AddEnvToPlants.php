<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEnvToPlants extends Migration
{
    public function up()
    {
        $fields = [
            'temperature_c' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'after' => 'predicted_harvest',
            ],
            'humidity_pct' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'after' => 'temperature_c',
            ],
            'tds_ppm' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'null' => true,
                'after' => 'humidity_pct',
            ],
            'ph_level' => [
                'type' => 'DECIMAL',
                'constraint' => '4,2',
                'null' => true,
                'after' => 'tds_ppm',
            ],
        ];

        $this->forge->addColumn('plants', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('plants', ['temperature_c', 'humidity_pct', 'tds_ppm', 'ph_level']);
    }
}
