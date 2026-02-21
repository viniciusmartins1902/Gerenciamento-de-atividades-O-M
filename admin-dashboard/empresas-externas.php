<?php
require_once 'auth.php';
require_once 'supabase.php';
require_once 'controle-acesso.php';

requerLogin();
if (getNivelAcesso() > 2) {
    header('Location: dashboard.php');
    exit;
}

$supabase = new Supabase();

$mensagem = '';
$tipo_mensagem = '';

// Editar empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_empresa') {
    $id = intval($_POST['id'] ?? 0);
    $nome_empresa = trim($_POST['nome_empresa'] ?? '');
    $chave = trim($_POST['chave'] ?? '');
    if ($id && $nome_empresa && $chave) {
        $result = $supabase->request('PATCH', '/rest/v1/access_keys?id=eq.' . $id, [
            'nome_empresa' => $nome_empresa,
            'chave' => strtoupper($chave)
        ]);
        if ($result) {
            $mensagem = 'Empresa atualizada com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao atualizar empresa.';
            $tipo_mensagem = 'danger';
        }
    }
}

// Excluir empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'excluir_empresa') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $result = $supabase->request('DELETE', '/rest/v1/access_keys?id=eq.' . $id);
        if ($result === '' || $result === null) {
            $mensagem = 'Empresa excluída com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao excluir empresa.';
            $tipo_mensagem = 'danger';
        }
    }
}

// Ativar/desativar empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_ativa') {
    $id = intval($_POST['id'] ?? 0);
    $ativa = ($_POST['ativa'] ?? '0') === '1' ? false : true;
    if ($id) {
        $result = $supabase->request('PATCH', '/rest/v1/access_keys?id=eq.' . $id, [
            'ativa' => $ativa
        ]);
        if ($result) {
            $mensagem = $ativa ? 'Empresa ativada!' : 'Empresa desativada!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao atualizar status.';
            $tipo_mensagem = 'danger';
        }
    }
}

// Cadastro de empresa externa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cadastrar_empresa') {
    $nome_empresa = trim($_POST['nome_empresa'] ?? '');
    $chave = trim($_POST['chave'] ?? '');
    if ($nome_empresa && $chave) {
        $result = $supabase->request('POST', '/rest/v1/access_keys', [
            'chave' => strtoupper($chave),
            'nome_empresa' => $nome_empresa,
            'ativa' => true
        ]);
        if ($result) {
            $mensagem = 'Empresa cadastrada com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao cadastrar empresa.';
            $tipo_mensagem = 'danger';
        }
    } else {
        $mensagem = 'Preencha todos os campos.';
        $tipo_mensagem = 'warning';
    }
}

// Listar empresas externas
$empresas = $supabase->request('GET', '/rest/v1/access_keys', null, ['order' => 'created_at.desc']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas Externas - PowerChina</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-contrast.css">
</head>
<body style="background: linear-gradient(135deg, #0f2027, #203a43, #8b0000); min-height: 100vh;">
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Empresas Externas</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastroEmpresa">
                        <i class="bi bi-building-add"></i> Nova Empresa
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
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Chave</th>
                                <th>Ativa</th>
                                <th>Criada em</th>
                                <th>Último acesso</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($empresas): ?>
                                <?php foreach ($empresas as $empresa): ?>
                                <tr>
                                    <td><?= htmlspecialchars($empresa['id']) ?></td>
                                    <td><?= htmlspecialchars($empresa['nome_empresa']) ?></td>
                                    <td><code><?= htmlspecialchars($empresa['chave']) ?></code></td>
                                    <td><?= $empresa['ativa'] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-danger">Não</span>' ?></td>
                                    <td><?= date('d/m/Y', strtotime($empresa['created_at'])) ?></td>
                                    <td><?= $empresa['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($empresa['ultimo_acesso'])) : '-' ?></td>
                                    <td>
                                        <!-- Editar -->
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditarEmpresa<?= $empresa['id'] ?>" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <!-- Excluir -->
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta empresa?');">
                                            <input type="hidden" name="action" value="excluir_empresa">
                                            <input type="hidden" name="id" value="<?= $empresa['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <!-- Ativar/Desativar -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_ativa">
                                            <input type="hidden" name="id" value="<?= $empresa['id'] ?>">
                                            <input type="hidden" name="ativa" value="<?= $empresa['ativa'] ? '1' : '0' ?>">
                                            <button type="submit" class="btn btn-sm <?= $empresa['ativa'] ? 'btn-secondary' : 'btn-success' ?>" title="<?= $empresa['ativa'] ? 'Desativar' : 'Ativar' ?>">
                                                <i class="bi <?= $empresa['ativa'] ? 'bi-x-circle' : 'bi-check-circle' ?>"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                   <!-- Removido: Modal de solicitação de intervenção -->
                                <!-- Modal Editar Empresa -->
                                <div class="modal fade" id="modalEditarEmpresa<?= $empresa['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Empresa</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="editar_empresa">
                                                    <input type="hidden" name="id" value="<?= $empresa['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nome da Empresa</label>
                                                        <input type="text" name="nome_empresa" class="form-control" value="<?= htmlspecialchars($empresa['nome_empresa']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Chave de Acesso</label>
                                                        <input type="text" name="chave" class="form-control" value="<?= htmlspecialchars($empresa['chave']) ?>" required style="text-transform: uppercase;">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Nenhuma empresa cadastrada</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <!-- Modal Cadastro Empresa -->
    <div class="modal fade" id="modalCadastroEmpresa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Nova Empresa Externa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="cadastrar_empresa">
                        <div class="mb-3">
                            <label class="form-label">Nome da Empresa</label>
                            <input type="text" name="nome_empresa" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Chave de Acesso</label>
                            <input type="text" name="chave" class="form-control" placeholder="POWERCHINA-EXT-2026-XXX" required style="text-transform: uppercase;">
                            <small class="text-muted">A chave deve ser única para cada empresa</small>
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
<script>
// Formulário dinâmico de colaboradores para cada empresa
function adicionarColaborador(empresaId) {
    if (!window['colabCount_' + empresaId]) window['colabCount_' + empresaId] = 0;
    var count = ++window['colabCount_' + empresaId];
    var html = `
    <div class="row mb-2" id="colab-row-${empresaId}-${count}">
        <div class="col-md-4">
            <input type="text" class="form-control" name="colaboradores[${count}][nome]" placeholder="Nome do colaborador" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="colaboradores[${count}][funcao]" placeholder="Função" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="colaboradores[${count}][origem]" placeholder="Origem" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger" onclick="removerColaborador(${empresaId}, ${count})">Remover</button>
        </div>
    </div>`;
    document.getElementById('colaboradores-list-' + empresaId).insertAdjacentHTML('beforeend', html);
}
function removerColaborador(empresaId, id) {
    var el = document.getElementById('colab-row-' + empresaId + '-' + id);
    if (el) el.remove();
}
</script>
</body>
</html>
