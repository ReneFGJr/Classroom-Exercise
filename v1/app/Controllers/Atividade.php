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
        $avaliacaoFinalizada = $this->avaliacaoFinalizada($db, $id, $usuarioId);
        $dadosCronometro = $this->obterDadosCronometro($db, $grupo, $id, $usuarioId, $avaliacaoFinalizada);

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
            'avaliacao_finalizada' => $avaliacaoFinalizada,
            'cronometro_ativo' => $dadosCronometro['ativo'],
            'segundos_restantes' => $dadosCronometro['segundos_restantes'],
        ];

        return view('atividade', $dados);
    }

    public function avaliacao(int $id): string|ResponseInterface
    {
        $db = db_connect();

        $isAdmin = (bool) (session('auth_is_admin') ?? session('is_admin') ?? false);
        if (! $isAdmin) {
            return redirect()->to('/')->with('erro', 'Acesso negado. Somente administradores podem acessar a correcao.');
        }

        if (! $db->tableExists('grupo_avaliacao')) {
            return redirect()->to('/')->with('erro', 'Tabela grupo_avaliacao nao encontrada.');
        }

        if (! $db->tableExists('respostas')) {
            return redirect()->to('/atividade/' . $id)->with('erro', 'Tabela respostas nao encontrada.');
        }

        $grupo = $db->table('grupo_avaliacao')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($grupo === null) {
            throw PageNotFoundException::forPageNotFound('Grupo de avaliacao nao encontrado.');
        }

        $temUsuarios = $db->tableExists('usuarios');
        $temNomeCompleto = $temUsuarios && $this->campoExiste($db, 'usuarios', 'nome_completo');
        $temUsuarioLogin = $temUsuarios && $this->campoExiste($db, 'usuarios', 'usuario');
        $temEmail = $temUsuarios && $this->campoExiste($db, 'usuarios', 'email');

        $nomeUsuarioExpr = "CONCAT('Usuario #', r.usuario_id)";

        if ($temNomeCompleto && $temUsuarioLogin) {
            $nomeUsuarioExpr = "COALESCE(NULLIF(u.nome_completo, ''), NULLIF(u.usuario, ''), CONCAT('Usuario #', r.usuario_id))";
        } elseif ($temNomeCompleto) {
            $nomeUsuarioExpr = "COALESCE(NULLIF(u.nome_completo, ''), CONCAT('Usuario #', r.usuario_id))";
        } elseif ($temUsuarioLogin) {
            $nomeUsuarioExpr = "COALESCE(NULLIF(u.usuario, ''), CONCAT('Usuario #', r.usuario_id))";
        }

        $select = [
            'r.id AS resposta_id',
            'r.usuario_id',
            $nomeUsuarioExpr . ' AS nome_usuario',
            ($temEmail ? 'u.email' : "''") . ' AS email_usuario',
            'r.question_id',
            'q.enunciado_questao',
            'q.resposta_correta',
            'r.resposta_texto',
            'r.created_at',
            'r.updated_at',
        ];

        if ($this->campoExiste($db, 'respostas', 'finalizado_em')) {
            $select[] = 'r.finalizado_em';
        }

        if ($this->campoExiste($db, 'respostas', 'corrigido')) {
            $select[] = 'r.corrigido';
        }

        if ($this->campoExiste($db, 'respostas', 'comentarios_correcao')) {
            $select[] = 'r.comentarios_correcao';
        }

        if ($this->campoExiste($db, 'respostas', 'nota')) {
            $select[] = 'r.nota';
        }

        $builder = $db->table('respostas r')
            ->select(implode(', ', $select), false)
            ->join('questions q', 'q.id = r.question_id', 'left')
            ->where('r.grupo_avaliacao_id', $id)
            ->orderBy('nome_usuario', 'ASC')
            ->orderBy('r.usuario_id', 'ASC')
            ->orderBy('r.id', 'ASC');

        if ($temUsuarios) {
            $builder->join('usuarios u', 'u.id = r.usuario_id', 'left');
        }

        $respostas = $builder
            ->get()
            ->getResultArray();

        $respostasPorAluno = [];

        foreach ($respostas as $resposta) {
            $usuarioId = (int) ($resposta['usuario_id'] ?? 0);

            if (! isset($respostasPorAluno[$usuarioId])) {
                $respostasPorAluno[$usuarioId] = [
                    'usuario_id' => $usuarioId,
                    'nome_usuario' => (string) ($resposta['nome_usuario'] ?? ('Usuario #' . $usuarioId)),
                    'email_usuario' => (string) ($resposta['email_usuario'] ?? ''),
                    'respostas' => [],
                ];
            }

            $respostasPorAluno[$usuarioId]['respostas'][] = $resposta;
        }

        $dados = [
            'title' => 'Avaliacao - Correcao',
            'grupo' => $grupo,
            'respostas_por_aluno' => array_values($respostasPorAluno),
        ];

        return view('atividade_avaliacao', $dados);
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

        if ($this->avaliacaoFinalizada($db, $id, $usuarioId)) {
            return redirect()->to('/atividade/' . $id)->with('erro', 'A avaliacao ja foi finalizada e nao pode mais ser editada.');
        }

        $tempoRestanteSegundos = $this->obterTempoRestanteSegundos($db, $id, $usuarioId);
        if ($tempoRestanteSegundos !== null && $tempoRestanteSegundos <= 0) {
            return redirect()->to('/atividade/' . $id)->with('erro', 'Tempo da avaliacao encerrado. Nao e mais possivel editar.');
        }

        $acao = (string) ($this->request->getPost('acao') ?? 'salvar');
        $acao = in_array($acao, ['salvar', 'finalizar'], true) ? $acao : 'salvar';

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

        if ($totalAtualizado === 0 && $acao !== 'finalizar') {
            return redirect()->to('/atividade/' . $id)->with('erro', 'Nenhuma resposta valida foi salva.');
        }

        if ($acao === 'finalizar') {
            if (! $this->campoExiste($db, 'respostas', 'finalizado_em')) {
                return redirect()->to('/atividade/' . $id)->with('erro', 'Campo de finalizacao nao encontrado. Execute as migrations pendentes.');
            }

            $agora = date('Y-m-d H:i:s');

            $db->table('respostas')
                ->where('grupo_avaliacao_id', $id)
                ->where('usuario_id', $usuarioId)
                ->update([
                    'finalizado_em' => $agora,
                    'updated_at' => $agora,
                ]);

            return redirect()->to('/atividade/' . $id)->with('sucesso', 'Avaliacao finalizada e enviada com sucesso.');
        }

        return redirect()->to('/atividade/' . $id)->with('sucesso', 'Respostas salvas com sucesso.');
    }

    public function salvarCorrecaoManual(int $id, int $respostaId): ResponseInterface
    {
        $db = db_connect();

        $isAdmin = (bool) (session('auth_is_admin') ?? session('is_admin') ?? false);
        if (! $isAdmin) {
            return redirect()->to('/')->with('erro', 'Acesso negado.');
        }

        if (! $db->tableExists('respostas')) {
            return redirect()->to('/atividade/avaliacao/' . $id)->with('erro', 'Tabela respostas nao encontrada.');
        }

        foreach (['corrigido', 'nota', 'comentarios_correcao'] as $campo) {
            if (! $this->campoExiste($db, 'respostas', $campo)) {
                return redirect()->to('/atividade/avaliacao/' . $id)->with('erro', 'Campos de correcao nao encontrados. Execute as migrations pendentes.');
            }
        }

        $registro = $db->table('respostas')
            ->select('id')
            ->where('id', $respostaId)
            ->where('grupo_avaliacao_id', $id)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($registro === null) {
            return redirect()->to('/atividade/avaliacao/' . $id)->with('erro', 'Resposta nao encontrada.');
        }

        $notaRaw = $this->request->getPost('nota');
        $nota = $notaRaw !== null && $notaRaw !== '' ? (int) $notaRaw : null;
        $corrigido = $this->request->getPost('corrigido') === '1' ? 1 : 0;
        $comentarios = trim((string) ($this->request->getPost('comentarios_correcao') ?? ''));

        $db->table('respostas')
            ->where('id', $respostaId)
            ->where('grupo_avaliacao_id', $id)
            ->update([
                'corrigido' => $corrigido,
                'nota' => $nota,
                'comentarios_correcao' => $comentarios !== '' ? $comentarios : null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to('/atividade/avaliacao/' . $id)->with('sucesso', 'Correcao salva com sucesso.');
    }

    public function corrigirAutomaticamente(int $id): ResponseInterface
    {
        $db = db_connect();

        $isAdmin = (bool) (session('auth_is_admin') ?? session('is_admin') ?? false);
        if (! $isAdmin) {
            return redirect()->to('/')->with('erro', 'Acesso negado. Somente administradores podem corrigir automaticamente.');
        }

        if (! $db->tableExists('respostas') || ! $db->tableExists('questions')) {
            return redirect()->to('/atividade/avaliacao/' . $id)->with('erro', 'Tabelas obrigatorias para correcao automatica nao encontradas.');
        }

        foreach (['corrigido', 'comentarios_correcao', 'nota'] as $campoCorrecao) {
            if (! $this->campoExiste($db, 'respostas', $campoCorrecao)) {
                return redirect()->to('/atividade/avaliacao/' . $id)
                    ->with('erro', 'Campos de correcao nao encontrados na tabela respostas. Execute as migrations pendentes.');
            }
        }

        if (! $this->campoExiste($db, 'questions', 'resposta_correta')) {
            return redirect()->to('/atividade/avaliacao/' . $id)->with('erro', 'Campo resposta_correta nao encontrado em questions.');
        }

        $temTiposResposta = $db->tableExists('tipos_resposta') && $this->campoExiste($db, 'questions', 'tipo_resposta_id')
            && $this->campoExiste($db, 'tipos_resposta', 'id') && $this->campoExiste($db, 'tipos_resposta', 'slug');

        $select = [
            'r.id AS resposta_id',
            'r.resposta_texto',
            'q.resposta_correta',
            'q.resposta_1',
            'q.resposta_2',
            'q.resposta_3',
            'q.resposta_4',
            'q.resposta_5',
        ];

        if ($temTiposResposta) {
            $select[] = 'tr.slug AS tipo_slug';
        }

        $builder = $db->table('respostas r')
            ->select(implode(', ', $select))
            ->join('questions q', 'q.id = r.question_id', 'inner')
            ->where('r.grupo_avaliacao_id', $id)
            ->orderBy('r.id', 'ASC');

        if ($temTiposResposta) {
            $builder->join('tipos_resposta tr', 'tr.id = q.tipo_resposta_id', 'left');
        }

        $registros = $builder->get()->getResultArray();

        if ($registros === []) {
            return redirect()->to('/atividade/avaliacao/' . $id)->with('erro', 'Nao ha respostas para corrigir.');
        }

        $agora = date('Y-m-d H:i:s');
        $totalCorrigidas = 0;
        $totalAcertos = 0;

        $db->transStart();

        foreach ($registros as $registro) {
            $tipoSlug = strtolower(trim((string) ($registro['tipo_slug'] ?? '')));
            $correta = trim((string) ($registro['resposta_correta'] ?? ''));
            $resposta = trim((string) ($registro['resposta_texto'] ?? ''));

            $opcoes = [];
            foreach (['resposta_1', 'resposta_2', 'resposta_3', 'resposta_4', 'resposta_5'] as $campoOpcao) {
                $textoOpcao = trim((string) ($registro[$campoOpcao] ?? ''));
                if ($textoOpcao !== '') {
                    $opcoes[] = $textoOpcao;
                }
            }

            $ehMultiplaEscolha = false;

            if ($temTiposResposta && $tipoSlug !== '') {
                // tipo identificado pelo banco
                $ehMultiplaEscolha = in_array($tipoSlug, ['multipla_escolha', 'multipla_resposta', 'verdadeiro_falso'], true);
            } else {
                // fallback: questao com opcoes de resposta preenchidas e gabarito definido
                $ehMultiplaEscolha = count($opcoes) >= 2;
            }

            if (! $ehMultiplaEscolha || $correta === '') {
                continue;
            }

            $respostaNormalizada = mb_strtolower($resposta);
            $corretaNormalizada = mb_strtolower($correta);
            $acertou = $respostaNormalizada !== '' && $respostaNormalizada === $corretaNormalizada;

            if ($acertou) {
                $totalAcertos++;
            }

            $comentario = 'Correcao automatica: resposta incorreta.';
            if ($acertou) {
                $comentario = 'Correcao automatica: resposta correta.';
            } elseif ($resposta === '') {
                $comentario = 'Correcao automatica: questao sem resposta.';
            }

            if (! $acertou) {
                $comentario .= ' Correta: ' . $correta;
            }

            $db->table('respostas')
                ->where('id', (int) ($registro['resposta_id'] ?? 0))
                ->update([
                    'corrigido' => 1,
                    'nota' => $acertou ? 1 : 0,
                    'comentarios_correcao' => $comentario,
                    'updated_at' => $agora,
                ]);

            $totalCorrigidas++;
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/atividade/avaliacao/' . $id)->with('erro', 'Falha ao salvar a correcao automatica.');
        }

        if ($totalCorrigidas === 0) {
            return redirect()->to('/atividade/avaliacao/' . $id)
                ->with('erro', 'Nenhuma questao objetiva identificada para correcao automatica.');
        }

        return redirect()->to('/atividade/avaliacao/' . $id)
            ->with('sucesso', 'Correcao automatica concluida. Questoes corrigidas: ' . $totalCorrigidas . '. Acertos: ' . $totalAcertos . '.');
    }

    private function avaliacaoFinalizada(object $db, int $grupoAvaliacaoId, int $usuarioId): bool
    {
        if (! $db->tableExists('respostas')) {
            return false;
        }

        if (! $this->campoExiste($db, 'respostas', 'finalizado_em')) {
            return false;
        }

        $registroFinalizado = $db->table('respostas')
            ->select('id')
            ->where('grupo_avaliacao_id', $grupoAvaliacaoId)
            ->where('usuario_id', $usuarioId)
            ->where('finalizado_em IS NOT NULL', null, false)
            ->limit(1)
            ->get()
            ->getRowArray();

        return $registroFinalizado !== null;
    }

    private function campoExiste(object $db, string $tabela, string $campo): bool
    {
        $coluna = $db->query("SHOW COLUMNS FROM {$tabela} LIKE '{$campo}'")->getRowArray();

        return $coluna !== null;
    }

    /**
     * @param array<string, mixed> $grupo
     * @return array{ativo: bool, segundos_restantes: int}
     */
    private function obterDadosCronometro(object $db, array $grupo, int $grupoAvaliacaoId, int $usuarioId, bool $avaliacaoFinalizada): array
    {
        if ($avaliacaoFinalizada) {
            return ['ativo' => false, 'segundos_restantes' => 0];
        }

        $duracaoHoras = (int) ($grupo['duracao_prova_horas'] ?? 0);
        if ($duracaoHoras <= 0) {
            return ['ativo' => false, 'segundos_restantes' => 0];
        }

        $inicio = $this->obterInicioAvaliacaoPorResposta($db, $grupoAvaliacaoId, $usuarioId);
        if ($inicio === null) {
            return ['ativo' => false, 'segundos_restantes' => 0];
        }

        $timestampInicio = strtotime($inicio);
        if ($timestampInicio === false) {
            return ['ativo' => false, 'segundos_restantes' => 0];
        }

        $segundosRestantes = ($timestampInicio + ($duracaoHoras * 3600)) - time();

        return [
            'ativo' => $segundosRestantes > 0,
            'segundos_restantes' => max(0, $segundosRestantes),
        ];
    }

    private function obterTempoRestanteSegundos(object $db, int $grupoAvaliacaoId, int $usuarioId): ?int
    {
        if (! $db->tableExists('grupo_avaliacao')) {
            return null;
        }

        $grupo = $db->table('grupo_avaliacao')
            ->select('duracao_prova_horas')
            ->where('id', $grupoAvaliacaoId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($grupo === null) {
            return null;
        }

        $duracaoHoras = (int) ($grupo['duracao_prova_horas'] ?? 0);
        if ($duracaoHoras <= 0) {
            return null;
        }

        $inicio = $this->obterInicioAvaliacaoPorResposta($db, $grupoAvaliacaoId, $usuarioId);
        if ($inicio === null) {
            return null;
        }

        $timestampInicio = strtotime($inicio);
        if ($timestampInicio === false) {
            return null;
        }

        return ($timestampInicio + ($duracaoHoras * 3600)) - time();
    }

    private function obterInicioAvaliacaoPorResposta(object $db, int $grupoAvaliacaoId, int $usuarioId): ?string
    {
        if (! $db->tableExists('respostas')) {
            return null;
        }

        $linha = $db->table('respostas')
            ->select('MIN(created_at) AS inicio_avaliacao', false)
            ->where('grupo_avaliacao_id', $grupoAvaliacaoId)
            ->where('usuario_id', $usuarioId)
            ->get()
            ->getRowArray();

        if (! is_array($linha)) {
            return null;
        }

        $inicio = trim((string) ($linha['inicio_avaliacao'] ?? ''));

        return $inicio !== '' ? $inicio : null;
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