<?php
// Função para listar solicitações em tabela
function doc_solicitacoes_lista($lista, $guia = '') {
    if (!isset($_SESSION)) session_start();
    $nivel = isset($_SESSION['nivel_acesso']) ? (int)$_SESSION['nivel_acesso'] : null;
    if (!$lista || count($lista) == 0) {
        echo '<div class="alert alert-info">Nenhuma solicitação encontrada.</div>';
        return;
    }
    echo '<div class="table-responsive"><table class="table table-bordered table-hover bg-white">';
    echo '<thead><tr><th>ID</th><th>Empresa</th><th>Solicitante</th><th>Data Inicial</th><th>Status</th><th>Ações</th></tr></thead><tbody>';
    foreach ($lista as $sol) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($sol['id']) . '</td>';
        echo '<td>' . htmlspecialchars($sol['empresa_id']) . '</td>';
        echo '<td>' . htmlspecialchars($sol['solicitante']) . '</td>';
        echo '<td>' . htmlspecialchars($sol['data_inicial']) . '</td>';
        echo '<td>' . htmlspecialchars($sol['status']) . '</td>';
        echo '<td>';
        // Botão analisar (abrir PDF)
        echo '<a href="solicitacao-intervencao-pdf.php?id=' . $sol['id'] . '" class="btn btn-sm btn-outline-primary me-1" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Analisar</a>';
        // Botão aprovar (dependendo da etapa)
        if (in_array($nivel, [1,2,4])) {
            $etapa_atual = (int)$sol['etapa_aprovacao'];
            $status = $sol['status'];
            if (($status === 'pendente' && in_array($nivel, [1,2])) ||
                ($status === 'aprovado_om' && $nivel == 4)) {
                echo '<form method="POST" action="workflow-aprovar-solicitacao.php?redirect=documentacao.php" style="display:inline;">
                    <input type="hidden" name="id" value="' . $sol['id'] . '">
                    <input type="hidden" name="etapa" value="' . $etapa_atual . '">
                    <button type="submit" name="acao" value="aprovar" class="btn btn-success btn-sm ms-1">Aprovar</button>
                </form>';
            }
        }
        // Botão finalizar (apenas aprovadas_seguranca ou em_execucao, só para nível 1,2)
        if ($guia === 'aprovadas' && in_array($nivel, [1,2]) && in_array($sol['status'], ['aprovado_seguranca', 'em_execucao'])) {
            echo '<form method="POST" action="workflow-aprovar-solicitacao.php?redirect=documentacao.php" style="display:inline;">
                <input type="hidden" name="id" value="' . $sol['id'] . '">
                <input type="hidden" name="etapa" value="3">
                <button type="submit" name="acao" value="aprovar" class="btn btn-dark btn-sm ms-1">Finalizar</button>
            </form>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
