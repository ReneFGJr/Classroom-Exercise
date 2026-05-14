<?php
/** @var array<string, mixed> $grupo */
/** @var list<array<string, mixed>> $atividades */
$disciplina = isset($grupo['nome_disciplina']) ? (string) $grupo['nome_disciplina'] : '-';
?>
<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<section class="py-3">
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
</section>
<?= $this->endSection() ?>