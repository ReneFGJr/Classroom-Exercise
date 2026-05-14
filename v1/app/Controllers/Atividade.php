<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Atividade extends BaseController
{
    public function show(int $id): string|ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('grupo_avaliacao')) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'Tabela grupo_avaliacao nao encontrada.',
                ]);
        }

        $grupo = $db->table('grupo_avaliacao')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($grupo === null) {
            throw PageNotFoundException::forPageNotFound('Grupo de avaliacao nao encontrado.');
        }

        $atividades = [];

        if ($db->tableExists('definicao_atividade')) {
            $atividades = $db->table('definicao_atividade')
                ->where('grupo_avaliacao_id', $id)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        }

        $dados = [
            'title' => 'Atividades por Grupo de Avaliacao',
            'grupo' => $grupo,
            'atividades' => $atividades,
        ];

        return view('atividade', $dados);
    }
}