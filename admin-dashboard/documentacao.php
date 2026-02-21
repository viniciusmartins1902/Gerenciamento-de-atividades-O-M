<?php
require_once 'auth.php';
require_once 'supabase.php';
requerLogin();

$supabase = new Supabase();
$nivel = getNivelAcesso();

// Buscar solicitações (para externos, só aprovadas)
if ($nivel === null) {
    $solicitacoes = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
        'status' => 'in.(aprovado_seguranca,finalizado)',
        'order' => 'criado_em.desc'
    ]);
} else {
    $solicitacoes = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
        'order' => 'criado_em.desc'
    ]);
}

// Separar por status
$pendentes = [];
$aprovadas = [];
$finalizadas = [];
foreach ($solicitacoes as $sol) {
    if ($sol['status'] === 'pendente') {
        $pendentes[] = $sol;
    } elseif (in_array($sol['status'], ['aprovado_om', 'aprovado_seguranca', 'em_execucao'])) {
        $aprovadas[] = $sol;
    } elseif ($sol['status'] === 'finalizado') {
        $finalizadas[] = $sol;
    }
}
function statusLabel($status) {
    switch ($status) {
        case 'pendente': return '<span class="badge bg-warning text-dark">Pendente</span>';
        case 'aprovado_om': return '<span class="badge bg-primary">Aprovado O&M</span>';
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
    <title>Documentação - Solicitações de Intervenção</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="assets/css/admin.css" rel="stylesheet">
    <link href="assets/css/admin-contrast.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); min-height: 100vh;">
<?php include 'includes/navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-journal-bookmark-fill fs-3 text-primary me-2"></i>
                        <h2 class="mb-0 fw-bold">Documentação de Solicitações</h2>
                    </div>
                      <?php require_once 'includes/doc-solicitacoes-lista.php'; ?>
                      <h4 class="mb-3 mt-2 text-primary"><i class="bi bi-hourglass-split me-1"></i> Pendentes</h4>
                      <?php doc_solicitacoes_lista($pendentes, 'pendentes'); ?>

                      <h4 class="mb-3 mt-4 text-success"><i class="bi bi-check2-circle me-1"></i> Aprovadas (ou aguardando segurança)</h4>
                      <?php doc_solicitacoes_lista($aprovadas, 'aprovadas'); ?>

                      <h4 class="mb-3 mt-4 text-dark"><i class="bi bi-archive me-1"></i> Finalizadas</h4>
                      <?php doc_solicitacoes_lista($finalizadas, 'finalizadas'); ?>
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
