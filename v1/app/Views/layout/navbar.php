<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <?php $logado = (bool) session('auth_logged_in'); ?>
    <div class="container">
        <a class="navbar-brand fw-semibold" href="/">Classroom Exercise</a>
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
                    <a class="nav-link" href="/">Inicio</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/admin/avaliations">Gerenciar avaliacoes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin/importar-cdd-xml">Importar XML</a></li>
                        <li><a class="dropdown-item" href="/admin/inport/users">Import Users</a></li>
                    </ul>
                </li>
                <?php if ($logado) : ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1" href="/logout" title="Sair">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sair</span>
                        </a>
                    </li>
                <?php else : ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1" href="/login" title="Login">
                            <i class="bi bi-person-circle"></i>
                            <span>Login</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
