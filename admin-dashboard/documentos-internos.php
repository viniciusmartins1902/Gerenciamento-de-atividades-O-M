<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();

// Buscar solicitações internas
$solicitacoes = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
    'tipo' => 'eq.interno',
    'order' => 'criado_em.desc'
]);

function statusLabel($status) {
    switch ($status) {
        case 'pendente': return '<span class="badge bg-warning text-dark">Pendente</span>';
        case 'aprovado_seguranca': return '<span class="badge bg-success">Aprovado Segurança</span>';
        case 'finalizado': return '<span class="badge bg-dark">Finalizado</span>';
        case 'rejeitado': return '<span class="badge bg-danger">Rejeitado</span>';
        default: return $status;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos Internos - PowerChina</title>
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Documentos Internos</h1>
                    <a href="criar-solicitacao-interna.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nova Solicitação Interna
                    </a>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover bg-white">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Solicitante</th>
                                        <th>Data Inicial</th>
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
                                        <td><?= statusLabel($sol['status']) ?></td>
                                        <td>
                                            <a href="solicitacao-intervencao-pdf.php?id=<?= $sol['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Ver PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">Nenhuma solicitação interna encontrada.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar ao Dashboard</a>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>