<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPeriodoAvaliacaoToGrupoAvaliacao extends Migration
{
    public function up(): void
    {
        if (! $this->tabelaExiste('grupo_avaliacao')) {
            return;
        }

        $novosCampos = [];

        if (! $this->campoExiste('grupo_avaliacao', 'data_inicio_avaliacao')) {
            $novosCampos['data_inicio_avaliacao'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (! $this->campoExiste('grupo_avaliacao', 'data_fim_avaliacao')) {
            $novosCampos['data_fim_avaliacao'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (! $this->campoExiste('grupo_avaliacao', 'hora_inicio')) {
            $novosCampos['hora_inicio'] = [
                'type' => 'TIME',
                'null' => true,
            ];
        }

        if (! $this->campoExiste('grupo_avaliacao', 'hora_fim')) {
            $novosCampos['hora_fim'] = [
                'type' => 'TIME',
                'null' => true,
            ];
        }

        if (! $this->campoExiste('grupo_avaliacao', 'duracao_prova_horas')) {
            $novosCampos['duracao_prova_horas'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ];
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

        $campos = [
            'data_inicio_avaliacao',
            'data_fim_avaliacao',
            'hora_inicio',
            'hora_fim',
            'duracao_prova_horas',
        ];

        foreach ($campos as $campo) {
            if ($this->campoExiste('grupo_avaliacao', $campo)) {
                $this->forge->dropColumn('grupo_avaliacao', $campo);
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
