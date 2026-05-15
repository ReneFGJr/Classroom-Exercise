<?php $title = 'Classroom Exercise - Plataforma Academica'; ?>
<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<?php if (! (bool) session('auth_logged_in')) : ?>
    <div class="text-center py-5">
        <h1 class="display-4">Bem-vindo ao Classroom Exercise</h1>
        <p class="lead text-secondary">Acesse as atividades disponíveis para começar a praticar.</p>
        <a href="<?=base_url();?>/login" class="btn btn-primary btn-lg">Entrar</a>
    </div>
<?php else : ?>
    <div class="py-4">
        <div class="text-center mb-4">
            <h1 class="display-5">Bem-vindo de volta ao Classroom Exercise</h1>
            <p class="lead text-secondary">Explore as avaliacoes cadastradas e acompanhe os periodos de aplicacao.</p>
        </div>

        <?php $usuarioBasico = isset($usuario_basico) && is_array($usuario_basico) ? $usuario_basico : []; ?>
        <section class="card border-0 shadow-sm mb-4" id="dados-usuario">
            <div class="card-header bg-white py-3">
                <h2 class="h5 mb-0">Dados do usuario</h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="small text-secondary">Cracha</div>
                        <div class="fw-semibold"><?= esc((string) ($usuarioBasico['idcard'] ?? '-')) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-secondary">Nome</div>
                        <div class="fw-semibold"><?= esc((string) ($usuarioBasico['nome'] ?? '-')) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-secondary">Email</div>
                        <div class="fw-semibold"><?= esc((string) ($usuarioBasico['email'] ?? '-')) ?></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="avaliacoes" class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Avaliacoes</h2>
                <?php if (session('is_admin')): ?>
                    <a href="<?=base_url();?>/admin/avaliations" class="btn btn-primary">Gerenciar</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php $listaAvaliacoes = isset($avaliacoes) && is_array($avaliacoes) ? $avaliacoes : []; ?>
                <?php if ($listaAvaliacoes === []) : ?>
                    <div class="alert alert-info mb-0">Nenhuma avaliacao cadastrada no momento.</div>
                <?php else : ?>
                    <?php $agora = new DateTimeImmutable('now'); ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Disciplina</th>
                                    <th>Periodo</th>
                                    <th>Horario</th>
                                    <th>Duracao (h)</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaAvaliacoes as $avaliacao) : ?>
                                    <?php
                                    $id = (int) ($avaliacao['id'] ?? 0);
                                    $inicioData = trim((string) ($avaliacao['data_inicio_avaliacao'] ?? ''));
                                    $fimData = trim((string) ($avaliacao['data_fim_avaliacao'] ?? ''));
                                    $inicioHora = trim((string) ($avaliacao['hora_inicio'] ?? ''));
                                    $fimHora = trim((string) ($avaliacao['hora_fim'] ?? ''));

                                    $inicioJanela = null;
                                    $fimJanela = null;

                                    if ($inicioData !== '') {
                                        $inicioJanela = new DateTimeImmutable($inicioData . ' ' . ($inicioHora !== '' ? $inicioHora : '00:00'));
                                    }

                                    if ($fimData !== '') {
                                        $fimJanela = new DateTimeImmutable($fimData . ' ' . ($fimHora !== '' ? $fimHora : '23:59'));
                                    }

                                    $statusTexto = 'Sem agendamento';
                                    $statusClasse = 'secondary';

                                    if ($inicioJanela !== null && $agora < $inicioJanela) {
                                        $statusTexto = 'Ainda nao disponivel';
                                        $statusClasse = 'warning';
                                    } elseif ($fimJanela !== null && $agora > $fimJanela) {
                                        $statusTexto = 'Encerrada';
                                        $statusClasse = 'dark';
                                    } elseif ($inicioJanela !== null || $fimJanela !== null) {
                                        $statusTexto = 'Disponivel';
                                        $statusClasse = 'success';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $id ?></td>
                                        <td><?= esc((string) ($avaliacao['nome_disciplina'] ?? 'Sem nome')) ?></td>
                                        <td>
                                            <?= esc($inicioData !== '' ? $inicioData : '-') ?>
                                            ate
                                            <?= esc($fimData !== '' ? $fimData : '-') ?>
                                        </td>
                                        <td>
                                            <?= esc($inicioHora !== '' ? $inicioHora : '-') ?>
                                            -
                                            <?= esc($fimHora !== '' ? $fimHora : '-') ?>
                                        </td>
                                        <td><?= esc((string) ($avaliacao['duracao_prova_horas'] ?? '-')) ?></td>
                                        <td><span class="badge text-bg-<?= esc($statusClasse) ?>"><?= esc($statusTexto) ?></span></td>
                                        <td class="text-end">
                                            <a href="<?=base_url();?>/atividade/<?= $id ?>" class="btn btn-outline-primary btn-sm">Abrir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
