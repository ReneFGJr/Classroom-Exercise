<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGrupoQuestoesTable extends Migration
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
            'nome_grupo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'questoes_json' => [
                'type' => 'JSON',
                'null' => false,
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
        $this->forge->addKey('nome_grupo', false, true);

        $this->forge->createTable('grupo_questoes', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('grupo_questoes', true);
    }
}