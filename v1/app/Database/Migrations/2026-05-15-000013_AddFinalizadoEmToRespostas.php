<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFinalizadoEmToRespostas extends Migration
{
    public function up(): void
    {
        if (! $this->tabelaExiste('respostas')) {
            return;
        }

        if ($this->db->query("SHOW COLUMNS FROM respostas LIKE 'finalizado_em'")->getRowArray() !== null) {
            return;
        }

        $this->forge->addColumn('respostas', [
            'finalizado_em' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'resposta_texto',
            ],
        ]);
    }

    public function down(): void
    {
        if (! $this->tabelaExiste('respostas')) {
            return;
        }

        if ($this->db->query("SHOW COLUMNS FROM respostas LIKE 'finalizado_em'")->getRowArray() === null) {
            return;
        }

        $this->forge->dropColumn('respostas', 'finalizado_em');
    }

    private function tabelaExiste(string $tabela): bool
    {
        return $this->db->query("SHOW TABLES LIKE '{$tabela}'")->getRowArray() !== null;
    }
}
