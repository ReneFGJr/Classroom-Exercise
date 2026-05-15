<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRespostasTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'grupo_avaliacao_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'question_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'resposta_texto' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('grupo_avaliacao_id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('question_id');
        $this->forge->addKey(['grupo_avaliacao_id', 'usuario_id', 'question_id'], false, true);

        $this->forge->addForeignKey('grupo_avaliacao_id', 'grupo_avaliacao', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('question_id', 'questions', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('respostas', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('respostas', true);
    }
}
