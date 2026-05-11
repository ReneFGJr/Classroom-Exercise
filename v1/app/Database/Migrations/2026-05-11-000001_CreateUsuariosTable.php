<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuariosTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'usuario' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'idcard' => [
                'type'       => 'CHAR',
                'constraint' => 8,
            ],
            'senha' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'primeiro_acesso' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'data_primeira_entrada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_admin' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'nome_completo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
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
        $this->forge->addKey('usuario', false, true);
        $this->forge->addKey('idcard', true);
        $this->forge->addKey('is_admin');
        $this->forge->addKey('primeiro_acesso');

        $this->forge->createTable('usuarios', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('usuarios', true);
    }
}