<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdmins extends Migration
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
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
            ],
            'password_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('admins', true);

        // Move old admin-role user accounts to dedicated admins table.
        $adminUsers = [];
        if ($this->db->tableExists('users') && $this->db->fieldExists('role', 'users')) {
            $adminUsers = $this->db->table('users')
                ->select('id, username, password_hash')
                ->where('role', 'admin')
                ->get()
                ->getResultArray();
        }

        $adminTable = $this->db->table('admins');
        $userTable = $this->db->table('users');
        $plantTable = $this->db->table('plants');

        foreach ($adminUsers as $row) {
            $username = (string) ($row['username'] ?? '');
            if ($username === '') {
                continue;
            }

            $exists = $adminTable
                ->select('id')
                ->where('username', $username)
                ->countAllResults();

            if ($exists === 0) {
                $adminTable->insert([
                    'username' => $username,
                    'password_hash' => (string) ($row['password_hash'] ?? ''),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $oldUserId = (int) ($row['id'] ?? 0);
            if ($oldUserId > 0) {
                if ($this->db->tableExists('plants')) {
                    $plantTable->where('user_id', $oldUserId)->delete();
                }
                $userTable->where('id', $oldUserId)->delete();
            }
        }

        // Ensure there is at least one admin account after migration.
        $adminCount = $adminTable->countAllResults();
        if ($adminCount === 0) {
            $adminTable->insert([
                'username' => 'administrator',
                'password_hash' => password_hash('@adminpoko123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('admins', true);
    }
}

