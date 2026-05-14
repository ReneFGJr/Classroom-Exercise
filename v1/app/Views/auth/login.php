<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center py-4">
    <div class="col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3">Login</h1>
                <p class="text-secondary small mb-4">
                    Informe o ID do cracha com 8 digitos.
                    Se nao for o primeiro acesso, informe tambem sua senha.
                </p>

                <?php if (session('erro')) : ?>
                    <div class="alert alert-danger"><?= esc((string) session('erro')) ?></div>
                <?php endif; ?>

                <?php if (session('sucesso')) : ?>
                    <div class="alert alert-success"><?= esc((string) session('sucesso')) ?></div>
                <?php endif; ?>

                <form method="post" action="/login">
                    <div class="mb-3">
                        <label for="idcard" class="form-label">ID do cracha</label>
                        <input
                            type="text"
                            class="form-control"
                            id="idcard"
                            name="idcard"
                            maxlength="8"
                            minlength="8"
                            pattern="\d{8}"
                            value="<?= esc((string) old('idcard', '')) ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <input
                            type="password"
                            class="form-control"
                            id="senha"
                            name="senha"
                            placeholder="Obrigatoria apos o primeiro acesso"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>