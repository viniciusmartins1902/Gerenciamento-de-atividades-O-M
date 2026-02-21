<?php
/**
 * Teste de Conexão Supabase
 * Acesse este arquivo para verificar se o Supabase está respondendo
 */

require_once 'config.php';

echo "<h1>Teste de Conexão Supabase</h1>";
echo "<hr>";

// 1. Verificar cURL
echo "<h2>1. Verificação cURL</h2>";
if (function_exists('curl_init')) {
    echo "✅ cURL está instalado e disponível<br>";
} else {
    echo "❌ cURL NÃO está disponível! Entre em contato com o suporte.<br>";
    die();
}

// 2. Verificar configurações
echo "<h2>2. Configurações</h2>";
echo "URL: " . SUPABASE_URL . "<br>";
echo "Key Length: " . strlen(SUPABASE_ANON_KEY) . " caracteres<br>";
echo "Key (primeiros 30): " . substr(SUPABASE_ANON_KEY, 0, 30) . "...<br>";
echo "Key (últimos 30): ..." . substr(SUPABASE_ANON_KEY, -30) . "<br>";
echo "<details><summary>Ver chave completa (clique)</summary><code>" . htmlspecialchars(SUPABASE_ANON_KEY) . "</code></details><br>";

// 3. Teste de requisição simples
echo "<h2>3. Validação da Chave</h2>";

// Verificar formato JWT
$keyParts = explode('.', SUPABASE_ANON_KEY);
echo "Partes do JWT: " . count($keyParts) . " (deve ser 3)<br>";

if (count($keyParts) === 3) {
    echo "✅ Formato JWT válido<br>";
    
    // Decodificar payload
    $payload = json_decode(base64_decode(strtr($keyParts[1], '-_', '+/')), true);
    if ($payload) {
        echo "Payload decodificado:<br>";
        echo "- Issuer: " . ($payload['iss'] ?? 'N/A') . "<br>";
        echo "- Ref: " . ($payload['ref'] ?? 'N/A') . "<br>";
        echo "- Role: " . ($payload['role'] ?? 'N/A') . "<br>";
        echo "- Expira em: " . ($payload['exp'] ?? 'N/A') . " (" . date('Y-m-d H:i:s', $payload['exp'] ?? 0) . ")<br>";
    }
} else {
    echo "❌ Formato JWT inválido!<br>";
}

echo "<h2>4. Teste de Requisição</h2>";

$url = SUPABASE_URL . '/rest/v1/inspections?limit=5';

$headers = [
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY,
    'Content-Type: application/json'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode<br>";
echo "cURL Error: " . ($error ? $error : 'Nenhum') . "<br>";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ Conexão bem-sucedida!<br>";
    
    $result = json_decode($response, true);
    
    if (is_array($result)) {
        echo "<h3>Dados retornados: " . count($result) . " registros</h3>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } else {
        echo "⚠️ Resposta não é um array<br>";
        echo "<pre>$response</pre>";
    }
} else {
    echo "❌ Falha na conexão!<br>";
    echo "<h3>Resposta:</h3>";
    echo "<pre>$response</pre>";
    
    echo "<h3>Diagnóstico:</h3>";
    if (strpos($response, 'Invalid API key') !== false) {
        echo "❌ A chave API está incorreta, expirada ou mal formatada<br>";
        echo "<strong>Ações sugeridas:</strong><br>";
        echo "1. Acesse o painel do Supabase: https://supabase.com/dashboard<br>";
        echo "2. Vá em Settings → API<br>";
        echo "3. Copie novamente a 'anon/public' key<br>";
        echo "4. Certifique-se de copiar a chave COMPLETA (sem espaços no início/fim)<br>";
        echo "5. Atualize o arquivo config.php com a nova chave<br>";
    }
}

// 5. Info do servidor
echo "<h2>5. Informações do Servidor</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Suporte SSL: " . (in_array('ssl', stream_get_transports()) ? 'Sim' : 'Não') . "<br>";
?>
