<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionsTable extends Migration
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
            'enunciado_questao' => [
                'type' => 'TEXT',
            ],
            'tipo_resposta_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'resposta_correta' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'justificativa_resposta' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'resposta_1' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'resposta_2' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'resposta_3' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'resposta_4' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'resposta_5' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'nivel_dificuldade' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
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
        $this->forge->addKey('tipo_resposta_id');
        $this->forge->addForeignKey('tipo_resposta_id', 'tipos_resposta', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('questions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('questions', true);
    }
}