<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

// Verifica se é acesso externo
if (!isset($_SESSION['tipo_acesso']) || $_SESSION['tipo_acesso'] !== 'externo') {
    header('Location: dashboard.php');
    exit;
}

$empresa_id = $_SESSION['access_key_id'];
$nome_empresa = $_SESSION['nome'] ?? 'Empresa';

$supabase = new Supabase();
// Busca solicitações só da empresa logada
$solicitacoes = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
    'empresa_id' => 'eq.' . $empresa_id,
    'order' => 'criado_em.desc'
]);

// Tratamento de mensagens
$mensagem = '';
$tipo_mensagem = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'execucao_marcada':
            $mensagem = 'Solicitação marcada como em execução!';
            $tipo_mensagem = 'success';
            break;
        case 'editada':
            $mensagem = 'Solicitação editada com sucesso!';
            $tipo_mensagem = 'success';
            break;
        case 'excluida':
            $mensagem = 'Solicitação excluída com sucesso!';
            $tipo_mensagem = 'success';
            break;
    }
}
if (isset($_GET['erro'])) {
    switch ($_GET['erro']) {
        case 'execucao_falhou':
            $mensagem = 'Erro ao marcar solicitação como em execução.';
            $tipo_mensagem = 'danger';
            break;
        case 'exclusao_falhou':
            $mensagem = 'Erro ao excluir solicitação.';
            $tipo_mensagem = 'danger';
            break;
    }
}

function statusLabel($status) {
    switch ($status) {
        case 'pendente': return '<span class="badge bg-warning text-dark">Pendente</span>';
        case 'aprovado_om': return '<span class="badge bg-primary">Aprovado O&M</span>';
        case 'aprovado_seguranca': return '<span class="badge bg-success">Aprovado Segurança</span>';
        case 'em_execucao': return '<span class="badge bg-info">Em Execução</span>';
        case 'finalizado': return '<span class="badge bg-dark">Finalizado</span>';
        case 'rejeitado': return '<span class="badge bg-danger">Rejeitado</span>';
        default: return $status;
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Solicitações de Intervenção</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body style="background: linear-gradient(135deg, #0f2027, #203a43, #8b0000); min-height: 100vh;">
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
    <div class="container py-4">
        <h2 class="mb-4">Minhas Solicitações de Intervenção</h2>
        <?php if ($mensagem): ?>
        <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($solicitacoes && count($solicitacoes) > 0): ?>
                    <?php foreach ($solicitacoes as $sol): ?>
                    <tr>
                        <td><?= htmlspecialchars($sol['id']) ?></td>
                        <td><?= htmlspecialchars($sol['solicitante']) ?></td>
                        <td><?= htmlspecialchars($sol['data_inicial']) ?></td>
                        <td><?= htmlspecialchars($sol['data_final']) ?></td>
                        <td><?= statusLabel($sol['status']) ?></td>
                        <td>
                            <a href="solicitacao-intervencao-pdf.php?id=<?= $sol['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Ver PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            <?php if ($sol['status'] === 'pendente'): ?>
                                <a href="editar-solicitacao.php?id=<?= $sol['id'] ?>" class="btn btn-sm btn-warning" title="Editar Solicitação"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="excluir-solicitacao.php" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta solicitação? Esta ação não pode ser desfeita.');">
                                    <input type="hidden" name="id" value="<?= $sol['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Excluir Solicitação"><i class="bi bi-trash"></i></button>
                                </form>
                            <?php elseif ($sol['status'] === 'aprovado_seguranca'): ?>
                                <form method="POST" action="marcar-execucao.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $sol['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Marcar esta solicitação como em execução?');" title="Marcar como Em Execução">
                                        <i class="bi bi-play-circle"></i> Em Execução
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Nenhuma solicitação encontrada.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="perfil-externo.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Voltar ao Perfil</a>
    </div>
</body>
</html>
