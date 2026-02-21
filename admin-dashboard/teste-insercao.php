<?php
require_once 'supabase.php';

$supabase = new Supabase();

// Teste simples de inserção
$dados_teste = [
    'tipo' => 'interno',
    'usuario_interno_id' => 1,
    'solicitante' => 'Teste Sistema',
    'data_inicial' => '2026-02-12',
    'data_final' => '2026-02-12',
    'hora_inicial' => '08:00',
    'hora_final' => '17:00',
    'tipo_solicitacao' => 'programada',
    'equipamento' => 'Teste Equipamento',
    'descricao' => 'Teste de inserção no sistema',
    'colaboradores' => [],
    'responsavel_nome' => 'Teste Responsavel',
    'responsavel_funcao' => 'Teste Funcao',
    'empresa_responsavel' => 'PowerChina',
    'status' => 'pendente',
    'etapa_aprovacao' => 1
];

echo "<h1>Teste de Inserção</h1>";
echo "<pre>Dados a enviar: ";
print_r($dados_teste);
echo "</pre>";

$result = $supabase->request('POST', '/rest/v1/intervention_requests', $dados_teste);

echo "<pre>Resultado: ";
print_r($result);
echo "</pre>";

if ($result) {
    echo "<p style='color: green;'>✅ Inserção realizada com sucesso!</p>";
} else {
    echo "<p style='color: red;'>❌ Erro na inserção</p>";
}
?></content>
<parameter name="filePath">c:\dev\admin-dashboard\teste-insercao.php