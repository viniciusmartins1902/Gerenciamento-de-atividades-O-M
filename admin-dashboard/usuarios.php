<?php
require_once 'auth.php';
require_once 'supabase.php';
require_once 'controle-acesso.php';

requerLogin();
verificarAcesso('usuarios'); // Apenas níveis 1 e 2

$supabase = new Supabase();
$mensagem = '';
$tipo_mensagem = '';
$nivel_usuario = getNivelAcesso();

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cadastrar') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $funcao = $_POST['funcao'] ?? 'Usuário';
    $nivel_acesso = intval($_POST['nivel_acesso'] ?? 4);
    
    if ($nome && $email && $senha) {
        $resultado = $supabase->cadastrarUsuario($nome, $email, $senha, $funcao, $nivel_acesso);
        if ($resultado) {
            $mensagem = 'Usuário cadastrado com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao cadastrar usuário.';
            $tipo_mensagem = 'danger';
        }
    }
}

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'excluir') {
    $id = $_POST['id'] ?? '';
    if ($id) {
        $resultado = $supabase->excluirUsuario($id);
        if ($resultado) {
            $mensagem = 'Usuário excluído com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao excluir usuário.';
            $tipo_mensagem = 'danger';
        }
    }
}

// Buscar todos os usuários
$usuarios = $supabase->getUsuarios();

// Se não houver usuários, mostrar aviso
if (!$usuarios || count($usuarios) === 0) {
    $mensagem = 'Execute o SQL database-users.sql no Supabase para criar a tabela de usuários.';
    $tipo_mensagem = 'warning';
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - PowerChina</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
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
                    <h1 class="h2">Gerenciar Usuários</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastro">
                        <i class="bi bi-person-plus"></i> Novo Usuário
                    </button>
                </div>

                <?php if ($mensagem): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensagem) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Cargo</th>
                                <th>Nível de Acesso</th>
                                <th>Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($usuarios): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($usuario['foto'])): ?>
                                            <img src="<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td><?= htmlspecialchars($usuario['funcao'] ?? 'Não definido') ?></td>
                                    <td>
                                        <?php
                                        $nivel = $usuario['nivel_acesso'] ?? 4;
                                        $badges = [1 => 'danger', 2 => 'warning', 3 => 'info', 4 => 'secondary'];
                                        ?>
                                        <span class="badge bg-<?= $badges[$nivel] ?? 'secondary' ?>">
                                            Nível <?= $nivel ?> - <?= getNomeNivel($nivel) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($usuario['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            <input type="hidden" name="action" value="excluir">
                                            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum usuário cadastrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Cadastro -->
    <div class="modal fade" id="modalCadastro" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="cadastrar">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" name="senha" class="form-control" required minlength="6">
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cargo na Empresa</label>
                            <input type="text" name="funcao" class="form-control" placeholder="Ex: Técnico, Analista, Gerente..." required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nível de Acesso</label>
                            <select name="nivel_acesso" class="form-select" required>
                                <?php if ($nivel_usuario == 1): ?>
                                <option value="1">Nível 1 - Administrador Master (Acesso Total)</option>
                                <?php endif; ?>
                                <option value="2">Nível 2 - Gerente (Sem padronizar técnicos)</option>
                                <option value="3">Nível 3 - Analista (Dashboard e relatórios)</option>
                                <option value="4" selected>Nível 4 - Segurança (Apenas perfil)</option>
                            </select>
                            <small class="text-muted">Define o que o usuário pode acessar no sistema</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
