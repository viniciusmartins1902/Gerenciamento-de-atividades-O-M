<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$id = $_GET['id'] ?? 1;

$supabase = new Supabase();
$inspecao = $supabase->getInspection($id);
$fotos = $supabase->getPhotos($id);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Debug Fotos</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        pre { background: white; padding: 15px; border: 1px solid #ddd; overflow: auto; }
        h2 { color: #333; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>🔍 Debug - Inspeção #<?= $id ?></h1>
    
    <h2>Inspeção encontrada:</h2>
    <?php if ($inspecao): ?>
        <p class="success">✅ SIM</p>
        <pre><?= print_r($inspecao, true) ?></pre>
    <?php else: ?>
        <p class="error">❌ NÃO</p>
    <?php endif; ?>
    
    <h2>Fotos:</h2>
    <?php if ($fotos === null): ?>
        <p class="error">❌ Retornou NULL</p>
    <?php elseif (empty($fotos)): ?>
        <p class="error">⚠️ Array vazio - Nenhuma foto encontrada</p>
    <?php else: ?>
        <p class="success">✅ Encontradas <?= count($fotos) ?> fotos</p>
        <pre><?= print_r($fotos, true) ?></pre>
        
        <h3>Primeira foto (se existir):</h3>
        <?php if (isset($fotos[0])): ?>
            <p>Campos disponíveis: <?= implode(', ', array_keys($fotos[0])) ?></p>
            
            <?php if (isset($fotos[0]['photo_data'])): ?>
                <p>✅ Campo photo_data existe</p>
                <img src="<?= htmlspecialchars($fotos[0]['photo_data']) ?>" style="max-width: 300px;">
            <?php else: ?>
                <p class="error">❌ Campo photo_data NÃO existe</p>
            <?php endif; ?>
            
            <?php if (isset($fotos[0]['foto_base64'])): ?>
                <p>✅ Campo foto_base64 existe</p>
                <img src="<?= htmlspecialchars($fotos[0]['foto_base64']) ?>" style="max-width: 300px;">
            <?php else: ?>
                <p class="error">❌ Campo foto_base64 NÃO existe</p>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <hr>
    <a href="detalhes.php?id=<?= $id ?>">← Voltar</a>
</body>
</html>
