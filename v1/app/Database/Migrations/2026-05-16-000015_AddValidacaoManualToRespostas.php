<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddValidacaoManualToRespostas extends Migration
{
    public function up(): void
    {
        if (! $this->tabelaExiste('respostas')) {
            return;
        }

        if (! $this->campoExiste('respostas', 'validacao_manual')) {
            $this->forge->addColumn('respostas', [
                'validacao_manual' => [
                    'type' => 'BOOLEAN',
                    'default' => false,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if (! $this->tabelaExiste('respostas')) {
            return;
        }

        if ($this->campoExiste('respostas', 'validacao_manual')) {
            $this->forge->dropColumn('respostas', 'validacao_manual');
        }
    }

    private function tabelaExiste(string $tabela): bool
    {
        return $this->db->query("SHOW TABLES LIKE '{$tabela}'")->getRowArray() !== null;
    }

    private function campoExiste(string $tabela, string $campo): bool
    {
        return $this->db->query("SHOW COLUMNS FROM {$tabela} LIKE '{$campo}'")->getRowArray() !== null;
    }
}
