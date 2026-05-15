<?= $this->extend('layout/base') ?>

<?= $this->section('content') ?>
<?php $listaUsuarios = $usuarios ?? []; ?>
<section class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="h3 mb-1">Usuarios cadastrados</h1>
            <p class="text-secondary mb-0">Lista de usuarios disponiveis para acesso ao sistema.</p>
        </div>
        <a href="<?=base_url();?>//admin/inport/users" class="btn btn-primary btn-sm" title="Importar usuarios" aria-label="Importar usuarios">
            <i class="bi bi-people-fill"></i>
            <span class="ms-1">Importar usuarios</span>
        </a>
    </div>
</section>

<?php if ($listaUsuarios === []) : ?>
    <div class="alert alert-warning mb-0">Nenhum usuario cadastrado no momento.</div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">ID cracha</th>
                    <th scope="col">Email</th>
                    <th scope="col">Perfil</th>
                    <th scope="col">Primeiro acesso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaUsuarios as $usuario) : ?>
                    <tr>
                        <td><?= esc((string) ($usuario['nome_completo'] ?? '')) ?></td>
                        <td><?= esc((string) ($usuario['usuario'] ?? '')) ?></td>
                        <td><?= esc((string) ($usuario['idcard'] ?? '')) ?></td>
                        <td><?= esc((string) ($usuario['email'] ?? '-')) ?></td>
                        <td>
                            <?php if ((int) ($usuario['is_admin'] ?? 0) === 1) : ?>
                                <span class="badge text-bg-primary">Admin</span>
                            <?php else : ?>
                                <span class="badge text-bg-secondary">Aluno</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int) ($usuario['primeiro_acesso'] ?? 0) === 1) : ?>
                                <span class="badge text-bg-warning">Pendente</span>
                            <?php else : ?>
                                <span class="badge text-bg-success">Concluido</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
