<?php
/** @var array<string, mixed> $grupo */
/** @var list<array<string, mixed>> $atividades */
/** @var list<array<string, mixed>> $questoes_selecionadas */
/** @var bool $avaliacao_finalizada */
/** @var bool $cronometro_ativo */
/** @var int $segundos_restantes */
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
        <a href="<?=base_url();?>/" class="btn btn-outline-primary">Voltar</a>
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

            <?php if (($avaliacao_finalizada ?? false) === true) : ?>
                <div class="alert alert-info">Avaliacao finalizada e enviada. Edicao bloqueada.</div>
            <?php endif; ?>

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
                                                <?= ($avaliacao_finalizada ?? false) === true ? 'disabled' : '' ?>
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
                                    <?= ($avaliacao_finalizada ?? false) === true ? 'disabled' : '' ?>
                                ><?= esc($respostaAtual) ?></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if (($avaliacao_finalizada ?? false) === true) : ?>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" disabled>Avaliacao finalizada</button>
                        </div>
                    <?php else : ?>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" name="acao" value="salvar" class="btn btn-primary">Salvar respostas</button>
                            <button
                                type="submit"
                                name="acao"
                                value="finalizar"
                                class="btn btn-success"
                                onclick="return confirm('Deseja finalizar e enviar? Depois disso nao sera possivel editar.')"
                            >
                                Finalizar e enviar
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (($cronometro_ativo ?? false) === true) : ?>
    <div id="contador-avaliacao" class="contador-avaliacao" data-segundos-restantes="<?= esc((string) max(0, (int) ($segundos_restantes ?? 0))) ?>">
        <div class="contador-avaliacao__titulo">Tempo restante</div>
        <div class="contador-avaliacao__valor" id="contador-avaliacao-valor">00:00:00</div>
    </div>

    <style>
        .contador-avaliacao {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 1055;
            min-width: 180px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #082a5e;
            color: #ffffff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.24);
        }

        .contador-avaliacao__titulo {
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            opacity: 0.95;
            margin-bottom: 2px;
        }

        .contador-avaliacao__valor {
            font-size: 1.5rem;
            line-height: 1.2;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
    </style>

    <script>
        (function () {
            const root = document.getElementById('contador-avaliacao');
            const valor = document.getElementById('contador-avaliacao-valor');

            if (!root || !valor) {
                return;
            }

            let segundosRestantes = parseInt(root.getAttribute('data-segundos-restantes') || '0', 10);
            if (Number.isNaN(segundosRestantes) || segundosRestantes < 0) {
                segundosRestantes = 0;
            }

            const formatarTempo = (totalSegundos) => {
                const horas = Math.floor(totalSegundos / 3600);
                const minutos = Math.floor((totalSegundos % 3600) / 60);
                const segundos = totalSegundos % 60;

                const hh = String(horas).padStart(2, '0');
                const mm = String(minutos).padStart(2, '0');
                const ss = String(segundos).padStart(2, '0');

                return `${hh}:${mm}:${ss}`;
            };

            const renderizar = () => {
                valor.textContent = formatarTempo(Math.max(0, segundosRestantes));
            };

            renderizar();

            const timer = setInterval(() => {
                if (segundosRestantes <= 0) {
                    clearInterval(timer);
                    valor.textContent = '00:00:00';
                    root.querySelector('.contador-avaliacao__titulo').textContent = 'Tempo encerrado';
                    return;
                }

                segundosRestantes -= 1;
                renderizar();
            }, 1000);
        })();
    </script>
<?php endif; ?>
<?= $this->endSection() ?>