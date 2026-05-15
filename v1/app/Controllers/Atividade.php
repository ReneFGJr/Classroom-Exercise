<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Atividade extends BaseController
{
    public function show(int $id): string|ResponseInterface
    {
        $db = db_connect();

        $usuarioId = (int) (session('auth_user_id') ?? 0);

        if ($usuarioId <= 0) {
            return redirect()->to('/login')->with('erro', 'Voce precisa estar autenticado para acessar a atividade.');
        }

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

        if ($db->tableExists('respostas')) {
            $respostaExistente = $db->table('respostas')
                ->select('id')
                ->where('grupo_avaliacao_id', $id)
                ->where('usuario_id', $usuarioId)
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($respostaExistente === null) {
                $this->gerarRespostasIniciais($db, $grupo, $id, $usuarioId);
            }
        }

        $questoesSelecionadas = $this->carregarQuestoesSelecionadas($db, $id, $usuarioId);

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
            'questoes_selecionadas' => $questoesSelecionadas,
        ];

        return view('atividade', $dados);
    }

    public function responder(int $id): ResponseInterface
    {
        $db = db_connect();

        $usuarioId = (int) (session('auth_user_id') ?? 0);

        if ($usuarioId <= 0) {
            return redirect()->to('/login')->with('erro', 'Voce precisa estar autenticado para responder a atividade.');
        }

        if (! $db->tableExists('respostas')) {
            return redirect()->to('/atividade/' . $id)->with('erro', 'Tabela respostas nao encontrada.');
        }

        $respostasPost = $this->request->getPost('respostas');

        if (! is_array($respostasPost) || $respostasPost === []) {
            return redirect()->to('/atividade/' . $id)->with('erro', 'Nenhuma resposta foi enviada.');
        }

        $agora = date('Y-m-d H:i:s');
        $totalAtualizado = 0;

        foreach ($respostasPost as $respostaId => $conteudo) {
            $idResposta = (int) $respostaId;

            if ($idResposta <= 0) {
                continue;
            }

            $respostaTexto = trim((string) $conteudo);
            $respostaTexto = $respostaTexto !== '' ? $respostaTexto : null;

            $atualizou = $db->table('respostas')
                ->where('id', $idResposta)
                ->where('grupo_avaliacao_id', $id)
                ->where('usuario_id', $usuarioId)
                ->update([
                    'resposta_texto' => $respostaTexto,
                    'updated_at' => $agora,
                ]);

            if ($atualizou) {
                $totalAtualizado++;
            }
        }

        if ($totalAtualizado === 0) {
            return redirect()->to('/atividade/' . $id)->with('erro', 'Nenhuma resposta valida foi salva.');
        }

        return redirect()->to('/atividade/' . $id)->with('sucesso', 'Respostas salvas com sucesso.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function carregarQuestoesSelecionadas(object $db, int $grupoAvaliacaoId, int $usuarioId): array
    {
        if (! $db->tableExists('respostas') || ! $db->tableExists('questions')) {
            return [];
        }

        return $db->table('respostas r')
            ->select('r.id AS resposta_id, r.resposta_texto, q.id AS question_id, q.enunciado_questao, q.resposta_1, q.resposta_2, q.resposta_3, q.resposta_4, q.resposta_5')
            ->join('questions q', 'q.id = r.question_id', 'inner')
            ->where('r.grupo_avaliacao_id', $grupoAvaliacaoId)
            ->where('r.usuario_id', $usuarioId)
            ->orderBy('r.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @param array<string, mixed> $grupo
     */
    private function gerarRespostasIniciais(object $db, array $grupo, int $grupoAvaliacaoId, int $usuarioId): void
    {
        if (! $db->tableExists('grupo_questoes') || ! $db->tableExists('questions')) {
            return;
        }

        $idsQuestoesSorteadas = [];

        for ($indice = 1; $indice <= 10; $indice++) {
            $campoGrupoQuestoes = 'grupo_questoes_querstao_' . $indice;
            $campoQuantidade = 'qq' . $indice;

            $grupoQuestoesId = (int) ($grupo[$campoGrupoQuestoes] ?? 0);
            $quantidadeQuestoes = (int) ($grupo[$campoQuantidade] ?? 1);

            if ($grupoQuestoesId <= 0 || $quantidadeQuestoes <= 0) {
                continue;
            }

            $grupoQuestoes = $db->table('grupo_questoes')
                ->select('id, questoes_json')
                ->where('id', $grupoQuestoesId)
                ->get()
                ->getRowArray();

            // Se o grupo de questoes nao existir, segue para o proximo sem falhar.
            if ($grupoQuestoes === null) {
                continue;
            }

            $idsQuestoesDoGrupo = $this->decodificarIdsQuestoes((string) ($grupoQuestoes['questoes_json'] ?? '[]'));

            if ($idsQuestoesDoGrupo === []) {
                continue;
            }

            $questoesDisponiveis = $db->table('questions')
                ->select('id')
                ->whereIn('id', $idsQuestoesDoGrupo)
                ->get()
                ->getResultArray();

            if ($questoesDisponiveis === []) {
                continue;
            }

            $idsDisponiveis = array_map(static fn(array $questao): int => (int) $questao['id'], $questoesDisponiveis);
            $idsDisponiveis = array_values(array_unique(array_filter($idsDisponiveis, static fn(int $id): bool => $id > 0)));

            if ($idsDisponiveis === []) {
                continue;
            }

            shuffle($idsDisponiveis);
            $limite = min($quantidadeQuestoes, count($idsDisponiveis));
            $idsSorteadosGrupo = array_slice($idsDisponiveis, 0, $limite);

            foreach ($idsSorteadosGrupo as $questionId) {
                $idsQuestoesSorteadas[] = (int) $questionId;
            }
        }

        $idsQuestoesSorteadas = array_values(array_unique(array_filter($idsQuestoesSorteadas, static fn(int $id): bool => $id > 0)));

        if ($idsQuestoesSorteadas === []) {
            return;
        }

        $idsJaRespondidos = $db->table('respostas')
            ->select('question_id')
            ->where('grupo_avaliacao_id', $grupoAvaliacaoId)
            ->where('usuario_id', $usuarioId)
            ->whereIn('question_id', $idsQuestoesSorteadas)
            ->get()
            ->getResultArray();

        $mapaJaRespondidos = [];

        foreach ($idsJaRespondidos as $resposta) {
            $mapaJaRespondidos[(int) $resposta['question_id']] = true;
        }

        $agora = date('Y-m-d H:i:s');
        $payload = [];

        foreach ($idsQuestoesSorteadas as $questionId) {
            if (isset($mapaJaRespondidos[$questionId])) {
                continue;
            }

            $payload[] = [
                'grupo_avaliacao_id' => $grupoAvaliacaoId,
                'usuario_id' => $usuarioId,
                'question_id' => $questionId,
                'resposta_texto' => null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        if ($payload !== []) {
            $db->table('respostas')->insertBatch($payload);
        }
    }

    /**
     * @return list<int>
     */
    private function decodificarIdsQuestoes(string $questoesJson): array
    {
        $decodificado = json_decode($questoesJson, true);

        if (! is_array($decodificado)) {
            return [];
        }

        $ids = [];

        foreach ($decodificado as $valor) {
            $id = (int) $valor;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }
}