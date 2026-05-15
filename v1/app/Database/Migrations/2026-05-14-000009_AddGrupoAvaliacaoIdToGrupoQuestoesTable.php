<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGrupoAvaliacaoIdToGrupoQuestoesTable extends Migration
{
    public function up(): void
    {
        if (! $this->tabelaExiste('grupo_questoes')) {
            return;
        }

        if (! $this->campoExiste('grupo_questoes', 'grupo_avaliacao_id')) {
            $this->forge->addColumn('grupo_questoes', [
                'grupo_avaliacao_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'nome_grupo',
                ],
            ]);
        }

        $this->forge->addKey('grupo_avaliacao_id');
        $this->forge->addForeignKey('grupo_avaliacao_id', 'grupo_avaliacao', 'id', 'SET NULL', 'CASCADE');
        $this->forge->processIndexes('grupo_questoes');
    }

    public function down(): void
    {
        if (! $this->tabelaExiste('grupo_questoes')) {
            return;
        }

        if ($this->campoExiste('grupo_questoes', 'grupo_avaliacao_id')) {
            $this->forge->dropForeignKey('grupo_questoes', 'grupo_questoes_grupo_avaliacao_id_foreign');
            $this->forge->dropColumn('grupo_questoes', 'grupo_avaliacao_id');
        }
    }

    private function tabelaExiste(string $tabela): bool
    {
        return $this->db->query("SHOW TABLES LIKE '{$tabela}'")->getRowArray() !== null;
    }

    private function campoExiste(string $tabela, string $campo): bool
    {
        $coluna = $this->db->query("SHOW COLUMNS FROM {$tabela} LIKE '{$campo}'")->getRowArray();

        return $coluna !== null;
    }
}
