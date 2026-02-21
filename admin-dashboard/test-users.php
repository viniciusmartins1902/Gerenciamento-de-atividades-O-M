<?php
/**
 * Teste da Tabela Users
 */

require_once 'config.php';
require_once 'supabase.php';

echo "<h1>Teste da Tabela Users</h1>";
echo "<hr>";

$supabase = new Supabase();

// Testar conexão básica
echo "<h2>1. Teste de Conexão</h2>";
$teste = $supabase->request('GET', '/rest/v1/', null);
echo "Resposta: " . print_r($teste, true) . "<br>";

// Testar se a tabela users existe
echo "<h2>2. Listar Todos os Usuários</h2>";
$usuarios = $supabase->getUsuarios();
echo "<pre>";
print_r($usuarios);
echo "</pre>";

// Testar busca específica
echo "<h2>3. Buscar admin@powerchina.com.br</h2>";
$admin = $supabase->getUsuarioPorEmail('admin@powerchina.com.br');
echo "<pre>";
print_r($admin);
echo "</pre>";

// Testar busca específica
echo "<h2>4. Buscar vinicius.pimenta@powerchina.com.br</h2>";
$vinicius = $supabase->getUsuarioPorEmail('vinicius.pimenta@powerchina.com.br');
echo "<pre>";
print_r($vinicius);
echo "</pre>";
