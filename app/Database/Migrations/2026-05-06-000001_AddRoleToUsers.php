<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'user',
                'after' => 'password_hash',
            ],
        ]);

        $userTable = $this->db->table('users');
        $adminCount = $userTable->where('role', 'admin')->countAllResults();
        if ($adminCount === 0) {
            $firstUser = $this->db->table('users')
                ->select('id')
                ->orderBy('id', 'ASC')
                ->get(1)
                ->getRowArray();

            if ($firstUser && isset($firstUser['id'])) {
                $this->db->table('users')
                    ->where('id', (int) $firstUser['id'])
                    ->update(['role' => 'admin']);
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'role');
    }
}
