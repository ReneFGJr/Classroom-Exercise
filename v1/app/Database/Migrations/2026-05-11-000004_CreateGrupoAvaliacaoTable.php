<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGrupoAvaliacaoTable extends Migration
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
            'nome_disciplina' => [
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
        $this->forge->addKey('nome_disciplina', false, true);

        $this->forge->createTable('grupo_avaliacao', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('grupo_avaliacao', true);
    }
}