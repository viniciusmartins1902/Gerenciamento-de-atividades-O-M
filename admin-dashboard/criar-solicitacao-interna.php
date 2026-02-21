<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();
$usuario_id = $_SESSION['user_id'] ?? null;
$usuario_nome = $_SESSION['nome'] ?? 'Usuário';

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
    <title>Nova Solicitação Interna de Intervenção</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-contrast.css">
</head>
<body style="background: linear-gradient(135deg, #0f2027, #203a43, #8b0000); min-height: 100vh;">
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Nova Solicitação Interna de Intervenção</h1>
                    <a href="documentos-internos.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-success"> <?= $mensagem ?> </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="salvar-solicitacao-interna.php">
                            <input type="hidden" name="usuario_interno_id" value="<?php echo $usuario_id; ?>">
                            <input type="hidden" name="tipo" value="interno">
                            <input type="hidden" name="usuario_interno_id" value="<?php echo $usuario_id; ?>">
                            <input type="hidden" name="tipo" value="interno">

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Solicitante</label>
                                    <input type="text" class="form-control" name="solicitante" value="<?php echo htmlspecialchars($usuario_nome); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Solicitante substituto</label>
                                    <input type="text" class="form-control" name="substituto">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Receptor</label>
                                    <input type="text" class="form-control" name="receptor" value="Mesa de operações" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Data Inicial</label>
                                    <input type="date" class="form-control" name="data_inicial" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Data Final</label>
                                    <input type="date" class="form-control" name="data_final" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hora Inicial</label>
                                    <input type="time" class="form-control" name="hora_inicial" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hora Final</label>
                                    <input type="time" class="form-control" name="hora_final" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Solicitação</label>
                                    <select class="form-control" name="tipo_solicitacao" required>
                                        <option value="emergencial">Emergencial</option>
                                        <option value="programada" selected>Programada</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Equipamento</label>
                                    <input type="text" class="form-control" name="equipamento" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descrição da Intervenção</label>
                                <textarea class="form-control" name="descricao" rows="3" required></textarea>
                            </div>

                            <!-- Colaboradores dinâmicos -->
                            <div class="mb-3">
                                <label class="form-label">Colaboradores/Participantes</label>
                                <div id="colaboradores-list"></div>
                                <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="adicionarColaborador()">Adicionar Participante</button>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Responsável Nome</label>
                                    <input type="text" class="form-control" name="responsavel_nome" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Responsável Função</label>
                                    <input type="text" class="form-control" name="responsavel_funcao" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Empresa Responsável</label>
                                    <input type="text" class="form-control" name="empresa_responsavel" value="PowerChina" required>
                                </div>
                            </div>

                            <input type="hidden" name="colaboradores" id="colaboradores-hidden" value="[]">

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Enviar Solicitação
                                </button>
                                <a href="documentos-internos.php" class="btn btn-secondary">
                                    <i class="bi bi-x"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function adicionarColaborador() {
            const idx = document.querySelectorAll('.colaborador-item').length;
            const html = `
                <div class="row colaborador-item mb-2 align-items-end" data-idx="${idx}">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="colaboradores[${idx}][nome]" placeholder="Nome" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="colaboradores[${idx}][funcao]" placeholder="Função" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="colaboradores[${idx}][origem]" placeholder="Origem" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removerColaborador(this)"><i class="bi bi-x"></i></button>
                    </div>
                </div>
            `;
            document.getElementById('colaboradores-list').insertAdjacentHTML('beforeend', html);
        }

        function removerColaborador(btn) {
            btn.closest('.colaborador-item').remove();
        }

        // Atualizar campo hidden antes do submit
        document.querySelector('form').addEventListener('submit', function() {
            const colaboradores = [];
            document.querySelectorAll('.colaborador-item').forEach((item, index) => {
                const nome = item.querySelector('input[name*="nome"]').value;
                const funcao = item.querySelector('input[name*="funcao"]').value;
                const origem = item.querySelector('input[name*="origem"]').value;

                if (nome && funcao && origem) {
                    colaboradores.push({
                        nome: nome,
                        funcao: funcao,
                        origem: origem
                    });
                }
            });

            document.getElementById('colaboradores-hidden').value = JSON.stringify(colaboradores);
        });
    </script>
</body>
</html>