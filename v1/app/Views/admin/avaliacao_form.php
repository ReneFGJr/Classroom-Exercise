<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<section class="mb-4 d-flex align-items-center justify-content-between gap-2">
    <div>
        <h1 class="h3 mb-1"><?= esc((string) ($titulo_form ?? 'Formulario de avaliacao')) ?></h1>
        <p class="text-secondary mb-0">Preencha os dados da avaliacao.</p>
    </div>
    <a href="<?=base_url();?>//admin/avaliations" class="btn btn-outline-secondary btn-sm" title="Voltar" aria-label="Voltar">
        <i class="bi bi-arrow-left"></i>
    </a>
</section>

<?php if (session('erro')) : ?>
    <div class="alert alert-danger"><?= esc((string) session('erro')) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= esc((string) ($acao ?? '/admin/avaliations/store')) ?>" class="row g-3">
            <?= csrf_field() ?>

            <div class="col-12">
                <label for="nome_disciplina" class="form-label">Nome da disciplina</label>
                <input
                    type="text"
                    id="nome_disciplina"
                    name="nome_disciplina"
                    class="form-control"
                    required
                    value="<?= esc((string) old('nome_disciplina', (string) ($avaliacao['nome_disciplina'] ?? ''))) ?>"
                >
            </div>

            <div class="col-md-6 col-lg-3">
                <label for="data_inicio_avaliacao" class="form-label">Data inicio</label>
                <input
                    type="date"
                    id="data_inicio_avaliacao"
                    name="data_inicio_avaliacao"
                    class="form-control"
                    value="<?= esc((string) old('data_inicio_avaliacao', (string) ($avaliacao['data_inicio_avaliacao'] ?? ''))) ?>"
                >
            </div>

            <div class="col-md-6 col-lg-3">
                <label for="data_fim_avaliacao" class="form-label">Data final</label>
                <input
                    type="date"
                    id="data_fim_avaliacao"
                    name="data_fim_avaliacao"
                    class="form-control"
                    value="<?= esc((string) old('data_fim_avaliacao', (string) ($avaliacao['data_fim_avaliacao'] ?? ''))) ?>"
                >
            </div>

            <div class="col-md-6 col-lg-2">
                <label for="hora_inicio" class="form-label">Hora inicio</label>
                <input
                    type="time"
                    id="hora_inicio"
                    name="hora_inicio"
                    class="form-control"
                    value="<?= esc((string) old('hora_inicio', (string) ($avaliacao['hora_inicio'] ?? ''))) ?>"
                >
            </div>

            <div class="col-md-6 col-lg-2">
                <label for="hora_fim" class="form-label">Hora fim</label>
                <input
                    type="time"
                    id="hora_fim"
                    name="hora_fim"
                    class="form-control"
                    value="<?= esc((string) old('hora_fim', (string) ($avaliacao['hora_fim'] ?? ''))) ?>"
                >
            </div>

            <div class="col-md-6 col-lg-2">
                <label for="duracao_prova_horas" class="form-label">Duracao (h)</label>
                <input
                    type="number"
                    id="duracao_prova_horas"
                    name="duracao_prova_horas"
                    class="form-control"
                    min="1"
                    step="1"
                    value="<?= esc((string) old('duracao_prova_horas', (string) ($avaliacao['duracao_prova_horas'] ?? ''))) ?>"
                >
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary" title="Salvar avaliacao" aria-label="Salvar avaliacao">
                    <i class="bi bi-check2-circle"></i>
                </button>
                <a href="<?=base_url();?>//admin/avaliations" class="btn btn-light border" title="Cancelar" aria-label="Cancelar">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
