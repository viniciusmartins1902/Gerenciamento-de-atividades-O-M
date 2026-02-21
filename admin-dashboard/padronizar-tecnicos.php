<?php
/**
 * Padronização de Nomes de Técnicos
 */

require_once 'auth.php';
require_once 'controle-acesso.php';
require_once 'supabase.php';

requerLogin();
verificarAcesso('padronizar-tecnicos');

$supabase = new Supabase();
$inspecoes = $supabase->getInspections();

// Coletar todos os técnicos
$tecnicos = [];
foreach ($inspecoes as $insp) {
    if (!empty($insp['tecnico1'])) {
        $tecnico = trim($insp['tecnico1']);
        if (!isset($tecnicos[$tecnico])) {
            $tecnicos[$tecnico] = ['count' => 0, 'tipo' => 'tecnico1', 'ids' => []];
        }
        $tecnicos[$tecnico]['count']++;
        $tecnicos[$tecnico]['ids'][] = $insp['id'];
    }
    if (!empty($insp['tecnico2'])) {
        $tecnico = trim($insp['tecnico2']);
        if (!isset($tecnicos[$tecnico])) {
            $tecnicos[$tecnico] = ['count' => 0, 'tipo' => 'tecnico2', 'ids' => []];
        }
        $tecnicos[$tecnico]['count']++;
        $tecnicos[$tecnico]['ids'][] = $insp['id'];
    }
}

// Agrupar nomes similares
function agruparSimilares($tecnicos) {
    $grupos = [];
    $processados = [];
    
    foreach ($tecnicos as $nome1 => $data1) {
        if (in_array($nome1, $processados)) continue;
        
        $grupo = [$nome1 => $data1];
        $processados[] = $nome1;
        
        foreach ($tecnicos as $nome2 => $data2) {
            if ($nome1 === $nome2 || in_array($nome2, $processados)) continue;
            
            // Verifica se são similares
            $nome1Lower = mb_strtolower($nome1);
            $nome2Lower = mb_strtolower($nome2);
            
            // Mesma base de nome (Carlos contém em Carlos Dantas)
            if (strpos($nome1Lower, $nome2Lower) !== false || 
                strpos($nome2Lower, $nome1Lower) !== false ||
                similar_text($nome1Lower, $nome2Lower) / max(strlen($nome1Lower), strlen($nome2Lower)) > 0.7) {
                $grupo[$nome2] = $data2;
                $processados[] = $nome2;
            }
        }
        
        if (count($grupo) > 1) {
            $grupos[] = $grupo;
        }
    }
    
    return $grupos;
}

$gruposSimilares = agruparSimilares($tecnicos);

// Processar padronização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['padronizar'])) {
    $updates = json_decode($_POST['updates'], true);
    $totalAtualizados = 0;
    
    foreach ($updates as $update) {
        $nomeAntigo = $update['old'];
        $nomeNovo = $update['new'];
        
        if ($nomeAntigo === $nomeNovo) continue;
        
        // Atualizar todas as inspeções
        foreach ($inspecoes as $insp) {
            $needUpdate = false;
            $data = [];
            
            if (trim($insp['tecnico1']) === $nomeAntigo) {
                $data['tecnico1'] = $nomeNovo;
                $needUpdate = true;
            }
            if (trim($insp['tecnico2']) === $nomeAntigo) {
                $data['tecnico2'] = $nomeNovo;
                $needUpdate = true;
            }
            
            if ($needUpdate) {
                $supabase->updateInspection($insp['id'], $data);
                $totalAtualizados++;
            }
        }
    }
    
    header('Location: padronizar-tecnicos.php?sucesso=' . $totalAtualizados);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padronizar Técnicos - PowerChina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-contrast.css">
    <style>
        .grupo-card {
            border-left: 4px solid #ffc107;
            background: #fff9e6;
        }
        .variacao-item {
            padding: 8px;
            margin: 5px 0;
            background: white;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .variacao-item.selected {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">👥 Padronizar Técnicos</h1>
                    <a href="inspecoes.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if (isset($_GET['sucesso'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        ✅ <?= $_GET['sucesso'] ?> registro(s) atualizado(s) com sucesso!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Como funciona:</strong> O sistema encontrou nomes similares que podem ser o mesmo técnico. 
                    Selecione o nome padrão para cada grupo e clique em "Padronizar" para atualizar o banco de dados.
                </div>

                <?php if (empty($gruposSimilares)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Nenhuma variação de nome encontrada! Todos os técnicos já estão padronizados.
                    </div>
                <?php else: ?>
                    <form method="POST" id="padronizarForm">
                        <input type="hidden" name="updates" id="updatesInput">
                        <input type="hidden" name="padronizar" value="1">

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check2-all"></i> Padronizar Todos os Grupos
                            </button>
                        </div>

                        <?php foreach ($gruposSimilares as $idx => $grupo): ?>
                            <div class="card grupo-card mb-4">
                                <div class="card-header bg-warning bg-opacity-10">
                                    <h5 class="mb-0">
                                        <i class="bi bi-people"></i> Grupo <?= $idx + 1 ?> 
                                        <span class="badge bg-warning text-dark"><?= count($grupo) ?> variações</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-arrow-down-circle"></i> Selecione qual nome deve ser o padrão:
                                    </p>
                                    
                                    <?php 
                                    // Ordenar por mais usado e maior comprimento
                                    uasort($grupo, function($a, $b) {
                                        if ($a['count'] !== $b['count']) {
                                            return $b['count'] - $a['count'];
                                        }
                                        return strlen($b) - strlen($a);
                                    });
                                    
                                    $first = true;
                                    foreach ($grupo as $nome => $data): 
                                    ?>
                                        <div class="variacao-item <?= $first ? 'selected' : '' ?>" 
                                             data-grupo="<?= $idx ?>" 
                                             onclick="selecionarPadrao(this, '<?= htmlspecialchars($nome, ENT_QUOTES) ?>', <?= $idx ?>)">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="grupo_<?= $idx ?>" 
                                                       value="<?= htmlspecialchars($nome) ?>"
                                                       <?= $first ? 'checked' : '' ?>>
                                                <label class="form-check-label w-100">
                                                    <strong><?= htmlspecialchars($nome) ?></strong>
                                                    <span class="badge bg-secondary ms-2"><?= $data['count'] ?> inspeções</span>
                                                    <?php if ($first): ?>
                                                        <span class="badge bg-success ms-2">
                                                            <i class="bi bi-star-fill"></i> Recomendado
                                                        </span>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php 
                                        $first = false;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check2-all"></i> Padronizar Todos os Grupos
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Todos os Técnicos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Inspeções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    arsort($tecnicos);
                                    foreach ($tecnicos as $nome => $data): 
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($nome) ?></td>
                                            <td><span class="badge bg-info"><?= $data['count'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const grupos = <?= json_encode($gruposSimilares) ?>;
        const gruposPadrao = {};

        // Inicializar com as recomendações
        grupos.forEach((grupo, idx) => {
            const nomes = Object.keys(grupo);
            // Ordenar igual ao PHP
            nomes.sort((a, b) => {
                if (grupo[a].count !== grupo[b].count) {
                    return grupo[b].count - grupo[a].count;
                }
                return b.length - a.length;
            });
            gruposPadrao[idx] = nomes[0];
        });

        function selecionarPadrao(element, nome, grupoIdx) {
            // Remover seleção anterior
            const variacoes = document.querySelectorAll(`[data-grupo="${grupoIdx}"]`);
            variacoes.forEach(v => v.classList.remove('selected'));
            
            // Adicionar seleção
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
            
            // Atualizar padrão
            gruposPadrao[grupoIdx] = nome;
        }

        document.getElementById('padronizarForm').addEventListener('submit', function(e) {
            const updates = [];
            
            grupos.forEach((grupo, idx) => {
                const nomePadrao = gruposPadrao[idx];
                Object.keys(grupo).forEach(nomeAntigo => {
                    updates.push({
                        old: nomeAntigo,
                        new: nomePadrao
                    });
                });
            });
            
            document.getElementById('updatesInput').value = JSON.stringify(updates);
            
            const totalAtualizacoes = updates.filter(u => u.old !== u.new).length;
            if (totalAtualizacoes === 0) {
                e.preventDefault();
                alert('Nenhuma alteração necessária!');
                return false;
            }
            
            return confirm(`Você está prestes a padronizar ${totalAtualizacoes} variação(ões) de nomes.\n\nDeseja continuar?`);
        });
    </script>
</body>
</html>
