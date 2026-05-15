<?php
/** @var array<string, mixed> $grupo */
/** @var list<array<string, mixed>> $atividades */
/** @var list<array<string, mixed>> $questoes_selecionadas */
$disciplina = isset($grupo['nome_disciplina']) ? (string) $grupo['nome_disciplina'] : '-';
$sucesso = session('sucesso');
$erro = session('erro');
?>
<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<section class="py-3">
    <?php if (is_string($sucesso) && $sucesso !== '') : ?>
        <div class="alert alert-success" role="alert"><?= esc($sucesso) ?></div>
    <?php endif; ?>

    <?php if (is_string($erro) && $erro !== '') : ?>
        <div class="alert alert-danger" role="alert"><?= esc($erro) ?></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Grupo de Avaliacao</h1>
            <p class="text-secondary mb-0">Disciplina: <strong><?= esc($disciplina) ?></strong></p>
        </div>
        <a href="/" class="btn btn-outline-primary">Voltar</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if ($atividades === []) : ?>
                <p class="mb-0 text-secondary">Nenhuma atividade vinculada a este grupo de avaliacao.</p>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome da Atividade</th>
                                <th>Resposta Imediata</th>
                                <th>Avaliacao</th>
                                <th>Grupo Questoes ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($atividades as $atividade) : ?>
                                <tr>
                                    <td><?= esc((string) ($atividade['id'] ?? '')) ?></td>
                                    <td><?= esc((string) ($atividade['nome_atividade'] ?? '')) ?></td>
                                    <td><?= (int) ($atividade['resposta_imediata'] ?? 0) === 1 ? 'Sim' : 'Nao' ?></td>
                                    <td><?= (int) ($atividade['avaliacao'] ?? 0) === 1 ? 'Sim' : 'Nao' ?></td>
                                    <td><?= esc((string) ($atividade['grupo_questoes_id'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <h2 class="h5 mb-3">Avaliacao</h2>

            <?php if ($questoes_selecionadas === []) : ?>
                <p class="mb-0 text-secondary">Nenhuma questao selecionada para esta avaliacao.</p>
            <?php else : ?>
                <form method="post" action="<?= site_url('atividade/' . (int) ($grupo['id'] ?? 0) . '/responder') ?>">
                    <?= csrf_field() ?>

                    <?php foreach ($questoes_selecionadas as $indice => $questao) : ?>
                        <?php
                        $respostaId = (int) ($questao['resposta_id'] ?? 0);
                        $respostaAtual = (string) ($questao['resposta_texto'] ?? '');
                        $opcoes = [];

                        foreach (['resposta_1', 'resposta_2', 'resposta_3', 'resposta_4', 'resposta_5'] as $campoOpcao) {
                            $opcao = trim((string) ($questao[$campoOpcao] ?? ''));
                            if ($opcao !== '') {
                                $opcoes[] = $opcao;
                            }
                        }
                        ?>

                        <div class="border rounded p-3 mb-3">
                            <p class="fw-semibold mb-2">
                                <?= esc((string) ($indice + 1)) ?>.
                                <?= esc((string) ($questao['enunciado_questao'] ?? 'Questao sem enunciado.')) ?>
                            </p>

                            <?php if ($opcoes !== []) : ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($opcoes as $opcao) : ?>
                                        <label class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="radio"
                                                name="respostas[<?= esc((string) $respostaId) ?>]"
                                                value="<?= esc($opcao) ?>"
                                                <?= $respostaAtual === $opcao ? 'checked' : '' ?>
                                            >
                                            <span class="form-check-label"><?= esc($opcao) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <label class="form-label" for="resposta_<?= esc((string) $respostaId) ?>">Resposta</label>
                                <textarea
                                    class="form-control"
                                    id="resposta_<?= esc((string) $respostaId) ?>"
                                    name="respostas[<?= esc((string) $respostaId) ?>]"
                                    rows="3"
                                ><?= esc($respostaAtual) ?></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Salvar respostas</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>