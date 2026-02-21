<?php
/**
 * Perfil para Usuários Externos
 */

require_once 'auth.php';

requerLogin();

// Verificar se é acesso externo
if (!isset($_SESSION['tipo_acesso']) || $_SESSION['tipo_acesso'] !== 'externo') {
    header('Location: dashboard.php');
    exit;
}

$nome_empresa = $_SESSION['nome'] ?? 'Empresa';
$chave = $_SESSION['chave'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - PowerChina</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body style="background: linear-gradient(135deg, #0f2027, #203a43, #8b0000); min-height: 100vh;">
    <!-- Navbar Simplificada -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <strong>📱 PowerChina</strong> - Acesso Externo
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    👤 <?= htmlspecialchars($nome_empresa) ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sem sidebar - apenas conteúdo principal -->
            <main class="col-12 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Bem-vindo(a)</h1>
                </div>

                <div class="row">
                    <!-- Card de Informações -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-building"></i> Informações de Acesso
                                </h5>
                                <a href="criar-solicitacao-externa.php" class="btn btn-success mt-2 mb-2">
                                    <i class="bi bi-file-earmark-plus"></i> Nova Solicitação de Intervenção
                                </a>
                                <a href="minhas-solicitacoes.php" class="btn btn-primary mt-2 mb-2 ms-2">
                                    <i class="bi bi-list-check"></i> Minhas Solicitações de Intervenção
                                </a>
                                <hr>
                                
                                <div class="mb-3">
                                    <label class="form-label"><strong>Empresa:</strong></label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($nome_empresa) ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><strong>Chave de Acesso:</strong></label>
                                    <p class="form-control-plaintext">
                                        <code><?= htmlspecialchars($chave) ?></code>
                                    </p>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Acesso Limitado:</strong> Sua conta tem acesso restrito ao sistema.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card de Instruções -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-question-circle"></i> Como Usar
                                </h5>
                                <hr>
                                
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle text-success"></i>
                                        <strong>Acesso Rápido:</strong> Use sua chave de acesso para entrar no sistema
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle text-success"></i>
                                        <strong>Segurança:</strong> Não compartilhe sua chave de acesso
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle text-success"></i>
                                        <strong>Suporte:</strong> Entre em contato com PowerChina para dúvidas
                                    </li>
                                </ul>
                                
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Atenção:</strong> Esta é uma versão simplificada do sistema.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
