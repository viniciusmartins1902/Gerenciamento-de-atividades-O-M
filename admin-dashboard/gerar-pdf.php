<?php
/**
 * Gerador de PDF para Inspeção
 * Usa jsPDF via HTML para gerar o mesmo relatório do app
 */

require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID de inspeção não fornecido');
}

$supabase = new Supabase();
$inspecao = $supabase->getInspection($id);
$fotos = $supabase->getPhotos($id) ?? [];

if (!$inspecao) {
    die('Inspeção não encontrada');
}

// Decodificar JSONs
$inspecao['inspecao_visual'] = json_decode($inspecao['inspecao_visual'] ?? '{}', true);
$inspecao['inspecao_termografica'] = json_decode($inspecao['inspecao_termografica'] ?? '{}', true);
$inspecao['limpeza'] = json_decode($inspecao['limpeza'] ?? '{}', true);
$inspecao['etiquetas'] = json_decode($inspecao['etiquetas'] ?? '{}', true);
$inspecao['funcionamento'] = json_decode($inspecao['funcionamento'] ?? '{}', true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerar PDF - Inspeção #<?= $inspecao['id'] ?></title>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #pdf-content {
            position: absolute;
            left: -9999px;
            width: 210mm;
            background: white;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .pdf-table th, .pdf-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .pdf-table th {
            background-color: #1e88e5;
            color: white;
            font-weight: bold;
        }
        .checklist-ok {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .checklist-erro {
            background-color: #ffebee;
            color: #c62828;
        }
        .pdf-section {
            margin-bottom: 20px;
        }
        .pdf-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e88e5;
            margin-bottom: 15px;
            text-align: center;
        }
        .pdf-subtitle {
            font-size: 14px;
            font-weight: bold;
            color: #1e88e5;
            margin: 15px 0 10px 0;
            border-bottom: 2px solid #1e88e5;
            padding-bottom: 5px;
        }
        .foto-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 15px 0;
        }
        .foto-item {
            text-align: center;
        }
        .foto-item img {
            width: 100%;
            max-width: 300px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .foto-caption {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="text-center mb-4">
            <h3>Gerando PDF com fotos...</h3>
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3 text-muted">Por favor, aguarde...</p>
        </div>
    </div>

    <!-- Conteúdo para o PDF (oculto) -->
    <div id="pdf-content">
        <div class="pdf-title">RELATÓRIO DE INSPEÇÃO</div>
        
        <!-- Informações Básicas -->
        <div class="pdf-section">
            <table class="pdf-table">
                <thead>
                    <tr>
                        <th colspan="2">Informações Básicas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Campo:</strong></td>
                        <td><?= htmlspecialchars($inspecao['campo']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Subcampo:</strong></td>
                        <td><?= htmlspecialchars($inspecao['subcampo']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Inversor:</strong></td>
                        <td><?= htmlspecialchars($inspecao['inversor']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Técnico 1:</strong></td>
                        <td><?= htmlspecialchars($inspecao['tecnico1']) ?></td>
                    </tr>
                    <?php if (!empty($inspecao['tecnico2'])): ?>
                    <tr>
                        <td><strong>Técnico 2:</strong></td>
                        <td><?= htmlspecialchars($inspecao['tecnico2']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><strong>Data Início:</strong></td>
                        <td><?= date('d/m/Y', strtotime($inspecao['data_inicio'])) ?> às <?= $inspecao['hora_inicio'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Data Final:</strong></td>
                        <td><?= date('d/m/Y', strtotime($inspecao['data_final'])) ?> às <?= $inspecao['hora_final'] ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php
        // Função para renderizar checklist
        function renderChecklist($titulo, $dados) {
            if (empty($dados)) return;
            echo '<div class="pdf-section">';
            echo '<div class="pdf-subtitle">' . htmlspecialchars($titulo) . '</div>';
            echo '<table class="pdf-table">';
            foreach ($dados as $item => $valor) {
                $classe = ($valor === 'ok' || $valor === true) ? 'checklist-ok' : 'checklist-erro';
                $simbolo = ($valor === 'ok' || $valor === true) ? '✅' : '❌';
                echo '<tr class="' . $classe . '">';
                echo '<td width="5%">' . $simbolo . '</td>';
                echo '<td>' . htmlspecialchars($item) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        }

        renderChecklist('Inspeção Visual', $inspecao['inspecao_visual']);
        renderChecklist('Inspeção Termográfica', $inspecao['inspecao_termografica']);
        renderChecklist('Limpeza', $inspecao['limpeza']);
        renderChecklist('Etiquetas', $inspecao['etiquetas']);
        renderChecklist('Funcionamento', $inspecao['funcionamento']);
        ?>

        <!-- Comentários -->
        <?php if (!empty($inspecao['comentarios'])): ?>
        <div class="pdf-section">
            <div class="pdf-subtitle">Comentários</div>
            <div style="padding: 10px; background: #f5f5f5; border-radius: 4px;">
                <?= nl2br(htmlspecialchars($inspecao['comentarios'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fotos -->
        <?php if (!empty($fotos)): ?>
        <div class="pdf-section">
            <div class="pdf-subtitle">Registro Fotográfico (<?= count($fotos) ?> fotos)</div>
            <div class="foto-grid">
                <?php foreach ($fotos as $idx => $foto): ?>
                <div class="foto-item">
                    <img src="<?= htmlspecialchars($foto['photo_data']) ?>" alt="Foto <?= $idx + 1 ?>">
                    <div class="foto-caption">
                        Foto <?= $idx + 1 ?> - <?= date('d/m/Y H:i', strtotime($foto['created_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const inspecao = <?= json_encode($inspecao) ?>;
        const totalFotos = <?= count($fotos) ?>;

        async function gerarPDF() {
            try {
                console.log('Iniciando geração do PDF...');
                const { jsPDF } = window.jspdf;
                const content = document.getElementById('pdf-content');
                
                if (!content) {
                    throw new Error('Conteúdo não encontrado');
                }

                console.log('Renderizando HTML para canvas...');
                const canvas = await html2canvas(content, {
                    scale: 1.5,
                    useCORS: true,
                    allowTaint: true,
                    logging: true,
                    backgroundColor: '#ffffff',
                    imageTimeout: 0,
                    removeContainer: true
                });

                console.log('Canvas criado, gerando PDF...');
                const imgData = canvas.toDataURL('image/jpeg', 0.90);
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4',
                    compress: true
                });

                const imgWidth = 210;
                const pageHeight = 297;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
                heightLeft -= pageHeight;

                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
                    heightLeft -= pageHeight;
                }

                console.log('Salvando PDF...');
                const nomeArquivo = `Inspecao_${inspecao.campo}_${inspecao.inversor}_${new Date().toISOString().split('T')[0]}.pdf`;
                pdf.save(nomeArquivo);

                console.log('PDF gerado com sucesso!');
                setTimeout(() => {
                    window.location.href = 'detalhes.php?id=<?= $inspecao['id'] ?>';
                }, 1000);
            } catch (error) {
                console.error('Erro ao gerar PDF:', error);
                alert('Erro ao gerar PDF: ' + error.message + '\n\nVerifique o console para mais detalhes.');
                window.location.href = 'detalhes.php?id=<?= $inspecao['id'] ?>';
            }
        }

        // Aguardar imagens carregarem
        window.addEventListener('load', () => {
            if (totalFotos > 0) {
                // Aguardar um pouco mais para garantir que as imagens carregaram
                setTimeout(gerarPDF, 2000);
            } else {
                setTimeout(gerarPDF, 500);
            }, () => {
            setTimeout(gerarPDF, 500);
        });
    </script>
</body>
</html>
