<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $avaliacoes = [];

        if ($db->tableExists('grupo_avaliacao')) {
            $selectCampos = ['id', 'nome_disciplina', 'created_at'];

            foreach (['data_inicio_avaliacao', 'data_fim_avaliacao', 'hora_inicio', 'hora_fim', 'duracao_prova_horas'] as $campo) {
                if ($this->campoExiste($campo)) {
                    $selectCampos[] = $campo;
                }
            }

            $avaliacoes = $db->table('grupo_avaliacao')
                ->select(implode(', ', $selectCampos))
                ->orderBy('nome_disciplina', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('welcome_message', [
            'avaliacoes' => $avaliacoes,
        ]);
    }

    private function campoExiste(string $campo): bool
    {
        $db = db_connect();

        return $db->query("SHOW COLUMNS FROM grupo_avaliacao LIKE '{$campo}'")->getRowArray() !== null;
    }
}
