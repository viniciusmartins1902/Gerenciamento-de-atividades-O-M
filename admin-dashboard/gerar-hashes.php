<?php
/**
 * Gerar Hashes de Senhas
 */

echo "<h1>Gerar Hashes de Senhas</h1>";
echo "<hr>";

$senhas = [
    'Mrt@2026' => 'Vinicius',
    'Admin@2026' => 'Administrador'
];

foreach ($senhas as $senha => $nome) {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    echo "<h3>$nome</h3>";
    echo "<strong>Senha:</strong> $senha<br>";
    echo "<strong>Hash:</strong> <code>$hash</code><br>";
    echo "<strong>Verificação:</strong> " . (password_verify($senha, $hash) ? '✅ OK' : '❌ FALHOU') . "<br><br>";
}

echo "<hr>";
echo "<h2>SQL para Atualizar</h2>";
echo "<pre>";
echo "-- Atualizar senha do Vinicius\n";
echo "UPDATE users SET senha = '" . password_hash('Mrt@2026', PASSWORD_DEFAULT) . "' WHERE email = 'vinicius.pimenta@powerchina.com.br';\n\n";
echo "-- Atualizar senha do Admin\n";
echo "UPDATE users SET senha = '" . password_hash('Admin@2026', PASSWORD_DEFAULT) . "' WHERE email = 'admin@powerchina.com.br';\n";
echo "</pre>";
