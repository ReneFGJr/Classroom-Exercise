<?php
/** @var array<int, array<string, mixed>> $avaliacoes */
/** @var string|null $mensagem_aviso */
?>
<section class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0">Avaliacoes disponiveis</h2>
    </div>
    <div class="card-body">
        <?php if (! empty($mensagem_aviso)) : ?>
            <div class="alert alert-warning mb-0"><?= esc($mensagem_aviso) ?></div>
        <?php elseif ($avaliacoes === []) : ?>
            <div class="alert alert-info mb-0">Nenhuma avaliacao cadastrada no momento.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 90px;">ID</th>
                            <th scope="col">Disciplina</th>
                            <th scope="col" style="width: 220px;">Criada em</th>
                            <th scope="col" style="width: 240px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($avaliacoes as $avaliacao) : ?>
                            <?php $idAvaliacao = (int) ($avaliacao['id'] ?? 0); ?>
                            <tr>
                                <td><?= $idAvaliacao ?></td>
                                <td>
                                    <a href="<?=base_url();?>/atividade/<?= $idAvaliacao ?>" class="text-decoration-none fw-semibold">
                                        <?= esc((string) ($avaliacao['nome_disciplina'] ?? 'Sem nome')) ?>
                                    </a>
                                </td>
                                <td><?= esc((string) ($avaliacao['created_at'] ?? '-')) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?=base_url();?>/admin/avaliations/<?= $idAvaliacao ?>/edit" class="btn btn-outline-secondary btn-sm" title="Editar avaliacao" aria-label="Editar avaliacao">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="post" action="<?= base_url(); ?> ?>/admin/avaliations/<?= $idAvaliacao ?>/delete" onsubmit="return confirm('Deseja excluir esta avaliacao?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Excluir avaliacao" aria-label="Excluir avaliacao">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>