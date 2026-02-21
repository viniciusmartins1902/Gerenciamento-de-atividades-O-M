<?php
/**
 * Lista de Inspeções com Filtros
 */

require_once 'auth.php';
require_once 'controle-acesso.php';
require_once 'supabase.php';

requerLogin();

$supabase = new Supabase();

// Filtros
$filtros = [
    'campo' => $_GET['campo'] ?? '',
    'tecnico' => $_GET['tecnico'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_final' => $_GET['data_final'] ?? ''
];

$inspecoes = $supabase->getInspections($filtros);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspeções - PowerChina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-contrast.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">📋 Inspeções</h1>
                    <div>
                        <span class="badge bg-primary"><?= count($inspecoes) ?> registro(s)</span>
                    </div>
                </div>

                <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'excluido'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        ✅ Inspeção(ões) excluída(s) com sucesso!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['erro'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ❌ 
                        <?php 
                        switch($_GET['erro']) {
                            case 'id_invalido':
                                echo 'ID inválido.';
                                break;
                            case 'nao_encontrado':
                                echo 'Inspeção não encontrada.';
                                break;
                            default:
                                echo 'Erro ao processar solicitação.';
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Campo</label>
                                <input type="text" name="campo" class="form-control" value="<?= htmlspecialchars($filtros['campo']) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Técnico</label>
                                <input type="text" name="tecnico" class="form-control" value="<?= htmlspecialchars($filtros['tecnico']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Data Início</label>
                                <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Data Final</label>
                                <input type="date" name="data_final" class="form-control" value="<?= htmlspecialchars($filtros['data_final']) ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Botão de Exclusão em Lote -->
                <?php if (!empty($inspecoes)): ?>
                <div class="mb-3 d-flex gap-2">
                    <button id="deleteSelectedBtn" class="btn btn-danger" disabled>
                        <i class="bi bi-trash"></i> Excluir Selecionados (0)
                    </button>
                    <?php if (getNivelAcesso() == 1): ?>
                    <button id="findDuplicatesBtn" class="btn btn-warning">
                        <i class="bi bi-search"></i> Encontrar Duplicatas
                    </button>
                    <button id="selectDuplicatesBtn" class="btn btn-outline-warning" style="display:none;">
                        <i class="bi bi-check2-square"></i> Selecionar Duplicatas (<span id="duplicateCount">0</span>)
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Tabela -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <?php if (!empty($inspecoes)): ?>
                                <th width="30">
                                    <input type="checkbox" id="selectAll" class="form-check-input" title="Selecionar todos">
                                </th>
                                <?php endif; ?>
                                <th>ID</th>
                                <th>Campo</th>
                                <th>Subcampo</th>
                                <th>Inversor</th>
                                <th>Técnico 1</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inspecoes)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2">Nenhuma inspeção encontrada</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inspecoes as $insp): ?>
                                    <tr data-campo="<?= htmlspecialchars($insp['campo']) ?>" 
                                        data-subcampo="<?= htmlspecialchars($insp['subcampo']) ?>" 
                                        data-inversor="<?= htmlspecialchars($insp['inversor']) ?>" 
                                        data-data="<?= date('Y-m-d', strtotime($insp['data_criacao'])) ?>"
                                        data-id="<?= $insp['id'] ?>">
                                        <td>
                                            <input type="checkbox" class="form-check-input inspection-checkbox" value="<?= $insp['id'] ?>">
                                        </td>
                                        <td><?= $insp['id'] ?></td>
                                        <td><?= htmlspecialchars($insp['campo']) ?></td>
                                        <td><?= htmlspecialchars($insp['subcampo']) ?></td>
                                        <td><?= htmlspecialchars($insp['inversor']) ?></td>
                                        <td><?= htmlspecialchars($insp['tecnico1']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($insp['data_criacao'])) ?></td>
                                        <td>
                                            <a href="detalhes.php?id=<?= $insp['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (!empty($inspecoes)): ?>
    <style>
        .duplicate-row {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107;
        }
        .duplicate-badge {
            background-color: #ffc107;
            color: #000;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75rem;
            margin-left: 5px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.inspection-checkbox');
            const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
            const findDuplicatesBtn = document.getElementById('findDuplicatesBtn');
            const selectDuplicatesBtn = document.getElementById('selectDuplicatesBtn');
            let duplicateIds = [];
            
            // Selecionar/desselecionar todos
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateDeleteButton();
                });
            }
            
            // Atualizar botão ao selecionar individuais
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateDeleteButton();
                    // Atualizar checkbox "selecionar todos"
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                    if (selectAll) {
                        selectAll.checked = allChecked;
                        selectAll.indeterminate = someChecked && !allChecked;
                    }
                });
            });
            
            // Excluir selecionados
            if (deleteSelectedBtn) {
                deleteSelectedBtn.addEventListener('click', function() {
                    const selected = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    
                    if (selected.length === 0) return;
                    
                    const mensagem = selected.length === 1 
                        ? `⚠️ Deseja realmente excluir esta inspeção?\n\nEsta ação não pode ser desfeita!`
                        : `⚠️ Deseja realmente excluir ${selected.length} inspeções?\n\nEsta ação não pode ser desfeita!`;
                    
                    if (confirm(mensagem)) {
                        deleteInspections(selected);
                    }
                });
            }
            
            // Encontrar duplicatas
            if (findDuplicatesBtn) {
                findDuplicatesBtn.addEventListener('click', function() {
                    findDuplicates();
                });
            }

            // Selecionar duplicatas
            if (selectDuplicatesBtn) {
                selectDuplicatesBtn.addEventListener('click', function() {
                    selectDuplicates();
                });
            }

            function findDuplicates() {
                // Limpar marcações anteriores
                document.querySelectorAll('.duplicate-row').forEach(row => {
                    row.classList.remove('duplicate-row');
                });
                document.querySelectorAll('.duplicate-badge').forEach(badge => {
                    badge.remove();
                });

                const rows = document.querySelectorAll('tbody tr[data-campo]');
                const duplicateMap = {};
                duplicateIds = [];

                // Agrupar por chave (campo + subcampo + inversor + data)
                rows.forEach(row => {
                    const key = `${row.dataset.campo}|${row.dataset.subcampo}|${row.dataset.inversor}|${row.dataset.data}`;
                    if (!duplicateMap[key]) {
                        duplicateMap[key] = [];
                    }
                    duplicateMap[key].push({
                        row: row,
                        id: row.dataset.id
                    });
                });

                // Identificar duplicatas (grupos com mais de 1 item)
                let duplicateCount = 0;
                Object.values(duplicateMap).forEach(group => {
                    if (group.length > 1) {
                        // Ordenar por ID (mais antigo primeiro)
                        group.sort((a, b) => parseInt(a.id) - parseInt(b.id));
                        
                        // Marcar todos exceto o primeiro (mais antigo) como duplicata
                        for (let i = 1; i < group.length; i++) {
                            group[i].row.classList.add('duplicate-row');
                            duplicateIds.push(group[i].id);
                            duplicateCount++;
                            
                            // Adicionar badge
                            const firstCell = group[i].row.querySelector('td:nth-child(2)');
                            const badge = document.createElement('span');
                            badge.className = 'duplicate-badge';
                            badge.textContent = 'DUPLICADO';
                            firstCell.appendChild(badge);
                        }
                    }
                });

                // Mostrar resultado
                if (duplicateCount > 0) {
                    document.getElementById('duplicateCount').textContent = duplicateCount;
                    selectDuplicatesBtn.style.display = 'inline-block';
                    alert(`✅ Encontradas ${duplicateCount} inspeção(ões) duplicada(s).\n\nAs duplicatas estão destacadas em amarelo.\n\nClique em "Selecionar Duplicatas" para marcá-las.`);
                } else {
                    selectDuplicatesBtn.style.display = 'none';
                    alert('✅ Nenhuma duplicata encontrada!');
                }
            }

            function selectDuplicates() {
                // Desmarcar todos primeiro
                checkboxes.forEach(cb => cb.checked = false);
                
                // Marcar apenas as duplicatas
                duplicateIds.forEach(id => {
                    const checkbox = document.querySelector(`.inspection-checkbox[value="${id}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
                
                updateDeleteButton();
                if (selectAll) {
                    selectAll.checked = false;
                }
            }

            function updateDeleteButton() {
                const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                if (deleteSelectedBtn) {
                    deleteSelectedBtn.disabled = selectedCount === 0;
                    const texto = selectedCount === 1 ? 'Excluir Selecionado (1)' : `Excluir Selecionados (${selectedCount})`;
                    deleteSelectedBtn.innerHTML = `<i class="bi bi-trash"></i> ${texto}`;
                }
            }
            
            function deleteInspections(ids) {
                // Desabilitar botão durante processamento
                deleteSelectedBtn.disabled = true;
                deleteSelectedBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Excluindo...';
                
                fetch('delete_inspections.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'inspecoes.php?sucesso=excluido';
                    } else {
                        alert('❌ Erro ao excluir: ' + (data.message || 'Erro desconhecido'));
                        deleteSelectedBtn.disabled = false;
                        updateDeleteButton();
                    }
                })
                .catch(error => {
                    alert('❌ Erro na requisição: ' + error);
                    deleteSelectedBtn.disabled = false;
                    updateDeleteButton();
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
