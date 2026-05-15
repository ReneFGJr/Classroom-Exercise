<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<section class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="h3 mb-1">Painel Administrativo</h1>
            <p class="text-secondary mb-0">Acompanhamento das avaliacoes cadastradas no sistema.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/avaliations/new" class="btn btn-success btn-sm" title="Nova avaliacao" aria-label="Nova avaliacao">
                <i class="bi bi-plus-lg"></i>
            </a>
            <a href="/admin/importar-xml" class="btn btn-outline-primary btn-sm" title="Importar XML" aria-label="Importar XML">
                <i class="bi bi-filetype-xml"></i>
            </a>
            <a href="/admin/inport/users" class="btn btn-primary btn-sm" title="Importar usuarios" aria-label="Importar usuarios">
                <i class="bi bi-people-fill"></i>
            </a>
        </div>
    </div>
</section>

<?php if (session('erro')) : ?>
    <div class="alert alert-danger"><?= esc((string) session('erro')) ?></div>
<?php endif; ?>

<?php if (session('sucesso')) : ?>
    <div class="alert alert-success"><?= esc((string) session('sucesso')) ?></div>
<?php endif; ?>

<?= view('components/avaliacoes_disponiveis', [
    'avaliacoes' => $avaliacoes ?? [],
    'mensagem_aviso' => $mensagem_aviso ?? null,
]) ?>
<?= $this->endSection() ?>