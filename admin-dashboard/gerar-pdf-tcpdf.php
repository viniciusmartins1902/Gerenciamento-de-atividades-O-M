<?php
require_once 'auth.php';
require_once 'supabase.php';
require_once __DIR__ . '/vendor/tcpdf/tcpdf.php';

$supabase = new Supabase();

if (!isset($_GET['id'])) {
    die('ID da inspeção não fornecido');
}

$id = intval($_GET['id']);

// Buscar dados da inspeção
$inspecao = $supabase->getInspection($id);
$fotos = $supabase->getPhotos($id);

if (!$inspecao) {
    die('Inspeção não encontrada');
}

// Decodifica campos JSON
$campos_json = ['inspecao_visual', 'inspecao_termografica', 'limpeza', 'etiquetas', 'funcionamento'];
foreach ($campos_json as $campo) {
    if (isset($inspecao[$campo])) {
        $inspecao[$campo] = json_decode($inspecao[$campo], true) ?? [];
    }
}

// Criar PDF
class MYPDF extends TCPDF {
    public function Header() {
        // Logo à esquerda
        $logoPath = __DIR__ . '/assets/images/images.jpg';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 8, 30, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Título centralizado
        $this->SetY(12);
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(0, 10, 'Relatório de Inspeção', 0, 0, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurações do documento
$pdf->SetCreator('PowerChina Sistema');
$pdf->SetAuthor('PowerChina');
$pdf->SetTitle('Relatório de Inspeção #' . $id);
$pdf->SetSubject('Inspeção Solar');

// Margens
$pdf->SetMargins(15, 30, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 20);

// Adicionar página
$pdf->AddPage();

// Fonte padrão
$pdf->SetFont('helvetica', '', 10);

// Título do relatório
$pdf->SetFillColor(0, 51, 102);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'RELATÓRIO DE INSPEÇÃO', 0, 1, 'C', true);
$pdf->Ln(5);

// Informações principais
$pdf->SetFillColor(236, 240, 241);
$pdf->SetTextColor(44, 62, 80);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, 'INFORMAÇÕES GERAIS', 0, 1, 'L', true);
$pdf->SetFont('helvetica', '', 10);
$pdf->Ln(2);

// Grid de informações
$labelWidth = 50;
$valueWidth = 130;

$info = [
    'Campo' => $inspecao['campo'] ?? '',
    'Subcampo' => $inspecao['subcampo'] ?? '',
    'Inversor' => $inspecao['inversor'] ?? '',
    'String' => $inspecao['string'] ?? '',
    'Técnico 1' => $inspecao['tecnico1'] ?? '',
];

if (!empty($inspecao['tecnico2'])) {
    $info['Técnico 2'] = $inspecao['tecnico2'];
}

$row = 0;
foreach ($info as $label => $value) {
    // Alternar cores
    if ($row % 2 == 0) {
        $pdf->SetFillColor(245, 245, 245);
    } else {
        $pdf->SetFillColor(220, 220, 220);
    }
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell($labelWidth, 7, $label . ':', 0, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell($valueWidth, 7, $value, 0, 1, 'L', true);
    
    $row++;
}

// Período
$periodo = sprintf(
    'Início: %s às %s | Término: %s às %s',
    $inspecao['data_inicio'] ?? '',
    $inspecao['hora_inicio'] ?? '',
    $inspecao['data_final'] ?? '',
    $inspecao['hora_final'] ?? ''
);

if ($row % 2 == 0) {
    $pdf->SetFillColor(245, 245, 245);
} else {
    $pdf->SetFillColor(220, 220, 220);
}

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($labelWidth, 7, 'Período:', 0, 0, 'L', true);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($valueWidth, 7, $periodo, 0, 1, 'L', true);

$pdf->Ln(5);

// Função para renderizar seção de checklist
function renderChecklistSection($pdf, $title, $items) {
    if (empty($items)) return;
    
    $pdf->SetFillColor(0, 51, 102);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, $title, 0, 1, 'L', true);
    $pdf->Ln(1);
    
    $pdf->SetTextColor(44, 62, 80);
    $pdf->SetFont('helvetica', '', 10);
    
    $row = 0;
    foreach ($items as $key => $value) {
        if ($value) {
            $itemFormatado = ucwords(str_replace('_', ' ', $key));
            
            // Alternar cores de fundo
            if ($row % 2 == 0) {
                $pdf->SetFillColor(245, 245, 245); // Cinza claro
            } else {
                $pdf->SetFillColor(220, 220, 220); // Cinza escuro
            }
            
            // Célula com checkmark
            $pdf->SetFont('zapfdingbats', '', 10);
            $pdf->Cell(10, 7, chr(52), 0, 0, 'C', true);
            
            // Célula com texto
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 7, ' ' . $itemFormatado, 0, 1, 'L', true);
            
            $row++;
        }
    }
    
    $pdf->Ln(3);
}

// Renderizar checklists
renderChecklistSection($pdf, 'INSPEÇÃO VISUAL', $inspecao['inspecao_visual'] ?? []);
renderChecklistSection($pdf, 'INSPEÇÃO TERMOGRÁFICA', $inspecao['inspecao_termografica'] ?? []);
renderChecklistSection($pdf, 'LIMPEZA', $inspecao['limpeza'] ?? []);
renderChecklistSection($pdf, 'ETIQUETAS', $inspecao['etiquetas'] ?? []);
renderChecklistSection($pdf, 'FUNCIONAMENTO', $inspecao['funcionamento'] ?? []);

// Observações
if (!empty($inspecao['observacoes_gerais'])) {
    $pdf->SetFillColor(241, 196, 15);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, 'OBSERVAÇÕES', 0, 1, 'L', true);
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $inspecao['observacoes_gerais'], 0, 'L');
    $pdf->Ln(3);
}

// Fotos
if (!empty($fotos)) {
    $pdf->AddPage();
    $pdf->SetFillColor(0, 51, 102);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'FOTOS DA INSPEÇÃO', 0, 1, 'C', true);
    $pdf->Ln(5);
    
    $pdf->SetTextColor(44, 62, 80);
    
    $normalPhotoCount = 0;
    $screenshotCount = 0;
    
    foreach ($fotos as $foto) {
        if (!empty($foto['photo_data'])) {
            // Converter base64 para imagem
            $imageData = $foto['photo_data'];
            
            // Remover prefixo data:image/...;base64, se existir
            if (strpos($imageData, 'base64,') !== false) {
                $imageData = explode('base64,', $imageData)[1];
            }
            
            // Decodificar imagem para verificar dimensões
            $imgContent = base64_decode($imageData);
            $imgInfo = getimagesizefromstring($imgContent);
            
            // Verificar se é screenshot (formato retrato - altura > largura)
            $isScreenshot = false;
            if ($imgInfo && $imgInfo[1] > $imgInfo[0] * 1.2) { // 20% mais alto que largo
                $isScreenshot = true;
            }
            
            if ($isScreenshot) {
                // Formato celular: duas fotos por página, lado a lado
                $imgWidth = 70;  // Largura de celular (aumentada)
                $imgHeight = 140; // Altura de celular
                $margin = 10;
                
                // Calcular posição (2 por página)
                $colIndex = $screenshotCount % 2;
                
                // Se é o primeiro screenshot ou terceiro, quinto... (nova página)
                if ($screenshotCount > 0 && $colIndex == 0) {
                    $pdf->AddPage();
                }
                
                // Centralizar as duas imagens
                $totalWidth = (2 * $imgWidth) + $margin;
                $startX = ($pdf->GetPageWidth() - $totalWidth) / 2;
                $x = $startX + ($colIndex * ($imgWidth + $margin));
                $y = 40; // Posição fixa no topo
                
                // Adicionar imagem
                $pdf->Image('@' . $imgContent, $x, $y, $imgWidth, $imgHeight, '', '', '', true, 150, '', false, false, 0, false, false, false);
                
                // Legenda
                $pdf->SetXY($x, $y + $imgHeight + 2);
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->Cell($imgWidth, 5, 'Screenshot ' . ($screenshotCount + 1), 0, 0, 'C');
                
                $screenshotCount++;
                $normalPhotoCount = 0; // Reset para controle de páginas
                
            } else {
                // Formato paisagem: 2 fotos por linha
                $imgWidth = 90;  // Aumentado
                $imgHeight = 70;  // Aumentado
                $margin = 8;
                
                // Calcular posição
                $colIndex = $normalPhotoCount % 2;
                $x = 15 + ($colIndex * ($imgWidth + $margin));
                
                // Se é a primeira foto normal após screenshots, ou primeira foto da página
                if ($normalPhotoCount == 0) {
                    $y = $pdf->GetY();
                } else if ($colIndex == 0) {
                    // Nova linha
                    $pdf->Ln($imgHeight + 10);
                    $y = $pdf->GetY();
                } else {
                    // Mesma linha, calcular Y da linha atual
                    $y = $pdf->GetY();
                }
                
                // Verificar se precisa nova página
                if ($y + $imgHeight + 15 > $pdf->GetPageHeight() - 20) {
                    $pdf->AddPage();
                    $pdf->SetFillColor(0, 51, 102);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->Cell(0, 10, 'FOTOS DA INSPEÇÃO (continuação)', 0, 1, 'C', true);
                    $pdf->Ln(5);
                    $pdf->SetTextColor(44, 62, 80);
                    $y = $pdf->GetY();
                    $x = 15;
                    $normalPhotoCount = 0;
                    $colIndex = 0;
                }
                
                // Adicionar imagem
                $pdf->Image('@' . $imgContent, $x, $y, $imgWidth, $imgHeight, '', '', '', true, 150, '', false, false, 0, false, false, false);
                
                // Legenda
                $pdf->SetXY($x, $y + $imgHeight + 1);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->Cell($imgWidth, 4, 'Foto ' . ($normalPhotoCount + 1), 0, 0, 'C');
                
                $normalPhotoCount++;
                
                // Se completou uma linha, avançar o Y
                if ($colIndex == 1) {
                    $pdf->SetY($y + $imgHeight + 6);
                }
            }
        }
    }
}

// Output do PDF
$pdf->Output('Inspecao_' . $id . '.pdf', 'I');
