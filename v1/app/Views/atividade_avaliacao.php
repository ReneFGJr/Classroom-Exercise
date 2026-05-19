<?php
/** @var array<string, mixed> $grupo */
/** @var list<array<string, mixed>> $respostas_por_aluno */
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
            <h1 class="h3 mb-1">Realizar Avaliacao</h1>
            <p class="text-secondary mb-0">Disciplina: <strong><?= esc($disciplina) ?></strong></p>
        </div>
        <div class="d-flex gap-2">
            <form method="post" action="<?= site_url('atividade/avaliacao/' . (int) ($grupo['id'] ?? 0) . '/corrigir-automatico') ?>" onsubmit="return confirm('Deseja corrigir automaticamente as questoes de multipla escolha?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success">Corrigir automaticamente</button>
            </form>
            <a href="<?= site_url('atividade/' . (int) ($grupo['id'] ?? 0)) ?>" class="btn btn-outline-primary">Voltar para atividade</a>
        </div>
    </div>

    <?php if ($respostas_por_aluno === []) : ?>
        <div class="alert alert-info">Nenhuma resposta encontrada para esta avaliacao.</div>
    <?php else : ?>
        <?php foreach ($respostas_por_aluno as $blocoAluno) : ?>
            <?php
            $nomeAluno = (string) ($blocoAluno['nome_usuario'] ?? 'Aluno sem nome');
            $emailAluno = trim((string) ($blocoAluno['email_usuario'] ?? ''));
            $respostasAluno = is_array($blocoAluno['respostas'] ?? null) ? $blocoAluno['respostas'] : [];
            ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h2 class="h5 mb-0"><?= esc($nomeAluno) ?></h2>
                        <?php if ($emailAluno !== '') : ?>
                            <small class="text-secondary"><?= esc($emailAluno) ?></small>
                        <?php endif; ?>
                    </div>
                    <span class="badge text-bg-light"><?= esc((string) count($respostasAluno)) ?> resposta(s)</span>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($respostasAluno as $indice => $resposta) : ?>
                            <?php
                            $respostaIdAtual = (int) ($resposta['resposta_id'] ?? 0);
                            $enunciado      = trim((string) ($resposta['enunciado_questao'] ?? 'Pergunta nao encontrada'));
                            $textoResposta  = trim((string) ($resposta['resposta_texto'] ?? ''));
                            $gabarito       = trim((string) ($resposta['resposta_correta'] ?? ''));
                            $foiCorrigido   = array_key_exists('corrigido', $resposta) && (int) ($resposta['corrigido'] ?? 0) === 1;
                            $avaliada       = $foiCorrigido ? 'Sim' : 'Nao';
                            $notaVal        = array_key_exists('nota', $resposta) && $resposta['nota'] !== null
                                                ? (int) $resposta['nota'] : null;
                            $nota           = $notaVal !== null ? (string) $notaVal : '-';
                            $comentarios    = trim((string) ($resposta['comentarios_correcao'] ?? ''));
                            $tipoQuestao    = trim((string) ($resposta['tipo_questao'] ?? ''));
                            $tipoQuestaoNorm = mb_strtolower($tipoQuestao);
                            $ehDissertativa = str_contains($tipoQuestaoNorm, 'dissert')
                                || str_contains($tipoQuestaoNorm, 'aberta')
                                || str_contains($tipoQuestaoNorm, 'texto');
                            $podeUsarJoinha = (! $foiCorrigido && $ehDissertativa)
                                || ($foiCorrigido && $notaVal === 0);
                            $offcanvasId    = 'offcanvas-correcao-' . $respostaIdAtual;
                            $ancoraQuestao  = 'questao-' . $respostaIdAtual;

                            if (! $foiCorrigido) {
                                $panelClass = 'border rounded p-3 bg-secondary-subtle border-secondary-subtle';
                                $badgeNota  = 'text-bg-secondary';
                            } elseif ($notaVal === 1) {
                                $panelClass = 'border rounded p-3 bg-success-subtle border-success';
                                $badgeNota  = 'text-bg-success';
                            } else {
                                $panelClass = 'border rounded p-3 bg-danger-subtle border-danger';
                                $badgeNota  = 'text-bg-danger';
                            }
                            ?>
                            <div id="<?= esc($ancoraQuestao) ?>" class="<?= $panelClass ?>">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div class="fw-semibold">
                                        Questao <?= esc((string) ($indice + 1)) ?>
                                        <span class="text-secondary fw-normal small">(ID <?= esc((string) $respostaIdAtual) ?>)</span>
                                        <?php if ($tipoQuestao !== '') : ?>
                                            <span class="badge text-bg-light border ms-1">Tipo: <?= esc($tipoQuestao) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge <?= $foiCorrigido ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                            Avaliada: <?= esc($avaliada) ?>
                                        </span>
                                        <?php if ($podeUsarJoinha) : ?>
                                            <form method="post" action="<?= site_url('atividade/avaliacao/' . (int) ($grupo['id'] ?? 0) . '/corrigir/' . $respostaIdAtual) ?>" class="mb-0">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="correta" value="1">
                                                <input type="hidden" name="validacao_manual" value="1">
                                                <input type="hidden" name="ancora" value="<?= esc($ancoraQuestao) ?>">
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Marcar como correta (nota 1)"
                                                    onclick="return confirm('Marcar esta questao como correta e atribuir nota 1?')"
                                                >
                                                    <i class="bi bi-hand-thumbs-up"></i> Joinha
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button
                                            type="button"
                                            class="badge <?= $badgeNota ?> border-0 text-decoration-none"
                                            style="cursor:pointer;"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#<?= esc($offcanvasId) ?>"
                                            aria-controls="<?= esc($offcanvasId) ?>"
                                        >Nota: <?= esc($nota) ?> ✏️</button>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <div class="small text-secondary">Pergunta</div>
                                    <div><?= esc($enunciado) ?></div>
                                </div>

                                <div class="mb-2">
                                    <div class="small text-secondary">Resposta do aluno</div>
                                    <div><?= esc($textoResposta !== '' ? $textoResposta : '-') ?></div>
                                </div>

                                <div class="mb-2">
                                    <div class="small text-secondary">Gabarito</div>
                                    <div><?= esc($gabarito !== '' ? $gabarito : '-') ?></div>
                                </div>

                                <div>
                                    <div class="small text-secondary">Comentarios da correcao</div>
                                    <div><?= esc($comentarios !== '' ? $comentarios : '-') ?></div>
                                </div>
                            </div>

                            <div class="offcanvas offcanvas-end" tabindex="-1" id="<?= esc($offcanvasId) ?>" aria-labelledby="<?= esc($offcanvasId) ?>Label">
                                <div class="offcanvas-header border-bottom">
                                    <h5 class="offcanvas-title" id="<?= esc($offcanvasId) ?>Label">
                                        Corrigir Questao <?= esc((string) ($indice + 1)) ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <p class="text-secondary small mb-3"><?= esc($enunciado) ?></p>

                                    <div class="mb-2">
                                        <div class="small fw-semibold text-secondary">Resposta do aluno</div>
                                        <div><?= esc($textoResposta !== '' ? $textoResposta : '-') ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small fw-semibold text-secondary">Gabarito</div>
                                        <div><?= esc($gabarito !== '' ? $gabarito : '-') ?></div>
                                    </div>

                                    <hr>

                                    <form method="post" action="<?= site_url('atividade/avaliacao/' . (int) ($grupo['id'] ?? 0) . '/corrigir/' . $respostaIdAtual) ?>">
                                        <?= csrf_field() ?>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Resultado da correcao</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="correta" id="<?= esc($offcanvasId) ?>_correta_sim" value="1" <?= $notaVal === 1 ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="<?= esc($offcanvasId) ?>_correta_sim">Correta (nota 1)</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="correta" id="<?= esc($offcanvasId) ?>_correta_nao" value="0" <?= $notaVal !== 1 ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="<?= esc($offcanvasId) ?>_correta_nao">Errada (nota 0)</label>
                                                </div>
                                            </div>
                                            <div class="form-text">A nota sera atribuida automaticamente: 1 para correta e 0 para errada.</div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="<?= esc($offcanvasId) ?>_comentarios" class="form-label fw-semibold">Comentarios</label>
                                            <textarea
                                                class="form-control"
                                                id="<?= esc($offcanvasId) ?>_comentarios"
                                                name="comentarios_correcao"
                                                rows="4"
                                                placeholder="Observacoes sobre a resposta..."
                                            ><?= esc($comentarios) ?></textarea>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Salvar correcao</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
