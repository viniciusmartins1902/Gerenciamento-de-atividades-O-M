<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();

// Buscar TODAS as fotos no banco
$endpoint = "/rest/v1/inspection_photos?select=*&order=created_at.desc&limit=100";
$todasFotos = $supabase->request('GET', $endpoint);

// Buscar todas as inspeções
$inspecoes = $supabase->getInspections();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Debug Geral - Fotos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container">
        <h1>🔍 Debug Geral - Sistema de Fotos</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5>📊 Estatísticas</h5>
            </div>
            <div class="card-body">
                <p><strong>Total de Inspeções:</strong> <?= count($inspecoes) ?></p>
                <p><strong>Total de Fotos no Banco:</strong> <?= is_array($todasFotos) ? count($todasFotos) : 0 ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5>📷 Todas as Fotos (primeiras 10)</h5>
            </div>
            <div class="card-body">
                <?php if (is_array($todasFotos) && count($todasFotos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Inspection ID</th>
                                    <th>Criado em</th>
                                    <th>Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($todasFotos, 0, 10) as $foto): ?>
                                <tr>
                                    <td><?= $foto['id'] ?? '-' ?></td>
                                    <td><?= $foto['inspection_id'] ?? '-' ?></td>
                                    <td><?= isset($foto['created_at']) ? date('d/m/Y H:i', strtotime($foto['created_at'])) : '-' ?></td>
                                    <td>
                                        <?php 
                                        $imgData = $foto['photo_data'] ?? $foto['foto_base64'] ?? null;
                                        if ($imgData): 
                                        ?>
                                            <img src="<?= htmlspecialchars($imgData) ?>" style="max-width: 100px; max-height: 60px;">
                                        <?php else: ?>
                                            <span class="text-danger">Sem dados</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        ⚠️ Nenhuma foto encontrada em TODO o banco de dados!
                        <hr>
                        <p><strong>Isso significa que:</strong></p>
                        <ul>
                            <li>O app mobile ainda não sincronizou nenhuma foto, OU</li>
                            <li>As fotos não estão sendo enviadas corretamente</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5>📋 Inspeções (primeiras 10)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Campo</th>
                                <th>Inversor</th>
                                <th>Técnico</th>
                                <th>Data</th>
                                <th>Sincronizado</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($inspecoes, 0, 10) as $insp): ?>
                            <tr>
                                <td><?= $insp['id'] ?></td>
                                <td><?= htmlspecialchars($insp['campo']) ?></td>
                                <td><?= htmlspecialchars($insp['inversor']) ?></td>
                                <td><?= htmlspecialchars($insp['tecnico1']) ?></td>
                                <td><?= date('d/m/Y', strtotime($insp['data_criacao'])) ?></td>
                                <td>
                                    <?php if ($insp['sincronizado']): ?>
                                        <span class="badge bg-success">✅</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">⏳</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="debug-fotos.php?id=<?= $insp['id'] ?>" target="_blank" class="btn btn-sm btn-info">Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning">
                <h5>⚙️ Dados Brutos da API</h5>
            </div>
            <div class="card-body">
                <h6>Resposta da API de Fotos:</h6>
                <pre style="max-height: 300px; overflow: auto;"><?= print_r($todasFotos, true) ?></pre>
            </div>
        </div>

        <hr>
        <a href="dashboard.php" class="btn btn-secondary">← Voltar ao Dashboard</a>
    </div>
</body>
</html>
