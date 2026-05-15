<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGrupoQuestoesQuerstaoFieldsToGrupoAvaliacao extends Migration
{
    public function up(): void
    {
        if (! $this->tabelaExiste('grupo_avaliacao')) {
            return;
        }

        $novosCampos = [];

        for ($indice = 1; $indice <= 10; $indice++) {
            $nomeCampo = 'grupo_questoes_querstao_' . $indice;

            if (! $this->campoExiste('grupo_avaliacao', $nomeCampo)) {
                $novosCampos[$nomeCampo] = [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ];
            }
        }

        if ($novosCampos !== []) {
            $this->forge->addColumn('grupo_avaliacao', $novosCampos);
        }
    }

    public function down(): void
    {
        if (! $this->tabelaExiste('grupo_avaliacao')) {
            return;
        }

        for ($indice = 1; $indice <= 10; $indice++) {
            $nomeCampo = 'grupo_questoes_querstao_' . $indice;

            if ($this->campoExiste('grupo_avaliacao', $nomeCampo)) {
                $this->forge->dropColumn('grupo_avaliacao', $nomeCampo);
            }
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
