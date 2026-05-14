<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImportacoesXmlTable extends Migration
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
            'arquivo_nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'caminho_arquivo' => [
                'type' => 'TEXT',
            ],
            'hash_arquivo' => [
                'type'       => 'CHAR',
                'constraint' => 64,
            ],
            'disciplina' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'total_questoes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'conteudo_xml' => [
                'type' => 'LONGTEXT',
            ],
            'status_importacao' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pendente',
            ],
            'mensagem_importacao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'importado_em' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('hash_arquivo', false, true);
        $this->forge->addKey('status_importacao');
        $this->forge->addKey('disciplina');

        $this->forge->createTable('importacoes_xml', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('importacoes_xml', true);
    }
}