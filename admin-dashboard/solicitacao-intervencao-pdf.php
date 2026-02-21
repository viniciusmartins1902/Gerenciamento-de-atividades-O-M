<?php
require_once('TCPDF/tcpdf.php');
require_once('supabase.php');
require_once('auth.php');
require_once('controle-acesso.php');

$supabase = new Supabase();
$nivel = null;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nivel = isset($_SESSION['nivel_acesso']) ? (int)$_SESSION['nivel_acesso'] : null;

// Se vier por ID, busca do banco
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sol = $supabase->request('GET', '/rest/v1/intervention_requests?id=eq.'.$id);
    if (!$sol || !isset($sol[0])) {
        die('Solicitação não encontrada.');
    }
    $row = $sol[0];
    // Permite PDF sempre para usuários autenticados internos (nível 1,2,3,4), mas bloqueia para empresas externas se não aprovado
    if ($nivel === null && !in_array($row['status'], ['aprovado_seguranca', 'finalizado'])) {
        $voltar = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'documentacao.php') !== false
            ? 'documentacao.php'
            : 'minhas-solicitacoes.php';
        // Corrigir barra extra na URL
        $voltar = ltrim($voltar, '/');
        echo '<div style="margin:40px auto;max-width:600px;text-align:center;font-family:sans-serif;">';
        echo '<h2>PDF disponível apenas após aprovação.</h2>';
        echo '<a href="/' . $voltar . '" style="color:#fff;background:#8b0000;padding:10px 20px;border-radius:5px;text-decoration:none;">Voltar</a>';
        echo '</div>';
        exit;
    }
    $solicitante = $row['solicitante'];
            $receptor = $row['receptor'];
            $substituto = $row['substituto'];
            $dataIn = $row['data_inicial'];
            $dataFn = $row['data_final'];
            $timeIn = $row['hora_inicial'];
            $timeFn = $row['hora_final'];
            $equipamento = $row['equipamento'];
            $descricao = $row['descricao'];
            $colaboradores = $row['colaboradores'] ?? [];
            if (is_string($colaboradores)) {
                $colaboradores = json_decode($colaboradores, true) ?: [];
            } elseif (!is_array($colaboradores)) {
                $colaboradores = [];
            }
            $resp = $row['responsavel_nome'];
            $funResp = $row['responsavel_funcao'];
            $emp = $row['empresa_responsavel'];
            $t = $row['tipo_solicitacao'] === 'emergencial' ? 'X' : ' ';
            $a = $row['tipo_solicitacao'] === 'programada' ? 'X' : ' ';
            $envio = $id;
        } else {
            $t = $a = null;
            $solicitante = $_POST['solicitante'] ?? '';
            $receptor = $_POST['receptor'] ?? 'Mesa de operações';
            $substituto = $_POST['substituto'] ?? '';
            $dataIn = $_POST['data1'] ?? '';
            $dataFn = $_POST['data2'] ?? '';
            $timeIn = $_POST['time1'] ?? '';
            $timeFn = $_POST['time2'] ?? '';
            $equipamento = $_POST['equipamento'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $colaboradores = $_POST['colaboradores'] ?? [];
            if (is_string($colaboradores)) {
                $colaboradores = json_decode($colaboradores, true) ?: [];
            } elseif (!is_array($colaboradores)) {
                $colaboradores = [];
            }
            $resp = $_POST['resp'] ?? '';
            $funResp = $_POST['funresp'] ?? '';
            $emp = $_POST['emp'] ?? '';
            $t = isset($_POST['tipo']) ? 'X' : ' ';
            $a = isset($_POST['tipo2']) ? 'X' : ' ';
            $envio = rand(1000,9999);
        }

        $htmlColaboradores = '';
        $num = 1;
        if (is_array($colaboradores) && count($colaboradores) > 0) {
            foreach ($colaboradores as $colab) {
                if (is_object($colab)) $colab = (array)$colab;
                $nome = htmlspecialchars($colab['nome'] ?? '');
                $funcao = htmlspecialchars($colab['funcao'] ?? '');
                $origem = htmlspecialchars($colab['origem'] ?? '');
                $htmlColaboradores .= '<tr>
                    <td colspan="1" class="fun">'.$num.'</td>
                    <td colspan="3">'.$nome.'</td>
                    <td colspan="2">'.$funcao.'</td>
                    <td colspan="1">'.$origem.'</td>
                    <td colspan="1"></td>
                </tr>';
                $num++;
            }
        } else {
            $htmlColaboradores .= '<tr><td colspan="8">Nenhum colaborador informado.</td></tr>';
        }

        $html = '<!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Solicitação de Intervenção</title>
            <style>
                 table { border-collapse: collapse; width: 100%; border: 0.5px solid #000; }
                 td { border: 0.5px solid #000; padding: 8px; }
                 tr{ width: 100%; border: thin solid #000; }
                 .barra{ height: 0px; background-color: #4682B4; color: white; text-align: center; font-weight: bold; }
                 .fun{ text-align: center; font-weight: bold; font-size: 11px; }
            </style>
        </head>
        <body>
            <table>
                <tr>
                  <td colspan="8" style="text-align: center;">
                      <h2>Solicitação de intervenção N°'.$envio.'</h2>
                  </td> 
                </tr>
                <tr><td colspan="8" class="barra"></td></tr>
                <tr>
                    <td colspan="4" style="text-align: left;">Solicitante: '.$solicitante.'</td>
                    <td colspan="4" style="text-align: left;">Substituto: '.$substituto.'</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: left; ">Receptor: '.$receptor.'</td>
                    <td colspan="2">N° Pex: </td>
                    <td colspan="2">N° PTW: </td>
                </tr>
                <tr><td colspan="8" >Empresa executante: '.$emp.'</td></tr>
                <tr><td colspan="8" class="barra">PROGRAMAÇÃO</td></tr>
                <tr>
                    <td colspan="4">Inicio da atividade: '.$dataIn.' - '.$timeIn.'</td>
                    <td colspan="4">Fim da atividade: '.$dataFn.' - '.$timeFn.'</td>
                </tr>
                <tr>
                    <td colspan="4">Tipo de solicitação:  </td>
                    <td colspan="2"> ('.$t.') Emergencial</td>
                    <td colspan="2"> ('.$a.') Programada</td>
                </tr>
                <tr><td colspan="8">Equipamentos: '.$equipamento.'</td></tr>
                <tr><td colspan="8" >Descrição do trabalho:</td></tr>
                <tr><td style="height: 200px;" colspan="8">'.$descricao.'</td></tr>
                <tr><td class="barra" colspan="8">COLABORADORES AUTORIZADOS A PARTICIPAR DA ATIVIDADE</td></tr>
                <tr class="fun">
                    <td colspan="1">N°</td>
                    <td colspan="3">NOME</td>
                    <td colspan="2">FUNÇÃO</td>
                    <td colspan="1">ORIGEM</td>
                    <td colspan="1">ASS</td>
                </tr>
                '.$htmlColaboradores.'
                <tr><td class="barra" colspan="8">RESPONSÁVEL BRASILEIRO PELA ATIVIDADE</td></tr>
                <tr class="fun">
                    <td colspan="4">NOME</td>
                    <td colspan="2">FUNÇÃO</td>
                    <td colspan="2">ASSINATURA</td>
                </tr>
                <tr>
                    <td colspan="4">'.htmlspecialchars($resp).'</td>
                    <td colspan="2">'.htmlspecialchars($funResp).'</td>
                    <td colspan="2"></td>
                </tr>
                <tr><td class="barra" colspan="8">SOLICITAÇÃO DE RETORNO DE GERAÇÃO</td></tr>
                <tr><td colspan="8" style="text-align: center;">Durante o retorno da geração, houve alguma falha? ()sim ()não</td></tr>
                <tr><td colspan="8">Se sim, descreva a falha:</td></tr>
                <tr><td class="barra" colspan="8">HORÁRIOS DE EXECUÇÃO</td></tr>
                <tr>
                    <td colspan="2" class="barra">Data inicial:</td>
                    <td colspan="2"></td>
                    <td colspan="2" class="barra">Horário inicial:</td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td colspan="2" class="barra">Data Final:</td>
                    <td colspan="2"></td>
                    <td colspan="2" class="barra">Horário final:</td>
                    <td colspan="2"></td>
                </tr>
                <tr><td class="barra" colspan="8">ASSINATURAS</td></tr>
                <tr>
                    <td colspan="4">Responsável pela execução:</td>
                    <td colspan="4">Responsável O&M:</td>
                </tr>
                <tr>
                    <td colspan="2">Data</td>
                    <td colspan="2">Hora</td>
                    <td colspan="2">Data</td>
                    <td colspan="2">Hora</td>
                </tr>
            </table>
        </body>
        </html>';

        // Geração do PDF com TCPDF
        date_default_timezone_set('America/Sao_Paulo');
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('solicitacao.pdf', 'I');