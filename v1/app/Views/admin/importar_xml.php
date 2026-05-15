<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<section class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="h3 mb-1">Importacao de Arquivos XML</h1>
            <p class="text-secondary mb-0">Revise os arquivos encontrados e confirme a importacao.</p>
        </div>
        <a href="<?=base_url();?>//admin" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</section>

<?php if (session('erro')) : ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <?= esc((string) session('erro')) ?>
    </div>
<?php endif; ?>

<?php if (session('sucesso')) : ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-1"></i>
        <?= esc((string) session('sucesso')) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Resumo</h2>
        <p class="mb-1"><strong>Pasta de origem:</strong> <?= esc((string) ($pasta_origem ?? '')) ?></p>
        <p class="mb-0"><strong>Total de arquivos XML:</strong> <?= count($arquivos ?? []) ?></p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Arquivos encontrados</h2>

        <?php if (empty($arquivos)) : ?>
            <div class="alert alert-warning mb-0">Nenhum arquivo XML encontrado para importacao.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Arquivo</th>
                            <th class="text-end">Tamanho (KB)</th>
                            <th class="text-end">Ultima modificacao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($arquivos as $arquivo) : ?>
                            <tr>
                                <td><i class="bi bi-filetype-xml text-primary me-1"></i><?= esc((string) $arquivo['nome']) ?></td>
                                <td class="text-end"><?= esc((string) $arquivo['tamanho_kb']) ?></td>
                                <td class="text-end"><?= esc((string) $arquivo['modificado_em']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $resultado = $resultado_importacao ?? null; ?>
<?php if (is_array($resultado)) : ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Resultado da ultima importacao</h2>
            <p class="mb-1"><strong>Arquivos processados:</strong> <?= esc((string) ($resultado['total_processados'] ?? 0)) ?></p>
            <p class="mb-3"><strong>Erros:</strong> <?= esc((string) ($resultado['total_erros'] ?? 0)) ?></p>

            <?php if (! empty($resultado['arquivos_processados'])) : ?>
                <h3 class="h6">Processados com sucesso</h3>
                <ul>
                    <?php foreach ($resultado['arquivos_processados'] as $nomeArquivo) : ?>
                        <li><?= esc((string) $nomeArquivo) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (! empty($resultado['erros'])) : ?>
                <h3 class="h6 text-danger">Erros encontrados</h3>
                <ul class="mb-0">
                    <?php foreach ($resultado['erros'] as $erro) : ?>
                        <li>
                            <strong><?= esc((string) ($erro['arquivo'] ?? 'arquivo_desconhecido')) ?>:</strong>
                            <?= esc((string) ($erro['erro'] ?? 'Erro nao informado')) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-end gap-2">
    <a href="<?=base_url();?>//admin" class="btn btn-light border">Cancelar</a>
    <form method="post" action="/admin/importar-xml/confirmar" class="m-0">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary" <?= empty($arquivos) ? 'disabled' : '' ?>>
            <i class="bi bi-database-fill-up me-1"></i>
            Confirmar importacao
        </button>
    </form>
</div>
<?= $this->endSection() ?>
