<?php
/**
 * Excluir Inspeção
 */

require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();

// Pegar ID da URL
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: inspecoes.php?erro=id_invalido');
    exit;
}

// Se confirmou a exclusão (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($supabase->deleteInspection($id)) {
            header('Location: inspecoes.php?sucesso=excluido');
            exit;
        } else {
            $erro = 'Erro ao excluir inspeção';
        }
    } catch (Exception $e) {
        $erro = 'Erro: ' . $e->getMessage();
    }
}

// Buscar dados da inspeção para mostrar confirmação
$inspecao = $supabase->getInspectionById($id);

if (!$inspecao) {
    header('Location: inspecoes.php?erro=nao_encontrado');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Inspeção - PowerChina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="assets/css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-contrast.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php 
            $paginaAtual = 'inspecoes';
            include 'includes/sidebar.php'; 
            ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">🗑️ Excluir Inspeção</h1>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ❌ <?= htmlspecialchars($erro) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">⚠️ Confirmar Exclusão</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>Atenção!</strong> Esta ação não pode ser desfeita. Todos os dados desta inspeção e suas fotos serão permanentemente excluídos.
                        </div>

                        <h6>Dados da Inspeção:</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">ID:</th>
                                <td><?= htmlspecialchars($inspecao['id']) ?></td>
                            </tr>
                            <tr>
                                <th>Usina:</th>
                                <td><?= htmlspecialchars($inspecao['usina'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Equipamento:</th>
                                <td><?= htmlspecialchars($inspecao['equipamento'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>TAG:</th>
                                <td><?= htmlspecialchars($inspecao['tag'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Data da Inspeção:</th>
                                <td>
                                    <?php 
                                    if (isset($inspecao['data_inspecao'])) {
                                        echo date('d/m/Y', strtotime($inspecao['data_inspecao']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>

                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta inspeção?');">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger">
                                    🗑️ Sim, Excluir Permanentemente
                                </button>
                                <a href="detalhes.php?id=<?= $id ?>" class="btn btn-secondary">
                                    ← Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
