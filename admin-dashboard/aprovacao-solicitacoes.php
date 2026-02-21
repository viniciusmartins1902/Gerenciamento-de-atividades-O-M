<?php
require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

// Apenas níveis internos (1, 2, 3, 4) podem aprovar
if (getNivelAcesso() > 4) {
    header('Location: dashboard.php');
    exit;
}

$supabase = new Supabase();
// Buscar todas as solicitações pendentes ou em aprovação (externas e internas)
$solicitacoes = $supabase->request('GET', '/rest/v1/intervention_requests', null, [
    'order' => 'criado_em.desc'
]);
// Buscar empresas para exibir nome
$empresas = $supabase->request('GET', '/rest/v1/access_keys');
$empresas_map = [];
foreach ($empresas as $e) {
    $empresas_map[$e['id']] = $e['nome_empresa'];
}

function statusLabel($status) {
    switch ($status) {
        case 'pendente': return '<span class="badge bg-warning text-dark">Pendente</span>';
        case 'aprovado_om': return '<span class="badge bg-primary">Aprovado O&M</span>';
        case 'aprovado_seguranca': return '<span class="badge bg-success">Aprovado Segurança</span>';
        case 'em_execucao': return '<span class="badge bg-info">Em Execução</span>';
        case 'finalizado': return '<span class="badge bg-dark">Finalizado</span>';
        case 'rejeitado': return '<span class="badge bg-danger">Rejeitado</span>';
        default: return $status;
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprovação de Solicitações de Intervenção</title>
    <link rel="icon" type="image/jpg" href="assets/images/images.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-contrast.css">
</head>
<body style="background: linear-gradient(135deg, #0f2027, #203a43, #8b0000); min-height: 100vh;">
    <?php include 'includes/navbar.php'; ?>
    <div class="container py-4">
        <h2 class="mb-4">Aprovação de Solicitações de Intervenção</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Empresa</th>
                        <th>Solicitante</th>
                        <th>Data Inicial</th>
                        <th>Status</th>
                        <th>Etapa</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($solicitacoes && count($solicitacoes) > 0): ?>
                    <?php foreach ($solicitacoes as $sol): ?>
                    <tr>
                        <td><?= htmlspecialchars($sol['id']) ?></td>
                        <td>
                            <?php
                            $tipo = $sol['tipo'] ?? 'externo';
                            $badge_class = $tipo === 'interno' ? 'bg-info' : 'bg-secondary';
                            $tipo_label = $tipo === 'interno' ? 'Interno' : 'Externo';
                            echo "<span class='badge $badge_class'>$tipo_label</span>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($empresas_map[$sol['empresa_id']] ?? 'PowerChina') ?></td>
                        <td><?= htmlspecialchars($sol['solicitante']) ?></td>
                        <td><?= htmlspecialchars($sol['data_inicial']) ?></td>
                        <td><?= statusLabel($sol['status']) ?></td>
                        <td><?= (int)$sol['etapa_aprovacao'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetalhes<?= $sol['id'] ?>" title="Detalhes"><i class="bi bi-eye"></i></button>
                            <a href="solicitacao-intervencao-pdf.php?id=<?= $sol['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Ver PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            <?php
                            $nivel = getNivelAcesso();
                            $tipo = $sol['tipo'] ?? 'externo';

                            // Lógica de aprovação baseada no tipo
                            if ($tipo === 'interno') {
                                // Solicitações internas: apenas segurança (nível 4) aprova
                                if ($sol['status'] === 'pendente' && $nivel == 4) : ?>
                                    <form method="POST" action="workflow-aprovar-solicitacao.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $sol['id'] ?>">
                                        <input type="hidden" name="etapa" value="1">
                                        <button type="submit" name="acao" value="aprovar" class="btn btn-success btn-sm" onclick="return confirm('Aprovar esta solicitação interna (Segurança)?');">Aprovar Segurança</button>
                                        <button type="submit" name="acao" value="rejeitar" class="btn btn-danger btn-sm" onclick="return confirm('Rejeitar esta solicitação?');">Rejeitar</button>
                                    </form>
                                <?php elseif (in_array($sol['status'], ['aprovado_seguranca']) && in_array($nivel, [1,2])) : ?>
                                    <form method="POST" action="workflow-aprovar-solicitacao.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $sol['id'] ?>">
                                        <input type="hidden" name="etapa" value="2">
                                        <button type="submit" name="acao" value="aprovar" class="btn btn-primary btn-sm" onclick="return confirm('Finalizar esta solicitação interna?');">Finalizar</button>
                                        <button type="submit" name="acao" value="rejeitar" class="btn btn-danger btn-sm" onclick="return confirm('Rejeitar esta solicitação?');">Rejeitar</button>
                                    </form>
                                <?php endif;
                            } else {
                                // Solicitações externas: fluxo normal
                                if ($sol['status'] === 'pendente' && in_array($nivel, [1,2])) : ?>
                                    <form method="POST" action="workflow-aprovar-solicitacao.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $sol['id'] ?>">
                                        <input type="hidden" name="etapa" value="1">
                                        <button type="submit" name="acao" value="aprovar" class="btn btn-success btn-sm" onclick="return confirm('Aprovar esta solicitação (O&M)?');">Aprovar O&M</button>
                                        <button type="submit" name="acao" value="rejeitar" class="btn btn-danger btn-sm" onclick="return confirm('Rejeitar esta solicitação?');">Rejeitar</button>
                                    </form>
                                <?php elseif ($sol['status'] === 'aprovado_om' && $nivel == 4) : ?>
                                    <form method="POST" action="workflow-aprovar-solicitacao.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $sol['id'] ?>">
                                        <input type="hidden" name="etapa" value="2">
                                        <button type="submit" name="acao" value="aprovar" class="btn btn-success btn-sm" onclick="return confirm('Aprovar esta solicitação (Segurança)?');">Aprovar Segurança</button>
                                        <button type="submit" name="acao" value="rejeitar" class="btn btn-danger btn-sm" onclick="return confirm('Rejeitar esta solicitação?');">Rejeitar</button>
                                    </form>
                                <?php elseif (in_array($sol['status'], ['aprovado_seguranca', 'em_execucao']) && in_array($nivel, [1,2])) : ?>
                                    <form method="POST" action="workflow-aprovar-solicitacao.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $sol['id'] ?>">
                                        <input type="hidden" name="etapa" value="3">
                                        <button type="submit" name="acao" value="aprovar" class="btn btn-primary btn-sm" onclick="return confirm('Finalizar esta solicitação?');">Finalizar</button>
                                        <button type="submit" name="acao" value="rejeitar" class="btn btn-danger btn-sm" onclick="return confirm('Rejeitar esta solicitação?');">Rejeitar</button>
                                    </form>
                                <?php endif;
                            }
                            ?>
                            <?php if (!in_array($nivel, [1,2,4])) echo '<span class="text-muted">Apenas usuários dos níveis 1, 2 ou 4 podem aprovar.</span>'; ?>
                        </td>
                    </tr>
                    <!-- Modal Detalhes -->
                    <div class="modal fade" id="modalDetalhes<?= $sol['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detalhes da Solicitação #<?= $sol['id'] ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <dl class="row">
                                        <dt class="col-sm-3">Empresa</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($empresas_map[$sol['empresa_id']] ?? '-') ?></dd>
                                        <dt class="col-sm-3">Solicitante</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['solicitante']) ?></dd>
                                        <dt class="col-sm-3">Substituto</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['substituto']) ?></dd>
                                        <dt class="col-sm-3">Receptor</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['receptor']) ?></dd>
                                        <dt class="col-sm-3">Data Inicial</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['data_inicial']) ?></dd>
                                        <dt class="col-sm-3">Data Final</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['data_final']) ?></dd>
                                        <dt class="col-sm-3">Hora Inicial</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['hora_inicial']) ?></dd>
                                        <dt class="col-sm-3">Hora Final</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['hora_final']) ?></dd>
                                        <dt class="col-sm-3">Tipo de Solicitação</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['tipo_solicitacao']) ?></dd>
                                        <dt class="col-sm-3">Equipamento</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['equipamento']) ?></dd>
                                        <dt class="col-sm-3">Descrição</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['descricao']) ?></dd>
                                        <dt class="col-sm-3">Responsável Brasileiro</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['responsavel_nome']) ?></dd>
                                        <dt class="col-sm-3">Função Responsável</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['responsavel_funcao']) ?></dd>
                                        <dt class="col-sm-3">Empresa Responsável</dt>
                                        <dd class="col-sm-9"><?= htmlspecialchars($sol['empresa_responsavel']) ?></dd>
                                    </dl>
                                    <h6>Colaboradores</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead><tr><th>#</th><th>Nome</th><th>Função</th><th>Origem</th></tr></thead>
                                            <tbody>
                                            <?php $colabs = json_decode($sol['colaboradores'], true) ?: []; $n=1; foreach ($colabs as $c): ?>
                                                <tr>
                                                    <td><?= $n++ ?></td>
                                                    <td><?= htmlspecialchars($c['nome'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($c['funcao'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($c['origem'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">Nenhuma solicitação encontrada.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="dashboard.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Voltar ao Dashboard</a>
    </div>
</body>
</html>
