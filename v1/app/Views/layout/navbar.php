<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <?php $logado = (bool) session('auth_logged_in'); ?>
    <?php $isAdmin = (bool) session('auth_is_admin'); ?>
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?=base_url();?>/">Classroom Exercise</a>
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Alternar navegacao"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?=base_url();?>/">Inicio</a>
                </li>
                <?php if ($isAdmin) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?=base_url();?>/#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?=base_url();?>/admin/avaliations">Gerenciar avaliacoes</a></li>
                            <li><a class="dropdown-item" href="<?=base_url();?>/admin/usuarios">Usuarios cadastrados</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?=base_url();?>/admin/importar-xml">Importar XML</a></li>
                            <li><a class="dropdown-item" href="<?=base_url();?>/admin/inport/users">Import Users</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if ($logado) : ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1" href="<?=base_url();?>/logout" title="Sair">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sair</span>
                        </a>
                    </li>
                <?php else : ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1" href="<?=base_url();?>/login" title="Login">
                            <i class="bi bi-person-circle"></i>
                            <span>Login</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
