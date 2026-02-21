<?php
/**
 * Script para atualizar a chave do Supabase
 * Use este script para corrigir a SUPABASE_ANON_KEY se necessário
 */

// INSTRUÇÕES:
// 1. Vá para https://supabase.com/dashboard/project/grfbyqyuhwoeyjlsobit/settings/api
// 2. Copie a chave "anon public" COMPLETA
// 3. Cole abaixo substituindo o valor entre as aspas
// 4. Acesse este arquivo no navegador
// 5. Copie o código gerado e atualize o config.php

$nova_chave_supabase = 'COLE_AQUI_A_CHAVE_DO_SUPABASE';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atualizar Chave Supabase</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #1e88e5; }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔑 Atualizar Chave do Supabase</h1>
        
        <?php if ($nova_chave_supabase === 'COLE_AQUI_A_CHAVE_DO_SUPABASE'): ?>
            <div class="warning">
                <strong>⚠️ Instruções:</strong>
                <ol>
                    <li>Abra o arquivo <code>update-key.php</code> em um editor de texto</li>
                    <li>Acesse: <a href="https://supabase.com/dashboard/project/grfbyqyuhwoeyjlsobit/settings/api" target="_blank">Configurações da API do Supabase</a></li>
                    <li>Copie a chave <strong>"anon public"</strong> completa</li>
                    <li>Cole no lugar de <code>COLE_AQUI_A_CHAVE_DO_SUPABASE</code></li>
                    <li>Salve o arquivo e recarregue esta página</li>
                </ol>
            </div>
        <?php else: ?>
            <div class="success">
                <strong>✅ Chave detectada!</strong>
                <p>Comprimento: <?= strlen($nova_chave_supabase) ?> caracteres</p>
                
                <?php
                $keyParts = explode('.', $nova_chave_supabase);
                if (count($keyParts) === 3) {
                    echo "<p>✅ Formato JWT válido (3 partes)</p>";
                } else {
                    echo "<p>❌ Formato JWT inválido! Deve ter 3 partes separadas por pontos.</p>";
                }
                ?>
            </div>
            
            <h2>📋 Código para o config.php:</h2>
            <p>Copie o código abaixo e substitua a linha correspondente no arquivo <code>config.php</code>:</p>
            <pre><?= htmlspecialchars("define('SUPABASE_ANON_KEY', '$nova_chave_supabase');") ?></pre>
            
            <h2>🧪 Testar Conexão:</h2>
            <p>Depois de atualizar o <code>config.php</code>, teste a conexão:</p>
            <p><a href="test-supabase.php" style="display: inline-block; background: #1e88e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔗 Testar Supabase</a></p>
        <?php endif; ?>
        
        <hr style="margin: 30px 0;">
        
        <h2>📚 Onde encontrar a chave?</h2>
        <ol>
            <li>Acesse o <a href="https://supabase.com/dashboard" target="_blank">Painel do Supabase</a></li>
            <li>Selecione seu projeto</li>
            <li>Vá em <strong>Settings → API</strong></li>
            <li>Em "Project API keys", copie a chave <strong>"anon public"</strong></li>
        </ol>
        
        <p><strong>Nota:</strong> A chave deve começar com <code>eyJ...</code> e ter aproximadamente 200-300 caracteres.</p>
    </div>
</body>
</html>
