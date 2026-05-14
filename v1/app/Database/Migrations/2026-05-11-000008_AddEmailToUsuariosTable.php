<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailToUsuariosTable extends Migration
{
    public function up(): void
    {
        if (! $this->tabelaExiste('usuarios')) {
            return;
        }

        if (! $this->campoExiste('usuarios', 'email')) {
            $this->forge->addColumn('usuarios', [
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                    'after'      => 'idcard',
                ],
            ]);
        }

        $index = $this->db->query("SHOW INDEX FROM usuarios WHERE Key_name = 'usuarios_email_unique'")->getRowArray();

        if ($index === null) {
            $this->db->query('ALTER TABLE usuarios ADD UNIQUE KEY usuarios_email_unique (email)');
        }
    }

    public function down(): void
    {
        if (! $this->tabelaExiste('usuarios')) {
            return;
        }

        $index = $this->db->query("SHOW INDEX FROM usuarios WHERE Key_name = 'usuarios_email_unique'")->getRowArray();

        if ($index !== null) {
            $this->db->query('ALTER TABLE usuarios DROP INDEX usuarios_email_unique');
        }

        if ($this->campoExiste('usuarios', 'email')) {
            $this->forge->dropColumn('usuarios', 'email');
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