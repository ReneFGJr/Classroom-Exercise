<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center py-4">
    <div class="col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3">Primeiro acesso</h1>
                <p class="text-secondary small mb-4">
                    Defina seu e-mail e uma nova senha para concluir o primeiro acesso.
                </p>

                <?php if (session('erro')) : ?>
                    <div class="alert alert-danger"><?= esc((string) session('erro')) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= base_url(); ?>/login/primeiro-acesso">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= esc((string) old('email', '')) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="senha_nova" class="form-label">Nova senha</label>
                        <input
                            type="password"
                            class="form-control"
                            id="senha_nova"
                            name="senha_nova"
                            minlength="6"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="senha_confirmacao" class="form-label">Confirmar senha</label>
                        <input
                            type="password"
                            class="form-control"
                            id="senha_confirmacao"
                            name="senha_confirmacao"
                            minlength="6"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Salvar dados</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>