<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: mostrar dados recebidos
    error_log("POST data: " . print_r($_POST, true));
    error_log("SESSION data: " . print_r($_SESSION, true));

    // Processar colaboradores do formulário
    $colaboradores = [];

    // Verificar se colaboradores vêm como JSON (campo hidden)
    if (isset($_POST['colaboradores']) && is_string($_POST['colaboradores'])) {
        $colaboradores = json_decode($_POST['colaboradores'], true) ?: [];
    }
    // Ou como array (campos do formulário)
    elseif (isset($_POST['colaboradores']) && is_array($_POST['colaboradores'])) {
        foreach ($_POST['colaboradores'] as $colab) {
            if (!empty($colab['nome']) && !empty($colab['funcao']) && !empty($colab['origem'])) {
                $colaboradores[] = [
                    'nome' => trim($colab['nome']),
                    'funcao' => trim($colab['funcao']),
                    'origem' => trim($colab['origem'])
                ];
            }
        }
    }

    $dados = [
        'tipo' => 'interno',
        'usuario_interno_id' => intval($_POST['usuario_interno_id'] ?? 0),
        'empresa_id' => null,
        'solicitante' => trim($_POST['solicitante'] ?? ''),
        'substituto' => trim($_POST['substituto'] ?? ''),
        'receptor' => trim($_POST['receptor'] ?? ''),
        'data_inicial' => $_POST['data_inicial'] ?? '',
        'data_final' => $_POST['data_final'] ?? '',
        'hora_inicial' => $_POST['hora_inicial'] ?? '',
        'hora_final' => $_POST['hora_final'] ?? '',
        'tipo_solicitacao' => $_POST['tipo_solicitacao'] ?? '',
        'equipamento' => trim($_POST['equipamento'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'colaboradores' => $colaboradores,
        'responsavel_nome' => trim($_POST['responsavel_nome'] ?? ''),
        'responsavel_funcao' => trim($_POST['responsavel_funcao'] ?? ''),
        'empresa_responsavel' => trim($_POST['empresa_responsavel'] ?? ''),
        'status' => 'pendente',
        'etapa_aprovacao' => 1
    ];

    error_log("Dados a serem enviados: " . print_r($dados, true));

    $result = $supabase->request('POST', '/rest/v1/intervention_requests', $dados);

    error_log("Resultado da API: " . print_r($result, true));

    if ($result) {
        header('Location: criar-solicitacao-interna.php?ok=1');
        exit;
    } else {
        // Debug: tentar buscar tabelas existentes
        $tables = $supabase->request('GET', '/rest/v1/');
        error_log("Tabelas disponíveis: " . print_r($tables, true));

        // Tentar buscar usuários
        $users = $supabase->request('GET', '/rest/v1/users');
        error_log("Usuários encontrados: " . print_r($users, true));

        error_log("Erro ao salvar solicitação interna");
        header('Location: criar-solicitacao-interna.php?erro=1');
        exit;
    }
}

header('Location: documentos-internos.php');
exit;
?>