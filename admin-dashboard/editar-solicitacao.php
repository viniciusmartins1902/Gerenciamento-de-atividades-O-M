<?php
require_once 'supabase.php';
require_once 'auth.php';

// Valida se é externo (por access_key, não sessão interna)
if (!isset($_SESSION['access_key_id'])) {
    header('Location: acesso-externo.php');
    exit;
}

$supabase = new Supabase();
$empresa_id = $_SESSION['access_key_id'];
$empresa_nome = $_SESSION['nome'];

$mensagem = '';
$erro = '';

// Buscar solicitação para editar
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: minhas-solicitacoes.php');
    exit;
}

$solicitacao = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
    'id' => 'eq.' . $id,
    'empresa_id' => 'eq.' . $empresa_id
]);

if (!$solicitacao || count($solicitacao) == 0) {
    header('Location: minhas-solicitacoes.php');
    exit;
}

$sol = $solicitacao[0];

// Verificar se pode editar (só pendente)
if ($sol['status'] !== 'pendente') {
    $erro = 'Esta solicitação não pode mais ser editada pois já foi aprovada.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$erro) {
    // Processar edição
    $dados = [
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
        'colaboradores' => json_decode($_POST['colaboradores'] ?? '[]', true),
        'responsavel_nome' => trim($_POST['responsavel_nome'] ?? ''),
        'responsavel_funcao' => trim($_POST['responsavel_funcao'] ?? ''),
        'empresa_responsavel' => trim($_POST['empresa_responsavel'] ?? '')
    ];

    $result = $supabase->request('PATCH', '/rest/v1/intervention_requests?id=eq.' . $id, $dados);

    if ($result) {
        header('Location: minhas-solicitacoes.php?msg=editada');
        exit;
    } else {
        $erro = 'Erro ao atualizar solicitação.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Solicitação de Intervenção - Externo</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Editar Solicitação de Intervenção<br><small class="text-muted"><?php echo htmlspecialchars($empresa_nome); ?></small></h2>

        <?php if ($erro): ?>
            <div class="alert alert-danger"> <?= $erro ?> </div>
        <?php endif; ?>

        <?php if ($sol['status'] === 'pendente'): ?>
        <form method="POST" action="">
            <input type="hidden" name="empresa_id" value="<?php echo $empresa_id; ?>">
            <div class="row mb-2">
                <div class="col-md-4">
                    <label class="form-label">Solicitante</label>
                    <input type="text" class="form-control" name="solicitante" value="<?php echo htmlspecialchars($sol['solicitante'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Solicitante substituto</label>
                    <input type="text" class="form-control" name="substituto" value="<?php echo htmlspecialchars($sol['substituto'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Receptor</label>
                    <input type="text" class="form-control" name="receptor" value="<?php echo htmlspecialchars($sol['receptor'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-3">
                    <label class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" name="data_inicial" value="<?php echo htmlspecialchars($sol['data_inicial'] ?? ''); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Final</label>
                    <input type="date" class="form-control" name="data_final" value="<?php echo htmlspecialchars($sol['data_final'] ?? ''); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hora Inicial</label>
                    <input type="time" class="form-control" name="hora_inicial" value="<?php echo htmlspecialchars($sol['hora_inicial'] ?? ''); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hora Final</label>
                    <input type="time" class="form-control" name="hora_final" value="<?php echo htmlspecialchars($sol['hora_final'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Solicitação</label>
                    <select class="form-control" name="tipo_solicitacao" required>
                        <option value="emergencial" <?php echo ($sol['tipo_solicitacao'] ?? '') === 'emergencial' ? 'selected' : ''; ?>>Emergencial</option>
                        <option value="programada" <?php echo ($sol['tipo_solicitacao'] ?? '') === 'programada' ? 'selected' : ''; ?>>Programada</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Equipamento</label>
                    <input type="text" class="form-control" name="equipamento" value="<?php echo htmlspecialchars($sol['equipamento'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label">Descrição da Intervenção</label>
                <textarea class="form-control" name="descricao" rows="3" required><?php echo htmlspecialchars($sol['descricao'] ?? ''); ?></textarea>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label class="form-label">Responsável Nome</label>
                    <input type="text" class="form-control" name="responsavel_nome" value="<?php echo htmlspecialchars($sol['responsavel_nome'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Responsável Função</label>
                    <input type="text" class="form-control" name="responsavel_funcao" value="<?php echo htmlspecialchars($sol['responsavel_funcao'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Empresa Responsável</label>
                    <input type="text" class="form-control" name="empresa_responsavel" value="<?php echo htmlspecialchars($sol['empresa_responsavel'] ?? ''); ?>" required>
                </div>
            </div>

            <input type="hidden" name="colaboradores" value='<?php echo json_encode($sol['colaboradores'] ?? []); ?>'>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="minhas-solicitacoes.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
        <?php endif; ?>

        <div class="mt-3">
            <a href="minhas-solicitacoes.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar às Minhas Solicitações</a>
        </div>
    </div>
</body>
</html>