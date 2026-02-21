<?php
/**
 * Ferramenta para adicionar foto de teste no Supabase
 */

require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();
$mensagem = '';
$tipo = '';

// Processar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar'])) {
    $inspection_id = $_POST['inspection_id'] ?? null;
    $foto_data = $_POST['foto_data'] ?? '';
    
    if ($inspection_id && $foto_data) {
        try {
            // Inserir foto diretamente no Supabase
            $endpoint = "/rest/v1/inspection_photos";
            $data = [
                'inspection_id' => (int)$inspection_id,
                'photo_data' => $foto_data,
                'photo_type' => 'image/jpeg'
            ];
            
            $result = $supabase->request('POST', $endpoint, $data);
            
            if ($result) {
                $mensagem = "✅ Foto adicionada com sucesso!";
                $tipo = 'success';
            } else {
                $mensagem = "❌ Erro ao adicionar foto.";
                $tipo = 'danger';
            }
        } catch (Exception $e) {
            $mensagem = "❌ Erro: " . $e->getMessage();
            $tipo = 'danger';
        }
    } else {
        $mensagem = "⚠️ Preencha todos os campos.";
        $tipo = 'warning';
    }
}

// Buscar inspeções
$inspecoes = $supabase->getInspections();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Adicionar Foto de Teste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container">
        <h1>📸 Adicionar Foto de Teste</h1>
        <p class="text-muted">Use esta ferramenta para adicionar uma foto de teste ao Supabase e verificar se o dashboard está funcionando.</p>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo ?> alert-dismissible fade show">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5>📤 Upload de Foto</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Selecione a Inspeção:</label>
                        <select name="inspection_id" class="form-select" required>
                            <option value="">-- Escolha --</option>
                            <?php foreach ($inspecoes as $insp): ?>
                                <option value="<?= $insp['id'] ?>">
                                    #<?= $insp['id'] ?> - <?= htmlspecialchars($insp['campo']) ?> / <?= htmlspecialchars($insp['inversor']) ?> 
                                    (<?= date('d/m/Y', strtotime($insp['data_criacao'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Selecione uma Foto:</label>
                        <input type="file" class="form-control" id="foto_file" accept="image/*" required>
                        <small class="text-muted">A foto será convertida para Base64 e salva no Supabase</small>
                    </div>

                    <input type="hidden" name="foto_data" id="foto_data">

                    <div id="preview" class="mb-3" style="display: none;">
                        <label class="form-label">Preview:</label><br>
                        <img id="preview_img" style="max-width: 300px; border: 2px solid #ddd; border-radius: 8px;">
                    </div>

                    <button type="submit" name="adicionar" class="btn btn-success" id="btnSubmit" disabled>
                        📸 Adicionar Foto
                    </button>
                </form>
            </div>
        </div>

        <div class="alert alert-info">
            <strong>ℹ️ Importante:</strong> Esta é uma ferramenta temporária de teste. 
            Depois de fazer o deploy da API corrigida no Vercel, as fotos virão automaticamente do app mobile.
        </div>

        <a href="debug-geral.php" class="btn btn-secondary">🔍 Ver Fotos</a>
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('foto_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const base64 = event.target.result;
                document.getElementById('foto_data').value = base64;
                document.getElementById('preview_img').src = base64;
                document.getElementById('preview').style.display = 'block';
                document.getElementById('btnSubmit').disabled = false;
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>
