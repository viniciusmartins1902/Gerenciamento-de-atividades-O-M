<?php
/**
 * Detalhes de uma Inspeção Específica
 */

require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: inspecoes.php');
    exit;
}

$supabase = new Supabase();
$inspecao = $supabase->getInspection($id);
$fotos = $supabase->getPhotos($id);

if (!$inspecao) {
    header('Location: inspecoes.php');
    exit;
}

// Decodifica campos JSON
$campos_json = ['inspecao_visual', 'inspecao_termografica', 'limpeza', 'etiquetas', 'funcionamento'];
foreach ($campos_json as $campo) {
    if (isset($inspecao[$campo])) {
        $inspecao[$campo] = json_decode($inspecao[$campo], true) ?? [];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspeção #<?= $id ?> - PowerChina</title>
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
                    <h1 class="h2">🔍 Inspeção #<?= $id ?></h1>
                    <div>
                        <a href="debug-fotos.php?id=<?= $id ?>" class="btn btn-info btn-sm" target="_blank">🔍 Debug</a>
                        <a href="editar.php?id=<?= $id ?>" class="btn btn-warning">✏️ Editar</a>
                        <a href="excluir.php?id=<?= $id ?>" class="btn btn-danger">🗑️ Excluir</a>
                        <a href="gerar-pdf-tcpdf.php?id=<?= $id ?>" class="btn btn-success">📄 Gerar PDF</a>
                        <a href="inspecoes.php" class="btn btn-secondary">← Voltar</a>
                    </div>
                </div>

                <?php if (isset($_GET['sucesso'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        ✅ Inspeção atualizada com sucesso!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Informações Básicas -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informações Gerais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Campo:</strong> <?= htmlspecialchars($inspecao['campo']) ?></p>
                                <p><strong>Subcampo:</strong> <?= htmlspecialchars($inspecao['subcampo']) ?></p>
                                <p><strong>Inversor:</strong> <?= htmlspecialchars($inspecao['inversor']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Técnico 1:</strong> <?= htmlspecialchars($inspecao['tecnico1']) ?></p>
                                <p><strong>Técnico 2:</strong> <?= htmlspecialchars($inspecao['tecnico2']) ?></p>
                                <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($inspecao['data_criacao'])) ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <p><strong>Período:</strong> 
                                    <?= $inspecao['data_inicio'] ?> <?= $inspecao['hora_inicio'] ?> até 
                                    <?= $inspecao['data_final'] ?> <?= $inspecao['hora_final'] ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checklists -->
                <div class="row">
                    <?php
                    $checklists = [
                        'inspecao_visual' => 'Inspeção Visual',
                        'inspecao_termografica' => 'Inspeção Termográfica',
                        'limpeza' => 'Limpeza',
                        'etiquetas' => 'Etiquetas',
                        'funcionamento' => 'Funcionamento'
                    ];
                    
                    foreach ($checklists as $key => $titulo):
                        if (!empty($inspecao[$key])):
                    ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><?= $titulo ?></h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <?php foreach ($inspecao[$key] as $item => $valor): ?>
                                            <li class="mb-2">
                                                <?php if ($valor === 'ok' || $valor === true): ?>
                                                    <span class="text-success">✅</span>
                                                <?php else: ?>
                                                    <span class="text-danger">❌</span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($item) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>

                <!-- Comentários -->
                <?php if (!empty($inspecao['comentarios'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">💬 Comentários</h6>
                        </div>
                        <div class="card-body">
                            <p><?= nl2br(htmlspecialchars($inspecao['comentarios'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Fotos -->
                <?php if (!empty($fotos)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">📷 Fotos (<?= count($fotos) ?>)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php foreach ($fotos as $foto): ?>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="position-relative">
                                            <img src="<?= htmlspecialchars($foto['photo_data']) ?>" 
                                                 class="img-fluid rounded shadow-sm w-100" 
                                                 alt="Foto"
                                                 style="cursor: pointer; object-fit: cover; height: 200px;"
                                                 onclick="abrirFotoModal(this.src, '<?= date('d/m/Y H:i', strtotime($foto['created_at'])) ?>')">
                                            <small class="text-muted d-block mt-2 text-center">
                                                <?= date('d/m/Y H:i', strtotime($foto['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        📷 Nenhuma foto disponível para esta inspeção.
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal para visualizar foto em tamanho grande -->
    <div class="modal fade" id="modalFoto" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFotoTitulo">Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalFotoImg" src="" class="img-fluid" alt="Foto">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function abrirFotoModal(src, data) {
            document.getElementById('modalFotoImg').src = src;
            document.getElementById('modalFotoTitulo').textContent = 'Foto - ' + data;
            new bootstrap.Modal(document.getElementById('modalFoto')).show();
        }
    </script>
</body>
</html>
