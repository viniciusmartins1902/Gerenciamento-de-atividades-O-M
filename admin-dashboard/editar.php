<?php
/**
 * Editar Inspeção
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

if (!$inspecao) {
    header('Location: inspecoes.php');
    exit;
}

// Decodificar JSONs
$inspecao['inspecao_visual'] = json_decode($inspecao['inspecao_visual'] ?? '{}', true);
$inspecao['inspecao_termografica'] = json_decode($inspecao['inspecao_termografica'] ?? '{}', true);
$inspecao['limpeza'] = json_decode($inspecao['limpeza'] ?? '{}', true);
$inspecao['etiquetas'] = json_decode($inspecao['etiquetas'] ?? '{}', true);
$inspecao['funcionamento'] = json_decode($inspecao['funcionamento'] ?? '{}', true);

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'campo' => $_POST['campo'] ?? '',
        'subcampo' => $_POST['subcampo'] ?? '',
        'inversor' => $_POST['inversor'] ?? '',
        'tecnico1' => $_POST['tecnico1'] ?? '',
        'tecnico2' => $_POST['tecnico2'] ?? '',
        'data_inicio' => $_POST['data_inicio'] ?? '',
        'data_final' => $_POST['data_final'] ?? '',
        'hora_inicio' => $_POST['hora_inicio'] ?? '',
        'hora_final' => $_POST['hora_final'] ?? '',
        'comentarios' => $_POST['comentarios'] ?? ''
    ];

    if ($supabase->updateInspection($id, $dados)) {
        header('Location: detalhes.php?id=' . $id . '&sucesso=1');
        exit;
    } else {
        $erro = 'Erro ao atualizar inspeção';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Inspeção - PowerChina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">✏️ Editar Inspeção #<?= $inspecao['id'] ?></h1>
                    <div>
                        <a href="detalhes.php?id=<?= $inspecao['id'] ?>" class="btn btn-secondary">← Voltar</a>
                    </div>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5>Dados da Inspeção</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Campo *</label>
                                    <input type="text" name="campo" class="form-control" value="<?= htmlspecialchars($inspecao['campo']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subcampo *</label>
                                    <input type="text" name="subcampo" class="form-control" value="<?= htmlspecialchars($inspecao['subcampo']) ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Inversor *</label>
                                    <input type="text" name="inversor" class="form-control" value="<?= htmlspecialchars($inspecao['inversor']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Técnico 1 *</label>
                                    <input type="text" name="tecnico1" class="form-control" value="<?= htmlspecialchars($inspecao['tecnico1']) ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Técnico 2</label>
                                    <input type="text" name="tecnico2" class="form-control" value="<?= htmlspecialchars($inspecao['tecnico2'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <!-- Vazio -->
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Data Início *</label>
                                    <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars(substr($inspecao['data_inicio'], 0, 10)) ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Hora Início *</label>
                                    <input type="time" name="hora_inicio" class="form-control" value="<?= htmlspecialchars($inspecao['hora_inicio']) ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Data Final *</label>
                                    <input type="date" name="data_final" class="form-control" value="<?= htmlspecialchars(substr($inspecao['data_final'], 0, 10)) ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Hora Final *</label>
                                    <input type="time" name="hora_final" class="form-control" value="<?= htmlspecialchars($inspecao['hora_final']) ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Comentários</label>
                                <textarea name="comentarios" class="form-control" rows="4"><?= htmlspecialchars($inspecao['comentarios'] ?? '') ?></textarea>
                            </div>

                            <div class="alert alert-info">
                                <strong>ℹ️ Observação:</strong> Os checklists (inspeção visual, termográfica, etc.) não podem ser editados por questões de auditoria.
                            </div>

                            <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
                            <a href="detalhes.php?id=<?= $inspecao['id'] ?>" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
