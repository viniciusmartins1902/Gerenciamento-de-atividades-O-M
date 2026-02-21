<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

// Verifica se é acesso externo
if (!isset($_SESSION['tipo_acesso']) || $_SESSION['tipo_acesso'] !== 'externo') {
    header('Location: dashboard.php');
    exit;
}

$supabase = new Supabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Verificar se a solicitação pertence à empresa logada
    $empresa_id = $_SESSION['access_key_id'];
    $solicitacao = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
        'id' => 'eq.' . $id,
        'empresa_id' => 'eq.' . $empresa_id
    ]);

    if ($solicitacao && count($solicitacao) > 0) {
        $status_atual = $solicitacao[0]['status'];

        // Só permite marcar como em execução se estiver aprovado pela Segurança
        if ($status_atual === 'aprovado_seguranca') {
            $result = $supabase->request('PATCH', '/rest/v1/intervention_requests?id=eq.' . $id, [
                'status' => 'em_execucao'
            ]);

            if ($result) {
                // Redirecionar de volta com sucesso
                header('Location: minhas-solicitacoes.php?msg=execucao_marcada');
                exit;
            }
        }
    }
}

// Se chegou aqui, algo deu errado
header('Location: minhas-solicitacoes.php?erro=execucao_falhou');
exit;
?>