<?php
/**
 * Acesso Externo - Login por Chave de Acesso
 */

require_once 'config.php';
require_once 'supabase.php';

$erro = '';
$supabase = new Supabase();

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chave = trim($_POST['chave'] ?? '');
    
    if ($chave) {
        // Buscar chave no banco
        $result = $supabase->request('GET', '/rest/v1/access_keys', null, [
            'chave' => 'eq.' . $chave,
            'ativa' => 'eq.true'
        ]);
        
        if (!empty($result) && isset($result[0])) {
            $access = $result[0];
            
            // Atualizar último acesso
            $supabase->request('PATCH', '/rest/v1/access_keys?id=eq.' . $access['id'], [
                'ultimo_acesso' => date('Y-m-d H:i:s')
            ]);
            
            // Criar sessão externa
            $_SESSION['logado'] = true;
            $_SESSION['tipo_acesso'] = 'externo';
            $_SESSION['access_key_id'] = $access['id'];
            $_SESSION['nome'] = $access['nome_empresa'];
            $_SESSION['chave'] = $chave;
            $_SESSION['nivel_acesso'] = 5; // Nível especial para externos
            
            header('Location: perfil-externo.php');
            exit;
        } else {
            $erro = 'Chave de acesso inválida ou inativa.';
        }
    } else {
        $erro = 'Por favor, informe a chave de acesso.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Externo - PowerChina</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #8b0000);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="assets/images/images.jpg" alt="PowerChina" class="img-fluid mb-3" style="max-width: 140px; border-radius: 10px;">
                            <h2 class="fw-bold text-primary"><i class="bi bi-key"></i> Acesso Externo</h2>
                            <p class="text-muted">PowerChina - Empresas Parceiras</p>
                        </div>
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">Chave de Acesso</label>
                    <input 
                        type="text" 
                        name="chave" 
                        class="form-control form-control-lg" 
                        placeholder="POWERCHINA-EXT-XXXX-XXX"
                        required
                        autofocus
                        style="text-transform: uppercase;"
                    >
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Digite a chave fornecida pela PowerChina
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Acessar
                </button>
            </form>
            
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">
                                Você é colaborador interno?<br>
                                <a href="index.php" class="text-primary">Clique aqui para login com email</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
