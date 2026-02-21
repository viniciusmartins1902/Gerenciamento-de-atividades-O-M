<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();
$mensagem = '';
$tipo_mensagem = '';

$email_usuario = $_SESSION['email'];
$usuario = $supabase->getUsuarioPorEmail($email_usuario);

// Se usuário não existe no banco, usar dados da sessão
if (!$usuario) {
    $usuario = [
        'id' => 0,
        'nome' => $_SESSION['nome'] ?? 'Usuário',
        'email' => $_SESSION['email'],
        'funcao' => 'admin',
        'foto' => null,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $mensagem = 'Aviso: Execute o SQL database-users.sql no Supabase para habilitar todas as funcionalidades.';
    $tipo_mensagem = 'warning';
}

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'atualizar_perfil') {
        $nome = $_POST['nome'] ?? '';
        $novo_email = $_POST['email'] ?? '';
        $funcao = $_POST['funcao'] ?? '';
        
        if ($nome && $novo_email && $funcao) {
            $resultado = $supabase->atualizarUsuario($usuario['id'], $nome, $novo_email, $funcao);
            if ($resultado) {
                $_SESSION['email'] = $novo_email;
                $_SESSION['nome'] = $nome;
                $_SESSION['funcao'] = $funcao;
                $mensagem = 'Perfil atualizado com sucesso!';
                $tipo_mensagem = 'success';
                $usuario = $supabase->getUsuarioPorEmail($novo_email);
            } else {
                $mensagem = 'Erro ao atualizar perfil.';
                $tipo_mensagem = 'danger';
            }
        }
    }
    
    if ($action === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $senha_nova = $_POST['senha_nova'] ?? '';
        $senha_confirma = $_POST['senha_confirma'] ?? '';
        
        if ($senha_nova === $senha_confirma) {
            $resultado = $supabase->alterarSenha($usuario['id'], $senha_atual, $senha_nova);
            if ($resultado) {
                $mensagem = 'Senha alterada com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Senha atual incorreta.';
                $tipo_mensagem = 'danger';
            }
        } else {
            $mensagem = 'As senhas não coincidem.';
            $tipo_mensagem = 'danger';
        }
    }
    
    if ($action === 'upload_foto') {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $foto = $_FILES['foto'];
            $extensao = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $nome_arquivo = 'user_' . $usuario['id'] . '_' . time() . '.' . $extensao;
                $caminho = 'assets/images/users/' . $nome_arquivo;
                
                if (!is_dir('assets/images/users')) {
                    mkdir('assets/images/users', 0777, true);
                }
                
                if (move_uploaded_file($foto['tmp_name'], $caminho)) {
                    $resultado = $supabase->atualizarFotoUsuario($usuario['id'], $caminho);
                    if ($resultado) {
                        $mensagem = 'Foto atualizada com sucesso!';
                        $tipo_mensagem = 'success';
                        $usuario = $supabase->getUsuarioPorEmail($email_usuario);
                    }
                }
            } else {
                $mensagem = 'Formato de imagem não permitido.';
                $tipo_mensagem = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - PowerChina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-contrast.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Meu Perfil</h1>
                </div>

                <?php if ($mensagem): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensagem) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Foto de Perfil -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Foto de Perfil</h5>
                                <?php if (!empty($usuario['foto'])): ?>
                                    <img src="<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                                        <i class="bi bi-person" style="font-size: 4rem; color: white;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="upload_foto">
                                    <div class="mb-3">
                                        <input type="file" name="foto" class="form-control" accept="image/*" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Atualizar Foto
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Informações Pessoais -->
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Informações Pessoais</h5>
                                <form method="POST">
                                    <input type="hidden" name="action" value="atualizar_perfil">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nome Completo</label>
                                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Cargo na Empresa</label>
                                        <input type="text" name="funcao" class="form-control" value="<?= htmlspecialchars($usuario['funcao']) ?>" placeholder="Ex: Gerente de Projetos, Técnico, Analista..." required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save"></i> Salvar Alterações
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Alterar Senha -->
                    <div class="col-md-8 offset-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Alterar Senha</h5>
                                <form method="POST">
                                    <input type="hidden" name="action" value="alterar_senha">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Senha Atual</label>
                                        <input type="password" name="senha_atual" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nova Senha</label>
                                        <input type="password" name="senha_nova" class="form-control" required minlength="6">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Confirmar Nova Senha</label>
                                        <input type="password" name="senha_confirma" class="form-control" required minlength="6">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-key"></i> Alterar Senha
                                    </button>
                                </form>
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
