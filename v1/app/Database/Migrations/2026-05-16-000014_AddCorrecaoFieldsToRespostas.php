<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCorrecaoFieldsToRespostas extends Migration
{
    public function up(): void
    {
        if (! $this->tabelaExiste('respostas')) {
            return;
        }

        $novasColunas = [];

        if (! $this->campoExiste('respostas', 'corrigido')) {
            $novasColunas['corrigido'] = [
                'type' => 'BOOLEAN',
                'default' => false,
            ];
        }

        if (! $this->campoExiste('respostas', 'comentarios_correcao')) {
            $novasColunas['comentarios_correcao'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (! $this->campoExiste('respostas', 'nota')) {
            $novasColunas['nota'] = [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ];
        }

        if ($novasColunas !== []) {
            $this->forge->addColumn('respostas', $novasColunas);
        }
    }

    public function down(): void
    {
        if (! $this->tabelaExiste('respostas')) {
            return;
        }

        foreach (['corrigido', 'comentarios_correcao', 'nota'] as $campo) {
            if ($this->campoExiste('respostas', $campo)) {
                $this->forge->dropColumn('respostas', $campo);
            }
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
