<?php
require_once 'supabase.php';
require_once 'auth.php';

requerLogin();

$supabase = new Supabase();

echo "<h1>Teste de Conexão Supabase</h1>";

// Testar conexão básica
echo "<h2>1. Teste de Conexão</h2>";
$test = $supabase->request('GET', '/rest/v1/');
echo "<pre>";
print_r($test);
echo "</pre>";

// Verificar se tabela users existe
echo "<h2>2. Tabela Users</h2>";
$users = $supabase->request('GET', '/rest/v1/users');
echo "<pre>";
print_r($users);
echo "</pre>";

// Verificar se tabela intervention_requests existe
echo "<h2>3. Tabela Intervention Requests</h2>";
$requests = $supabase->request('GET', '/rest/v1/intervention_requests');
echo "<pre>";
print_r($requests);
echo "</pre>";

// Verificar dados da sessão
echo "<h2>4. Dados da Sessão</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?></content>
<parameter name="filePath">c:\dev\admin-dashboard\teste-supabase.php