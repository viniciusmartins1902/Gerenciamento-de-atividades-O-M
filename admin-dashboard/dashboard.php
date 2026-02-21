<?php
/**
 * Dashboard Principal com Estatísticas
 */

require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();
$stats = $supabase->getStats();

// Verifica se houve erro de conexão
$erro_conexao = isset($stats['erro']) ? $stats['erro'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PowerChina</title>
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
                    <h1 class="h2">📊 Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="text-muted">Olá, <?= htmlspecialchars(nomeUsuario()) ?></span>
                    </div> 
                </div>

                <?php if ($erro_conexao): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ Aviso:</strong> <?= htmlspecialchars($erro_conexao) ?>
                        <br><small>Verifique se o cURL está habilitado no servidor e se as credenciais do Supabase estão corretas.</small>
                    </div>
                <?php endif; ?>

                <?php if (!function_exists('curl_init')): ?>
                    <div class="alert alert-danger">
                        <strong>❌ Erro Crítico:</strong> cURL não está instalado ou habilitado neste servidor!
                        <br><small>Entre em contato com o suporte do InfinityFree para habilitar a extensão cURL.</small>
                    </div>
                <?php endif; ?>

                <!-- Cards de Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total de Inspeções</h6>
                                <h2 class="mb-0"><?= $stats['total'] ?></h2>
                                <small>Todas as inspeções</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Hoje</h6>
                                <h2 class="mb-0"><?= $stats['hoje'] ?></h2>
                                <small>Inspeções de hoje</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Campos</h6>
                                <h2 class="mb-0"><?= count($stats['por_campo']) ?></h2>
                                <small>Campos ativos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">Técnicos</h6>
                                <h2 class="mb-0"><?= count($stats['por_tecnico']) ?></h2>
                                <small>Técnicos ativos</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Inspeções por Campo</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartCampos"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Inspeções por Técnico</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTecnicos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Últimos 7 Dias</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDias"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Dados dos gráficos
        const porCampo = <?= json_encode($stats['por_campo']) ?>;
        const porTecnico = <?= json_encode($stats['por_tecnico']) ?>;
        const porDia = <?= json_encode($stats['por_dia']) ?>;

        // Gráfico por Campo
        new Chart(document.getElementById('chartCampos'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(porCampo),
                datasets: [{
                    data: Object.values(porCampo),
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0']
                }]
            }
        });

        // Gráfico por Técnico
        new Chart(document.getElementById('chartTecnicos'), {
            type: 'bar',
            data: {
                labels: Object.keys(porTecnico),
                datasets: [{
                    label: 'Inspeções',
                    data: Object.values(porTecnico),
                    backgroundColor: '#0d6efd'
                }]
            }
        });

        // Gráfico por Dia
        const diasOrdenados = Object.keys(porDia).sort().slice(-7);
        new Chart(document.getElementById('chartDias'), {
            type: 'line',
            data: {
                labels: diasOrdenados.map(d => new Date(d).toLocaleDateString('pt-BR')),
                datasets: [{
                    label: 'Inspeções por Dia',
                    data: diasOrdenados.map(d => porDia[d]),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            }
        });
    </script>
</body>
</html>
