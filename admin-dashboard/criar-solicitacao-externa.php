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
if (isset($_GET['ok'])) {
    $mensagem = 'Solicitação criada com sucesso!';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Solicitação de Intervenção - Externo</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Nova Solicitação de Intervenção<br><small class="text-muted"><?php echo htmlspecialchars($empresa_nome); ?></small></h2>
        <?php if ($mensagem): ?>
            <div class="alert alert-success"> <?= $mensagem ?> </div>
        <?php endif; ?>
        <form method="POST" action="salvar-solicitacao.php">
            <input type="hidden" name="empresa_id" value="<?php echo $empresa_id; ?>">
            <div class="row mb-2">
                <div class="col-md-4">
                    <label class="form-label">Solicitante</label>
                    <input type="text" class="form-control" name="solicitante" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Solicitante substituto</label>
                    <input type="text" class="form-control" name="substituto">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Receptor</label>
                    <input type="text" class="form-control" name="receptor" value="Mesa de operações" readonly>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <label class="form-label">Data inicial</label>
                    <input type="date" class="form-control" name="data1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data final</label>
                    <input type="date" class="form-control" name="data2">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hora de início</label>
                    <input type="time" class="form-control" name="time1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hora final</label>
                    <input type="time" class="form-control" name="time2">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <label class="form-label">Tipo de solicitação</label><br>
                    <input class="form-check-input" type="radio" name="tipo" value="emergencial"> Emergencial
                    <input class="form-check-input ms-3" type="radio" name="tipo2" value="programada"> Programada
                </div>
                <div class="col-md-6">
                    <label class="form-label">Equipamento</label>
                    <input type="text" class="form-control" name="equipamento">
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Descrição de trabalho</label>
                <input type="text" class="form-control" name="descricao">
            </div>
            <div class="row mb-2">
                <div class="col-md-4">
                    <label class="form-label">Responsável brasileiro pela atividade</label>
                    <input type="text" class="form-control" name="resp">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Função</label>
                    <input type="text" class="form-control" name="funresp">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Empresa responsável</label>
                    <input type="text" class="form-control" name="emp">
                </div>
            </div>
            <!-- Colaboradores dinâmicos (pode ser implementado com JS depois) -->
            <div class="mb-3">
                <label class="form-label">Colaboradores/Participantes</label>
                <div id="colaboradores-list"></div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="adicionarColaborador()">Adicionar Participante</button>
            </div>
            <button type="submit" class="btn btn-success">Enviar Solicitação</button>
        </form>
    </div>
<script>
function adicionarColaborador() {
    const idx = document.querySelectorAll('.colaborador-item').length;
    const html = `
    <div class="row colaborador-item mb-2 align-items-end" data-idx="${idx}">
        <div class="col-md-4">
            <input type="text" class="form-control" name="colaboradores[${idx}][nome]" placeholder="Nome" required>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" name="colaboradores[${idx}][funcao]" placeholder="Função" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="colaboradores[${idx}][origem]" placeholder="Origem" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm" onclick="removerColaborador(this)"><i class="bi bi-x"></i></button>
        </div>
    </div>`;
    document.getElementById('colaboradores-list').insertAdjacentHTML('beforeend', html);
}
function removerColaborador(btn) {
    btn.closest('.colaborador-item').remove();
}
</script>
</body>
</html>
