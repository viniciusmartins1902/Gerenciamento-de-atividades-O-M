<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <strong>📱 PowerChina</strong> Dashboard
        </a>
        <div class="d-flex">
            <span class="navbar-text me-3">
                👤 <?= htmlspecialchars(nomeUsuario()) ?>
                <small class="text-muted ms-2">(Nível <?= getNivelAcesso() ?>)</small>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>
