<?php

require_once 'supabase.php';
require_once 'auth.php';

// Só permite criação se for externo (autenticado por access_key)
if (!isset($_SESSION['access_key_id'])) {
    echo '<script>alert("Apenas empresas externas podem criar solicitações.");window.location.href="acesso-externo.php";</script>';
    exit;
}

$supabase = new Supabase();

// Recebe dados do formulário
$empresa_id = intval($_POST['empresa_id'] ?? 0);
$solicitante = $_POST['solicitante'] ?? '';
$substituto = $_POST['substituto'] ?? '';
$receptor = $_POST['receptor'] ?? '';
$data_inicial = $_POST['data1'] ?? null;
$data_final = $_POST['data2'] ?? null;
$hora_inicial = $_POST['time1'] ?? null;
$hora_final = $_POST['time2'] ?? null;
$tipo = isset($_POST['tipo']) ? 'emergencial' : (isset($_POST['tipo2']) ? 'programada' : '');
$equipamento = $_POST['equipamento'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$colaboradores = $_POST['colaboradores'] ?? [];
$responsavel_nome = $_POST['resp'] ?? '';
$responsavel_funcao = $_POST['funresp'] ?? '';
$empresa_responsavel = $_POST['emp'] ?? '';

// Monta array para salvar
$dados = [
    'empresa_id' => $empresa_id,
    'solicitante' => $solicitante,
    'substituto' => $substituto,
    'receptor' => $receptor,
    'data_inicial' => $data_inicial,
    'data_final' => $data_final,
    'hora_inicial' => $hora_inicial,
    'hora_final' => $hora_final,
    'tipo_solicitacao' => $tipo,
    'equipamento' => $equipamento,
    'descricao' => $descricao,
    'colaboradores' => array_values($colaboradores), // array puro para JSONB
    'responsavel_nome' => $responsavel_nome,
    'responsavel_funcao' => $responsavel_funcao,
    'empresa_responsavel' => $empresa_responsavel,
    'status' => 'pendente',
    'etapa_aprovacao' => 1
];

// Salva no banco
$result = $supabase->request('POST', '/rest/v1/intervention_requests', $dados);

if ($result && isset($result[0]['id'])) {
    // Apenas exibe mensagem de sucesso e link para Minhas Solicitações
    echo '<script>alert("Solicitação enviada com sucesso! Você pode acompanhar o status em Minhas Solicitações.");window.location.href="minhas-solicitacoes.php";</script>';
    exit;
} else {
    echo '<script>alert("Erro ao salvar solicitação. Tente novamente.");history.back();</script>';
    exit;
}
