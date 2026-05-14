<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDefinicaoAtividadeTable extends Migration
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
            'nome_atividade' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'resposta_imediata' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'avaliacao' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'grupo_avaliacao_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'grupo_questoes_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addKey('grupo_questoes_id');
        $this->forge->addForeignKey('grupo_avaliacao_id', 'grupo_avaliacao', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('grupo_questoes_id', 'grupo_questoes', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('definicao_atividade', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('definicao_atividade', true);
    }
}