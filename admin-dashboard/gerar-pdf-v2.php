<?php
/**
 * Geração de PDF de Inspeção
 * Usa TCPDF para gerar relatório em PDF
 */

require_once 'auth.php';
require_once 'supabase.php';

requerLogin();

$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID não fornecido');
}

$supabase = new Supabase();
$inspecao = $supabase->getInspection($id);
$fotos = $supabase->getPhotos($id);

// Debug: verificar estrutura das fotos
error_log("Total de fotos: " . count($fotos));
if (!empty($fotos)) {
    error_log("Estrutura da primeira foto: " . print_r($fotos[0], true));
}

if (!$inspecao) {
    die('Inspeção não encontrada');
}

// Decodifica campos JSON
$campos_json = ['inspecao_visual', 'inspecao_termografica', 'limpeza', 'etiquetas', 'funcionamento'];
foreach ($campos_json as $campo) {
    if (isset($inspecao[$campo])) {
        $inspecao[$campo] = json_decode($inspecao[$campo], true) ?? [];
        error_log("Campo $campo: " . print_r($inspecao[$campo], true));
    }
}

// Verifica se TCPDF está instalado
$tcpdf_path = __DIR__ . '/vendor/tcpdf/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    // Usar geração HTML simples se TCPDF não estiver disponível
    gerarPDFHTML($inspecao, $fotos, $id);
    exit;
}

require_once $tcpdf_path;

// Criar PDF com TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('PowerChina Admin Dashboard');
$pdf->SetAuthor('PowerChina');
$pdf->SetTitle('Inspeção #' . $id);
$pdf->SetSubject('Relatório de Inspeção');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

// Conteúdo
$html = gerarConteudoHTML($inspecao, $fotos, $id);
$pdf->writeHTML($html, true, false, true, false, '');

// Output
$pdf->Output('inspecao_' . $id . '.pdf', 'D');

/**
 * Gera PDF usando HTML puro (fallback)
 */
function gerarPDFHTML($inspecao, $fotos, $id) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Inspeção #<?= $id ?> - PowerChina</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                background: #f5f5f5;
            }
            .container { 
                max-width: 1200px; 
                margin: 0 auto; 
                background: white;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .header {
                background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                font-size: 28px;
                margin-bottom: 10px;
                font-weight: 600;
            }
            .header .subtitle {
                font-size: 14px;
                opacity: 0.9;
            }
            .content { padding: 30px; }
            
            .section {
                margin-bottom: 30px;
                page-break-inside: avoid;
            }
            .section-title {
                background: #0d6efd;
                color: white;
                padding: 12px 20px;
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 15px;
                border-radius: 5px;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-bottom: 20px;
            }
            .info-box {
                border: 2px solid #e9ecef;
                border-radius: 8px;
                padding: 15px;
                background: #f8f9fa;
            }
            .info-box label {
                display: block;
                font-weight: 600;
                color: #0d6efd;
                margin-bottom: 5px;
                font-size: 13px;
                text-transform: uppercase;
            }
            .info-box .value {
                font-size: 16px;
                color: #212529;
            }
            .info-full {
                grid-column: 1 / -1;
            }
            
            .checklist {
                background: #f8f9fa;
                border-left: 4px solid #0d6efd;
                padding: 20px;
                border-radius: 5px;
            }
            .checklist-item {
                padding: 8px 0;
                border-bottom: 1px solid #dee2e6;
                display: flex;
                align-items: center;
            }
            .checklist-item:last-child {
                border-bottom: none;
            }
            .checklist-item::before {
                content: "✓";
                color: #198754;
                font-weight: bold;
                margin-right: 10px;
                font-size: 18px;
            }
            
            .observacoes {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 20px;
                border-radius: 5px;
                white-space: pre-wrap;
            }
            
            .fotos-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                margin-top: 15px;
            }
            .foto-item {
                break-inside: avoid;
                page-break-inside: avoid;
                border: 2px solid #dee2e6;
                border-radius: 8px;
                overflow: hidden;
                background: white;
            }
            .foto-item img {
                width: 100%;
                height: 250px;
                object-fit: cover;
                display: block;
            }
            .foto-caption {
                padding: 10px;
                font-size: 13px;
                color: #666;
                background: #f8f9fa;
            }
            
            .footer {
                background: #212529;
                color: white;
                padding: 20px;
                text-align: center;
                margin-top: 30px;
            }
            .footer p {
                margin: 5px 0;
                font-size: 13px;
            }
            
            .print-btn {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 30px;
                background: #198754;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
                box-shadow: 0 4px 15px rgba(25, 135, 84, 0.4);
                z-index: 1000;
                transition: all 0.3s ease;
            }
            .print-btn:hover {
                background: #157347;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(25, 135, 84, 0.5);
            }
            
            @media print {
                body { background: white; }
                .container { box-shadow: none; }
                .print-btn { display: none; }
                .section { page-break-inside: avoid; }
            }
            
            @media (max-width: 768px) {
                .info-grid { grid-template-columns: 1fr; }
                .fotos-grid { grid-template-columns: 1fr; }
            }
        </style>
    </head>
    <body>
        <button class="print-btn" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
        
        <div class="container">
            <div class="header">
                <h1>RELATÓRIO DE INSPEÇÃO</h1>
                <p class="subtitle">PowerChina - Sistema de Gestão de Inspeções Fotovoltaicas</p>
                <p class="subtitle">Protocolo #<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></p>
            </div>
            
            <div class="content">
                <!-- Informações Gerais -->
                <div class="section">
                    <div class="section-title">📋 INFORMAÇÕES GERAIS</div>
                    <div class="info-grid">
                        <div class="info-box">
                            <label>Campo</label>
                            <div class="value"><?= htmlspecialchars($inspecao['campo']) ?></div>
                        </div>
                        <div class="info-box">
                            <label>Subcampo</label>
                            <div class="value"><?= htmlspecialchars($inspecao['subcampo']) ?></div>
                        </div>
                        <div class="info-box">
                            <label>Inversor</label>
                            <div class="value"><?= htmlspecialchars($inspecao['inversor']) ?></div>
                        </div>
                        <div class="info-box">
                            <label>Data de Criação</label>
                            <div class="value"><?= date('d/m/Y às H:i', strtotime($inspecao['data_criacao'])) ?></div>
                        </div>
                        <div class="info-box">
                            <label>Técnico Responsável</label>
                            <div class="value"><?= htmlspecialchars($inspecao['tecnico1']) ?></div>
                        </div>
                        <?php if (!empty($inspecao['tecnico2'])): ?>
                        <div class="info-box">
                            <label>Técnico Auxiliar</label>
                            <div class="value"><?= htmlspecialchars($inspecao['tecnico2']) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="info-box info-full">
                            <label>Período da Inspeção</label>
                            <div class="value">
                                Início: <?= $inspecao['data_inicio'] ?> às <?= $inspecao['hora_inicio'] ?> | 
                                Término: <?= $inspecao['data_final'] ?> às <?= $inspecao['hora_final'] ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inspeção Visual -->
                <?php if (!empty($inspecao['inspecao_visual'])): ?>
                <div class="section">
                    <div class="section-title">👁️ INSPEÇÃO VISUAL</div>
                    <div class="checklist">
                        <?php 
                        $items = is_array($inspecao['inspecao_visual']) ? $inspecao['inspecao_visual'] : [];
                        foreach ($items as $key => $value): 
                            if ($value): // Só mostrar se tiver valor
                                $itemFormatado = ucwords(str_replace('_', ' ', $key));
                        ?>
                            <div class="checklist-item">✓ <?= htmlspecialchars($itemFormatado) ?></div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Inspeção Termográfica -->
                <?php if (!empty($inspecao['inspecao_termografica'])): ?>
                <div class="section">
                    <div class="section-title">🌡️ INSPEÇÃO TERMOGRÁFICA</div>
                    <div class="checklist">
                        <?php 
                        $items = is_array($inspecao['inspecao_termografica']) ? $inspecao['inspecao_termografica'] : [];
                        foreach ($items as $key => $value): 
                            if ($value):
                                $itemFormatado = ucwords(str_replace('_', ' ', $key));
                        ?>
                            <div class="checklist-item">✓ <?= htmlspecialchars($itemFormatado) ?></div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Limpeza -->
                <?php if (!empty($inspecao['limpeza'])): ?>
                <div class="section">
                    <div class="section-title">🧹 LIMPEZA</div>
                    <div class="checklist">
                        <?php 
                        $items = is_array($inspecao['limpeza']) ? $inspecao['limpeza'] : [];
                        foreach ($items as $key => $value): 
                            if ($value):
                                $itemFormatado = ucwords(str_replace('_', ' ', $key));
                        ?>
                            <div class="checklist-item">✓ <?= htmlspecialchars($itemFormatado) ?></div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Etiquetas -->
                <?php if (!empty($inspecao['etiquetas'])): ?>
                <div class="section">
                    <div class="section-title">🏷️ ETIQUETAS</div>
                    <div class="checklist">
                        <?php 
                        $items = is_array($inspecao['etiquetas']) ? $inspecao['etiquetas'] : [];
                        foreach ($items as $key => $value): 
                            if ($value):
                                $itemFormatado = ucwords(str_replace('_', ' ', $key));
                        ?>
                            <div class="checklist-item">✓ <?= htmlspecialchars($itemFormatado) ?></div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Funcionamento -->
                <?php if (!empty($inspecao['funcionamento'])): ?>
                <div class="section">
                    <div class="section-title">⚡ FUNCIONAMENTO</div>
                    <div class="checklist">
                        <?php 
                        $items = is_array($inspecao['funcionamento']) ? $inspecao['funcionamento'] : [];
                        foreach ($items as $key => $value): 
                            if ($value):
                                $itemFormatado = ucwords(str_replace('_', ' ', $key));
                        ?>
                            <div class="checklist-item">✓ <?= htmlspecialchars($itemFormatado) ?></div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Observações -->
                <?php if (!empty($inspecao['observacoes'])): ?>
                <div class="section">
                    <div class="section-title">📝 OBSERVAÇÕES</div>
                    <div class="observacoes"><?= nl2br(htmlspecialchars($inspecao['observacoes'])) ?></div>
                </div>
                <?php endif; ?>

                <!-- Fotos -->
                <?php if (!empty($fotos)): ?>
                <div class="section">
                    <div class="section-title">� FOTOS (<?= count($fotos) ?>)</div>
                    <div class="fotos-grid">
                        <?php foreach ($fotos as $idx => $foto): ?>
                            <div class="foto-item">
                                <?php 
                                // Tenta photo_data, depois photo_url, depois foto_base64
                                $fotoSrc = null;
                                if (!empty($foto['photo_data'])) {
                                    $fotoSrc = $foto['photo_data'];
                                    if (!str_starts_with($fotoSrc, 'data:')) {
                                        $fotoSrc = 'data:image/jpeg;base64,' . $fotoSrc;
                                    }
                                } elseif (!empty($foto['foto_base64'])) {
                                    $fotoSrc = $foto['foto_base64'];
                                    if (!str_starts_with($fotoSrc, 'data:')) {
                                        $fotoSrc = 'data:image/jpeg;base64,' . $fotoSrc;
                                    }
                                } elseif (!empty($foto['photo_url'])) {
                                    $fotoSrc = $foto['photo_url'];
                                }
                                
                                if ($fotoSrc): ?>
                                    <img src="<?= $fotoSrc ?>" alt="Foto <?= $idx + 1 ?>">
                                <?php elseif (!empty($foto['photo_url'])): ?>
                                    <img src="<?= htmlspecialchars($foto['photo_url']) ?>" 
                                         alt="Foto <?= $idx + 1 ?>" 
                                         onerror="this.parentElement.innerHTML='<div style=\'width:100%;height:250px;background:#e9ecef;display:flex;align-items:center;justify-content:center;color:#6c757d;\'>Imagem indisponível</div>'">
                                <?php else: ?>
                                    <div style="width: 100%; height: 250px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                        Imagem não disponível
                                    </div>
                                <?php endif; ?>
                                <div class="foto-caption">
                                    <strong>Foto <?= $idx + 1 ?></strong>
                                    <?php if (!empty($foto['descricao'])): ?>
                                        <br><?= htmlspecialchars($foto['descricao']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($foto['created_at'])): ?>
                                        <br><small style="color: #999;">📅 <?= date('d/m/Y H:i', strtotime($foto['created_at'])) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="footer">
                <p><strong>POWERCHINA</strong></p>
                <p>Sistema de Gestão de Inspeções Fotovoltaicas</p>
                <p>Relatório gerado em <?= date('d/m/Y \à\s H:i:s') ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Gera conteúdo HTML para TCPDF
 */
function gerarConteudoHTML($inspecao, $fotos, $id) {
    ob_start();
    ?>
    <h1 style="color: #0d6efd; border-bottom: 3px solid #0d6efd;">Relatório de Inspeção #<?= $id ?></h1>
    
    <h2 style="background-color: #f8f9fa; padding: 10px;">Informações Gerais</h2>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;"><strong>Campo:</strong></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><?= htmlspecialchars($inspecao['campo']) ?></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><strong>Subcampo:</strong></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><?= htmlspecialchars($inspecao['subcampo']) ?></td>
        </tr>
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;"><strong>Inversor:</strong></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><?= htmlspecialchars($inspecao['inversor']) ?></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><strong>Técnico 1:</strong></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><?= htmlspecialchars($inspecao['tecnico1']) ?></td>
        </tr>
        <tr>
            <td style="padding: 5px; border: 1px solid #ddd;"><strong>Técnico 2:</strong></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><?= htmlspecialchars($inspecao['tecnico2']) ?></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><strong>Data:</strong></td>
            <td style="padding: 5px; border: 1px solid #ddd;"><?= date('d/m/Y H:i', strtotime($inspecao['data_criacao'])) ?></td>
        </tr>
    </table>
    
    <?php if (!empty($inspecao['inspecao_visual'])): ?>
    <h2 style="background-color: #f8f9fa; padding: 10px; margin-top: 15px;">✓ Inspeção Visual</h2>
    <ul>
        <?php foreach ($inspecao['inspecao_visual'] as $item): ?>
            <li><?= htmlspecialchars($item) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php if (!empty($inspecao['observacoes'])): ?>
    <h2 style="background-color: #f8f9fa; padding: 10px; margin-top: 15px;">📝 Observações</h2>
    <p><?= nl2br(htmlspecialchars($inspecao['observacoes'])) ?></p>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align: center; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        <p>Relatório gerado em <?= date('d/m/Y H:i:s') ?></p>
    </div>
    <?php
    return ob_get_clean();
}
