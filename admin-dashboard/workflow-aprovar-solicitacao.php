<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

// Apenas níveis 1, 2 e 4 podem aprovar/finalizar
$nivel = getNivelAcesso();
if (!in_array($nivel, [1,2,4])) {
    header('Location: dashboard.php');
    exit;
}

$supabase = new Supabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['acao'], $_POST['etapa'])) {
    $id = intval($_POST['id']);
    $acao = $_POST['acao'];
    $etapa = intval($_POST['etapa']);

    // Buscar tipo da solicitação
    $solicitacao = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
        'id' => 'eq.' . $id
    ]);
    $tipo = $solicitacao[0]['tipo'] ?? 'externo';

    $dados = [];
    if ($acao === 'aprovar') {
        if ($tipo === 'interno') {
            // Solicitações internas: pula O&M, vai direto para segurança
            if ($etapa === 1 && $nivel === 4) {
                $dados = ['status' => 'aprovado_seguranca', 'etapa_aprovacao' => 2];
            } elseif ($etapa === 2 && in_array($nivel, [1,2])) {
                $dados = ['status' => 'finalizado', 'etapa_aprovacao' => 3];
            }
        } else {
            // Solicitações externas: fluxo normal
            if ($etapa === 1 && in_array($nivel, [1,2])) {
                $dados = ['status' => 'aprovado_om', 'etapa_aprovacao' => 2];
            } elseif ($etapa === 2 && $nivel === 4) {
                $dados = ['status' => 'aprovado_seguranca', 'etapa_aprovacao' => 3];
            } elseif ($etapa === 3 && in_array($nivel, [1,2])) {
                $dados = ['status' => 'finalizado', 'etapa_aprovacao' => 4];
            }
        }
    } elseif ($acao === 'rejeitar') {
        $dados = ['status' => 'rejeitado'];
    }
    if ($dados) {
        $supabase->request('PATCH', '/rest/v1/intervention_requests?id=eq.' . $id, $dados);
    }
}

// Redirecionamento baseado na origem
$redirect = $_GET['redirect'] ?? 'aprovacao-solicitacoes.php';
header('Location: ' . $redirect);
exit;
