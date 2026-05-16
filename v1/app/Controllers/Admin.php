<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use SimpleXMLElement;
use Throwable;

class Admin extends BaseController
{
    private function usuarioEhAdmin(): bool
    {
        return (bool) (session('auth_is_admin') ?? session('is_admin') ?? false);
    }

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        // Restrict access to admin-only pages
        if (! $this->usuarioEhAdmin()) {
            throw new \RuntimeException('Acesso negado.');
        }
    }

    private function verificarAcessoAdmin(): void
    {
        if (! $this->usuarioEhAdmin()) {
            throw new \RuntimeException('Acesso negado.');
        }
    }

    /**
     * Exibe painel administrativo com as avaliacoes disponiveis.
     */
    public function index(): string
    {
        $this->verificarAcessoAdmin();

        $db = db_connect();
        $avaliacoes = [];
        $mensagemAviso = null;

        if (! $db->tableExists('grupo_avaliacao')) {
            $mensagemAviso = 'Tabela grupo_avaliacao nao encontrada. Execute as migrations pendentes.';
        } else {
            $selectCampos = [
                'id',
                'nome_disciplina',
                'created_at',
                'updated_at',
            ];

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

        return view('admin/index', [
            'title' => 'Painel Admin',
            'avaliacoes' => $avaliacoes,
            'mensagem_aviso' => $mensagemAviso,
        ]);
    }

    public function logarComoUsuario(int $id): ResponseInterface
    {
        $db = db_connect();

        $usuario = $db->table('usuarios')->where('id', $id)->get()->getRowArray();

        if ($usuario === null) {
            return redirect()->to('/admin/usuarios')->with('erro', 'Usuario nao encontrado.');
        }

        $sessao = session();

        // Guarda o admin original para poder voltar
        $sessao->set([
            'impersonando'              => true,
            'impersonando_admin_id'     => $sessao->get('auth_user_id'),
            'impersonando_admin_nome'   => $sessao->get('auth_nome'),
            'auth_logged_in'            => true,
            'auth_user_id'              => (int) ($usuario['id'] ?? 0),
            'auth_nome'                 => (string) ($usuario['nome_completo'] ?? $usuario['usuario'] ?? 'Usuario'),
            'auth_is_admin'             => (int) ($usuario['is_admin'] ?? 0) === 1,
        ]);

        return redirect()->to('/')->with('sucesso', 'Voce esta logado como ' . esc($usuario['nome_completo'] ?? $usuario['usuario']));
    }

    public function voltarParaAdmin(): ResponseInterface
    {
        $sessao = session();

        if (! $sessao->get('impersonando')) {
            return redirect()->to('/');
        }

        $sessao->set([
            'impersonando'  => false,
            'auth_logged_in'=> true,
            'auth_user_id'  => $sessao->get('impersonando_admin_id'),
            'auth_nome'     => $sessao->get('impersonando_admin_nome'),
            'auth_is_admin' => true,
        ]);

        $sessao->remove(['impersonando_admin_id', 'impersonando_admin_nome']);

        return redirect()->to('/admin/usuarios')->with('sucesso', 'Voce voltou para sua conta de administrador.');
    }

    public function usuariosCadastrados(): string|ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('usuarios')) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Tabela de usuarios nao encontrada. Execute as migrations pendentes.');
        }

        $selectCampos = [
            'id',
            'usuario',
            'idcard',
            'nome_completo',
            'primeiro_acesso',
            'is_admin',
            'created_at',
        ];

        if ($this->campoExiste('usuarios', 'email')) {
            $selectCampos[] = 'email';
        }

        $usuarios = $db->table('usuarios')
            ->select(implode(', ', $selectCampos))
            ->orderBy('nome_completo', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/usuarios', [
            'title' => 'Usuarios cadastrados',
            'usuarios' => $usuarios,
        ]);
    }

    public function novaAvaliacao(): string|ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('grupo_avaliacao')) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Tabela grupo_avaliacao nao encontrada.');
        }

        return view('admin/avaliacao_form', [
            'title' => 'Nova avaliacao',
            'avaliacao' => null,
            'acao' => '/admin/avaliations/store',
            'titulo_form' => 'Nova avaliacao',
            'texto_botao' => 'Salvar avaliacao',
        ]);
    }

    public function storeAvaliacao(): ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('grupo_avaliacao')) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Tabela grupo_avaliacao nao encontrada.');
        }

        [$payload, $erros] = $this->validarDadosAvaliacao();

        if ($erros !== []) {
            return redirect()->back()->with('erro', implode(' ', $erros))->withInput();
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['updated_at'] = date('Y-m-d H:i:s');

        $db->table('grupo_avaliacao')->insert($this->filtrarCamposGrupoAvaliacao($payload));

        return redirect()->to('/admin/avaliations')->with('sucesso', 'Avaliacao criada com sucesso.');
    }

    public function editarAvaliacao(int $id): string|ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('grupo_avaliacao')) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Tabela grupo_avaliacao nao encontrada.');
        }

        $avaliacao = $db->table('grupo_avaliacao')->where('id', $id)->get()->getRowArray();

        if ($avaliacao === null) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Avaliacao nao encontrada.');
        }

        return view('admin/avaliacao_form', [
            'title' => 'Editar avaliacao',
            'avaliacao' => $avaliacao,
            'acao' => '/admin/avaliations/' . $id . '/update',
            'titulo_form' => 'Editar avaliacao',
            'texto_botao' => 'Atualizar avaliacao',
        ]);
    }

    public function updateAvaliacao(int $id): ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('grupo_avaliacao')) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Tabela grupo_avaliacao nao encontrada.');
        }

        $avaliacao = $db->table('grupo_avaliacao')->where('id', $id)->get()->getRowArray();

        if ($avaliacao === null) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Avaliacao nao encontrada.');
        }

        [$payload, $erros] = $this->validarDadosAvaliacao();

        if ($erros !== []) {
            return redirect()->back()->with('erro', implode(' ', $erros))->withInput();
        }

        $payload['updated_at'] = date('Y-m-d H:i:s');

        $db->table('grupo_avaliacao')
            ->where('id', $id)
            ->update($this->filtrarCamposGrupoAvaliacao($payload));

        return redirect()->to('/admin/avaliations')->with('sucesso', 'Avaliacao atualizada com sucesso.');
    }

    public function excluirAvaliacao(int $id): ResponseInterface
    {
        $db = db_connect();

        if (! $db->tableExists('grupo_avaliacao')) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Tabela grupo_avaliacao nao encontrada.');
        }

        $avaliacao = $db->table('grupo_avaliacao')->where('id', $id)->get()->getRowArray();

        if ($avaliacao === null) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Avaliacao nao encontrada.');
        }

        try {
            $db->table('grupo_avaliacao')->where('id', $id)->delete();
        } catch (Throwable $e) {
            return redirect()->to('/admin/avaliations')->with('erro', 'Nao foi possivel excluir: ' . $e->getMessage());
        }

        return redirect()->to('/admin/avaliations')->with('sucesso', 'Avaliacao excluida com sucesso.');
    }

    /**
     * Importa usuarios a partir do CSV de participantes.
     */
    public function inportUsers(): ResponseInterface
    {
        $csvPath = ROOTPATH . '../_docs/class/courseid_159145_participants.csv';

        if (! is_file($csvPath)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'Arquivo CSV nao encontrado.',
                    'caminho' => $csvPath,
                ]);
        }

        if (! is_readable($csvPath)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'Arquivo CSV sem permissao de leitura.',
                    'caminho' => $csvPath,
                ]);
        }

        $db = db_connect();

        if (! $db->tableExists('usuarios')) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'Tabela de usuarios nao encontrada. Execute as migrations.',
                ]);
        }

        $conteudo = file_get_contents($csvPath);

        if ($conteudo === false || $conteudo === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'Arquivo CSV vazio ou invalido.',
                ]);
        }

        // Alguns CSVs exportados possuem bytes nulos; removemos para normalizar a leitura.
        if (str_contains($conteudo, "\0")) {
            $conteudo = str_replace("\0", '', $conteudo);
        }

        $linhasBrutas = preg_split('/\R/', $conteudo) ?: [];
        $linhas = [];

        foreach ($linhasBrutas as $linhaBruta) {
            $linha = preg_replace('/^\xEF\xBB\xBF/', '', (string) $linhaBruta) ?? (string) $linhaBruta;
            $linha = trim($linha);

            if ($linha === '') {
                continue;
            }

            $linhas[] = $linha;
        }

        if ($linhas === []) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'Arquivo CSV vazio ou invalido.',
                ]);
        }

        $linhaCabecalho = null;
        $indiceCabecalho = null;
        $delimitador = "\t";

        foreach ($linhas as $idx => $linha) {
            foreach (["\t", ';', ','] as $candidato) {
                $colunasCandidatas = str_getcsv($linha, $candidato);
                if (count($colunasCandidatas) >= 4) {
                    $linhaCabecalho = $linha;
                    $indiceCabecalho = $idx;
                    $delimitador = $candidato;
                    break 2;
                }
            }
        }

        if ($linhaCabecalho === null || $indiceCabecalho === null) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'Nao foi possivel detectar o cabecalho do CSV.',
                ]);
        }

        $cabecalho = str_getcsv($linhaCabecalho, $delimitador);

        $mapaCabecalho = [];
        foreach ($cabecalho as $indice => $coluna) {
            $chave = $this->normalizarCabecalho((string) $coluna);
            if ($chave !== '') {
                $mapaCabecalho[$chave] = $indice;
            }
        }

        // Fallback para a estrutura fixa informada: Seq.|Aluno|Cartao|Nome|Assinatura
        $idxCartao = $mapaCabecalho['cartao'] ?? 2;
        $idxNome = $mapaCabecalho['nome'] ?? 3;

        $importados = 0;
        $existentes = 0;
        $ignorados = 0;
        $crachasInvalidos = 0;
        $erros = [];

        try {
            $db->transException(true)->transStart();

            foreach ($linhas as $indice => $linha) {
                if ($indice <= $indiceCabecalho) {
                    continue;
                }

                $colunas = str_getcsv($linha, $delimitador);

                if (! isset($colunas[$idxCartao], $colunas[$idxNome])) {
                    $ignorados++;

                    continue;
                }

                $cartao = preg_replace('/\D+/', '', trim((string) $colunas[$idxCartao]));
                $nome = trim((string) $colunas[$idxNome]);

                if ($cartao === '' || $nome === '') {
                    $ignorados++;

                    continue;
                }

                if (strlen($cartao) > 8) {
                    $crachasInvalidos++;

                    continue;
                }

                $idcard = str_pad($cartao, 8, '0', STR_PAD_LEFT);
                $usuario = 'u' . $idcard;

                $usuariosTable = $db->table('usuarios');
                $existente = $usuariosTable->where('idcard', $idcard)->get()->getRowArray();

                if ($existente !== null) {
                    $existentes++;

                    continue;
                }

                $usuariosTable->insert([
                    'usuario' => $usuario,
                    'idcard' => $idcard,
                    'senha' => password_hash($idcard, PASSWORD_DEFAULT),
                    'primeiro_acesso' => 1,
                    'data_primeira_entrada' => null,
                    'is_admin' => 0,
                    'nome_completo' => $nome,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $importados++;
            }

            $db->transComplete();
        } catch (Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }

            $erros[] = $e->getMessage();
        }

        if ($erros !== []) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'Erro durante a importacao de usuarios.',
                    'erros' => $erros,
                ]);
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'mensagem' => 'Importacao de usuarios concluida.',
            'arquivo' => $csvPath,
            'usuarios_importados' => $importados,
            'usuarios_ja_existentes' => $existentes,
            'linhas_ignoradas' => $ignorados,
            'crachas_invalidos_mais_de_8_digitos' => $crachasInvalidos,
        ]);
    }

    /**
     * Exibe a tela de importacao de XML com confirmacao.
     */
    public function importarXml(): string|ResponseInterface
    {
        $directoryPath = ROOTPATH . '../_docs/questions/';

        if (! is_dir($directoryPath)) {
            return redirect()->to('/admin')->with('erro', 'Diretorio de importacao nao encontrado: ' . $directoryPath);
        }

        $entries = array_values(array_diff(scandir($directoryPath), ['.', '..']));
        $arquivos = [];

        foreach ($entries as $entry) {
            $filePath = $directoryPath . DIRECTORY_SEPARATOR . $entry;

            if (! is_file($filePath)) {
                continue;
            }

            $extensao = strtolower(pathinfo($entry, PATHINFO_EXTENSION));

            if ($extensao !== 'xml') {
                continue;
            }

            $arquivos[] = [
                'nome' => $entry,
                'tamanho_kb' => round(((int) filesize($filePath)) / 1024, 2),
                'modificado_em' => date('d/m/Y H:i', (int) filemtime($filePath)),
            ];
        }

        usort($arquivos, static fn ($a, $b) => strcmp((string) $a['nome'], (string) $b['nome']));

        return view('admin/importar_xml', [
            'title' => 'Importar XML',
            'pasta_origem' => '_docs/questions',
            'arquivos' => $arquivos,
            'resultado_importacao' => session('resultado_importacao'),
        ]);
    }

    /**
     * Executa a importacao dos arquivos XML apos confirmacao do usuario.
     */
    public function confirmarImportacaoXml(): ResponseInterface
    {
        $directoryPath = ROOTPATH . '../_docs/questions/';

        if (! is_dir($directoryPath)) {
            return redirect()->to('/admin/importar-xml')->with('erro', 'Diretorio de importacao nao encontrado.');
        }

        $entries = array_values(array_diff(scandir($directoryPath), ['.', '..']));
        $processedFiles = [];
        $errors = [];

        foreach ($entries as $entry) {
            $filePath = $directoryPath . DIRECTORY_SEPARATOR . $entry;

            if (! is_file($filePath)) {
                continue;
            }

            if (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) !== 'xml') {
                continue;
            }

            try {
                $this->processarXml($filePath);
                $processedFiles[] = $entry;
            } catch (Throwable $e) {
                $errors[] = [
                    'arquivo' => $entry,
                    'erro' => $e->getMessage(),
                ];
            }
        }

        $resultado = [
            'total_processados' => count($processedFiles),
            'total_erros' => count($errors),
            'arquivos_processados' => $processedFiles,
            'erros' => $errors,
        ];

        if ($resultado['total_processados'] === 0 && $resultado['total_erros'] === 0) {
            return redirect()->to('/admin/importar-xml')->with('erro', 'Nenhum arquivo XML encontrado para importar.');
        }

        return redirect()->to('/admin/importar-xml')
            ->with('sucesso', 'Importacao finalizada.')
            ->with('resultado_importacao', $resultado);
    }

    private function processarXml(string $filePath): void
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);

        if ($xml === false) {
            $errors = array_map(
                static fn ($error) => trim($error->message),
                libxml_get_errors(),
            );
            libxml_clear_errors();

            throw new  \RuntimeException('Falha ao interpretar o XML: ' . implode('; ', $errors));
        }

        $db = db_connect();

        foreach (['questions', 'tipos_resposta', 'grupo_questoes'] as $tableName) {
            if (! $db->tableExists($tableName)) {
                throw new \RuntimeException('Tabela obrigatoria nao encontrada: ' . $tableName);
            }
        }

        $disciplina = trim((string) ($xml['disciplina'] ?? ''));
        if ($disciplina === '') {
            $disciplina = pathinfo($filePath, PATHINFO_FILENAME);
        }

        $grupoAvaliacaoId = null;
        if ($db->tableExists('grupo_avaliacao')) {
            $grupoAvaliacaoId = $this->upsertGrupoAvaliacao($db, $disciplina);
        }

        $tipoCache = [];
        $idsQuestoes = [];

        $questoesXml = $xml->questao ?? [];

        foreach ($questoesXml as $questaoXml) {
            $enunciado = trim((string) ($questaoXml->enunciado ?? ''));
            if ($enunciado === '') {
                continue;
            }

            $tipoSlug = trim((string) ($questaoXml['tipo'] ?? ''));
            if ($tipoSlug === '') {
                $tipoSlug = 'multipla_escolha';
            }

            $tipoRespostaId = $this->getOrCreateTipoResposta($db, $tipoSlug, $tipoCache);
            $dificuldade = $this->mapearDificuldade((string) ($questaoXml['dificuldade'] ?? ''));
            [$r1, $r2, $r3, $r4, $r5] = $this->extrairAlternativas($questaoXml);

            [$questionId] = $this->upsertQuestion($db, [
                'enunciado_questao' => $enunciado,
                'tipo_resposta_id' => $tipoRespostaId,
                'resposta_correta' => trim((string) ($questaoXml->respostaCorreta ?? '')) ?: null,
                'justificativa_resposta' => trim((string) ($questaoXml->explicacao ?? '')) ?: null,
                'resposta_1' => $r1,
                'resposta_2' => $r2,
                'resposta_3' => $r3,
                'resposta_4' => $r4,
                'resposta_5' => $r5,
                'nivel_dificuldade' => $dificuldade,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $idsQuestoes[] = $questionId;
        }

        $idsQuestoes = array_values(array_unique(array_map('intval', $idsQuestoes)));

        if ($idsQuestoes === []) {
            throw new \RuntimeException('Nenhuma questao valida encontrada no XML.');
        }

        $nomeArquivo = pathinfo($filePath, PATHINFO_FILENAME);
        $nomeGrupo = 'xml_' . strtolower(preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $nomeArquivo) ?? $nomeArquivo);
        $nomeGrupo = substr($nomeGrupo, 0, 150);

        $this->upsertGrupoQuestoes($db, $nomeGrupo, $idsQuestoes, $grupoAvaliacaoId);
    }

    private function normalizarCabecalho(string $coluna): string
    {
        $texto = trim($coluna);
        $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto) ?? $texto;

        if (function_exists('iconv')) {
            $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
            if ($convertido !== false) {
                $texto = $convertido;
            }
        }

        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9]+/', '', $texto) ?? '';

        return $texto;
    }

    /**
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    private function validarDadosAvaliacao(): array
    {
        $nomeDisciplina = trim((string) $this->request->getPost('nome_disciplina'));
        $dataInicio = trim((string) $this->request->getPost('data_inicio_avaliacao'));
        $dataFim = trim((string) $this->request->getPost('data_fim_avaliacao'));
        $horaInicio = trim((string) $this->request->getPost('hora_inicio'));
        $horaFim = trim((string) $this->request->getPost('hora_fim'));
        $duracao = trim((string) $this->request->getPost('duracao_prova_horas'));

        $erros = [];

        if ($nomeDisciplina === '') {
            $erros[] = 'Informe o nome da disciplina.';
        }

        if ($dataInicio !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInicio)) {
            $erros[] = 'Data de inicio invalida.';
        }

        if ($dataFim !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)) {
            $erros[] = 'Data final invalida.';
        }

        if ($dataInicio !== '' && $dataFim !== '' && $dataFim < $dataInicio) {
            $erros[] = 'Data final nao pode ser menor que a data de inicio.';
        }

        if ($horaInicio !== '' && ! preg_match('/^\d{2}:\d{2}$/', $horaInicio)) {
            $erros[] = 'Hora de inicio invalida.';
        }

        if ($horaFim !== '' && ! preg_match('/^\d{2}:\d{2}$/', $horaFim)) {
            $erros[] = 'Hora final invalida.';
        }

        if ($duracao !== '' && (! ctype_digit($duracao) || (int) $duracao <= 0)) {
            $erros[] = 'Duracao da prova deve ser um numero inteiro maior que zero.';
        }

        $payload = [
            'nome_disciplina' => $nomeDisciplina,
            'data_inicio_avaliacao' => $dataInicio !== '' ? $dataInicio : null,
            'data_fim_avaliacao' => $dataFim !== '' ? $dataFim : null,
            'hora_inicio' => $horaInicio !== '' ? $horaInicio : null,
            'hora_fim' => $horaFim !== '' ? $horaFim : null,
            'duracao_prova_horas' => $duracao !== '' ? (int) $duracao : null,
        ];

        return [$payload, $erros];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function filtrarCamposGrupoAvaliacao(array $payload): array
    {
        $payloadFiltrado = [];

        foreach ($payload as $campo => $valor) {
            if ($this->campoExiste('grupo_avaliacao', $campo)) {
                $payloadFiltrado[$campo] = $valor;
            }
        }

        return $payloadFiltrado;
    }

    private function campoExiste(string $tabela, string $campo): bool
    {
        $db = db_connect();

        return $db->query("SHOW COLUMNS FROM {$tabela} LIKE '{$campo}'")->getRowArray() !== null;
    }

    /**
     * Extrai ate 5 alternativas da questao.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: ?string, 4: ?string}
     */
    private function extrairAlternativas(SimpleXMLElement $questaoXml): array
    {
        $respostas = [];

        if (isset($questaoXml->alternativas)) {
            foreach ($questaoXml->alternativas->alternativa as $alternativa) {
                $respostas[] = trim((string) $alternativa);
            }
        }

        $respostas = array_slice($respostas, 0, 5);

        while (count($respostas) < 5) {
            $respostas[] = null;
        }

        return [$respostas[0], $respostas[1], $respostas[2], $respostas[3], $respostas[4]];
    }

    private function mapearDificuldade(string $dificuldade): int
    {
        return match (strtolower(trim($dificuldade))) {
            'facil' => 1,
            'media' => 2,
            'dificil' => 3,
            default => 2,
        };
    }

    private function normalizarNomeTipo(string $tipoSlug): string
    {
        return match ($tipoSlug) {
            'verdadeiro_falso' => 'Verdadeiro ou Falso',
            'multipla_escolha' => 'Multipla Escolha',
            'multipla_resposta' => 'Multipla Resposta',
            'dissertativa' => 'Dissertativa',
            'resposta_curta' => 'Resposta Curta',
            default => ucwords(str_replace('_', ' ', $tipoSlug)),
        };
    }

    /**
     * @param array<string, int> $tiposCache
     */
    private function getOrCreateTipoResposta(object $db, string $tipoSlug, array &$tiposCache): int
    {
        if (isset($tiposCache[$tipoSlug])) {
            return $tiposCache[$tipoSlug];
        }

        $tipoTable = $db->table('tipos_resposta');
        $tipo = $tipoTable->where('slug', $tipoSlug)->get()->getRowArray();

        if ($tipo !== null) {
            $tiposCache[$tipoSlug] = (int) $tipo['id'];

            return $tiposCache[$tipoSlug];
        }

        $tipoTable->insert([
            'nome' => $this->normalizarNomeTipo($tipoSlug),
            'slug' => $tipoSlug,
            'descricao' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $tiposCache[$tipoSlug] = (int) $db->insertID();

        return $tiposCache[$tipoSlug];
    }

    private function upsertGrupoAvaliacao(object $db, string $disciplina): int
    {
        $grupoAvaliacaoTable = $db->table('grupo_avaliacao');
        $grupoAvaliacao = $grupoAvaliacaoTable->where('nome_disciplina', $disciplina)->get()->getRowArray();

        if ($grupoAvaliacao !== null) {
            return (int) $grupoAvaliacao['id'];
        }

        $grupoAvaliacaoTable->insert([
            'nome_disciplina' => $disciplina,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $db->insertID();
    }

    /**
     * @param array<string, mixed> $questionData
     *
     * @return array{0: int, 1: bool} [question_id, foiInserida]
     */
    private function upsertQuestion(object $db, array $questionData): array
    {
        $questionsTable = $db->table('questions');
        $existing = $questionsTable
            ->where('enunciado_questao', $questionData['enunciado_questao'])
            ->get()
            ->getRowArray();

        if ($existing !== null) {
            // Se a pergunta ja existe, nao grava novamente.
            return [(int) $existing['id'], false];
        }

        $questionsTable->insert($questionData);

        return [(int) $db->insertID(), true];
    }

    /**
     * @param list<int> $idsQuestoes
     */
    private function upsertGrupoQuestoes(object $db, string $nomeGrupo, array $idsQuestoes, ?int $grupoAvaliacaoId = null): int
    {
        $grupoQuestoesTable = $db->table('grupo_questoes');
        $grupoQuestoes = $grupoQuestoesTable->where('nome_grupo', $nomeGrupo)->get()->getRowArray();

        $payload = [
            'questoes_json' => json_encode($idsQuestoes, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($grupoAvaliacaoId !== null && $this->campoExiste('grupo_questoes', 'grupo_avaliacao_id')) {
            $payload['grupo_avaliacao_id'] = $grupoAvaliacaoId;
        }

        if ($grupoQuestoes !== null) {
            $grupoQuestoesTable->where('id', (int) $grupoQuestoes['id'])->update($payload);

            return (int) $grupoQuestoes['id'];
        }

        $grupoQuestoesTable->insert($payload + [
            'nome_grupo' => $nomeGrupo,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $db->insertID();
    }

    private function upsertDefinicaoAtividade(
        object $db,
        string $nomeAtividade,
        int $grupoAvaliacaoId,
        int $grupoQuestoesId,
    ): int {
        $definicaoTable = $db->table('definicao_atividade');
        $definicao = $definicaoTable->where('nome_atividade', $nomeAtividade)->get()->getRowArray();

        $payload = [
            'nome_atividade' => $nomeAtividade,
            'resposta_imediata' => 1,
            'avaliacao' => 1,
            'grupo_avaliacao_id' => $grupoAvaliacaoId,
            'grupo_questoes_id' => $grupoQuestoesId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($definicao !== null) {
            $definicaoTable->where('id', (int) $definicao['id'])->update($payload);

            return (int) $definicao['id'];
        }

        $definicaoTable->insert($payload + [
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $db->insertID();
    }
}