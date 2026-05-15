<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $avaliacoes = [];
        $usuarioBasico = null;

        if ($db->tableExists('grupo_avaliacao')) {
            $selectCampos = ['id', 'nome_disciplina', 'created_at'];

            foreach (['data_inicio_avaliacao', 'data_fim_avaliacao', 'hora_inicio', 'hora_fim', 'duracao_prova_horas'] as $campo) {
                if ($this->campoExiste('grupo_avaliacao', $campo)) {
                    $selectCampos[] = $campo;
                }
            }

            $avaliacoes = $db->table('grupo_avaliacao')
                ->select(implode(', ', $selectCampos))
                ->orderBy('nome_disciplina', 'ASC')
                ->get()
                ->getResultArray();
        }

        $usuarioId = (int) (session('auth_user_id') ?? 0);

        if ($usuarioId > 0 && $db->tableExists('usuarios')) {
            $camposUsuario = ['id', 'nome_completo', 'usuario', 'idcard'];

            if ($this->campoExiste('usuarios', 'email')) {
                $camposUsuario[] = 'email';
            }

            $usuario = $db->table('usuarios')
                ->select(implode(', ', $camposUsuario))
                ->where('id', $usuarioId)
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($usuario !== null) {
                $nome = trim((string) ($usuario['nome_completo'] ?? ''));
                if ($nome === '') {
                    $nome = trim((string) ($usuario['usuario'] ?? ''));
                }

                $usuarioBasico = [
                    'idcard' => trim((string) ($usuario['idcard'] ?? '')),
                    'nome' => $nome,
                    'email' => trim((string) ($usuario['email'] ?? '')),
                ];
            }
        }

        return view('welcome_message', [
            'avaliacoes' => $avaliacoes,
            'usuario_basico' => $usuarioBasico,
        ]);
    }

    private function campoExiste(string $tabela, string $campo): bool
    {
        $db = db_connect();

        return $db->query("SHOW COLUMNS FROM {$tabela} LIKE '{$campo}'")->getRowArray() !== null;
    }
}
