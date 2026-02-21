<?php
/**
 * Página de Login
 */

require_once 'auth.php';

// Se já estiver logado, redireciona
if (estaLogado()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

// Processa login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (verificarLogin($email, $senha)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $erro = 'Email ou senha incorretos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard PowerChina</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="assets/images/images.jpg" alt="PowerChina" class="img-fluid mb-3" style="max-width: 140px; border-radius: 10px;">
                            <h2 class="fw-bold text-primary">Dashboard</h2>
                            <p class="text-muted">PowerChina - Inspeções</p>
                        </div>
                        
                        <?php if ($erro): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Senha</label>
                                <input type="password" name="senha" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted d-block mb-2">Acesso para empresas parceiras</small>
                            <a href="acesso-externo.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-key"></i> Acesso com Chave
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
