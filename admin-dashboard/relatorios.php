<?php
/**
 * Relatórios e Exportação
 */

require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();

// Exportar para CSV
if (isset($_GET['exportar'])) {
    $inspecoes = $supabase->getInspections();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inspecoes_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    // Cabeçalho
    fputcsv($output, ['ID', 'Campo', 'Subcampo', 'Inversor', 'Técnico 1', 'Técnico 2', 'Data Início', 'Data Final', 'Data Criação'], ';');
    
    // Dados
    foreach ($inspecoes as $insp) {
        fputcsv($output, [
            $insp['id'],
            $insp['campo'],
            $insp['subcampo'],
            $insp['inversor'],
            $insp['tecnico1'],
            $insp['tecnico2'],
            $insp['data_inicio'],
            $insp['data_final'],
            date('d/m/Y H:i', strtotime($insp['data_criacao']))
        ], ';');
    }
    
    fclose($output);
    exit;
}

$stats = $supabase->getStats();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - PowerChina</title>
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
                    <h1 class="h2">📈 Relatórios</h1>
                    <div>
                        <a href="?exportar=csv" class="btn btn-success">📥 Exportar CSV</a>
                    </div>
                </div>

                <!-- Resumo Executivo -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Resumo Executivo</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Total de Inspeções</h6>
                                        <h3><?= $stats['total'] ?></h3>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Campos Ativos</h6>
                                        <h3><?= count($stats['por_campo']) ?></h3>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Técnicos Ativos</h6>
                                        <h3><?= count($stats['por_tecnico']) ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inspeções por Campo -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Inspeções por Campo</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th class="text-end">Quantidade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats['por_campo'] as $campo => $qtd): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($campo) ?></td>
                                                <td class="text-end"><span class="badge bg-primary"><?= $qtd ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Inspeções por Técnico -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Inspeções por Técnico</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Técnico</th>
                                            <th class="text-end">Quantidade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats['por_tecnico'] as $tecnico => $qtd): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($tecnico) ?></td>
                                                <td class="text-end"><span class="badge bg-success"><?= $qtd ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
