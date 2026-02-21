<?php
require_once 'auth.php';
require_once 'supabase.php';

// Verifica se é acesso externo
if (!isset($_SESSION['tipo_acesso']) || $_SESSION['tipo_acesso'] !== 'externo') {
    header('Location: acesso-externo.php');
    exit;
}

$supabase = new Supabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Verificar se a solicitação pertence à empresa logada e está pendente
    $empresa_id = $_SESSION['access_key_id'];
    $solicitacao = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
        'id' => 'eq.' . $id,
        'empresa_id' => 'eq.' . $empresa_id,
        'status' => 'eq.pendente'
    ]);

    if ($solicitacao && count($solicitacao) > 0) {
        // Só permite excluir se estiver pendente
        $result = $supabase->request('DELETE', '/rest/v1/intervention_requests?id=eq.' . $id);

        if ($result === '' || $result === null) {
            // Redirecionar com sucesso
            header('Location: minhas-solicitacoes.php?msg=excluida');
            exit;
        }
    }
}

// Se chegou aqui, algo deu errado
header('Location: minhas-solicitacoes.php?erro=exclusao_falhou');
exit;
?>