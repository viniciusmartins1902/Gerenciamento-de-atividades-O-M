<?php
/**
 * Sistema de Controle de Acesso por Níveis
 * 
 * Nível 1: Acesso Total (apenas você)
 * Nível 2: Gerente - tudo menos padronizar técnicos e duplicadas
 * Nível 3: Analista - dashboard, relatórios, meu perfil
 * Nível 4: Segurança - apenas meu perfil
 */

function getNivelAcesso() {
    if (!isset($_SESSION['nivel_acesso'])) {
        // Se não tiver nível definido, buscar do banco
        if (isset($_SESSION['email'])) {
            require_once __DIR__ . '/supabase.php';
            $supabase = new Supabase();
            $usuario = $supabase->getUsuarioPorEmail($_SESSION['email']);
            if ($usuario && isset($usuario['nivel_acesso'])) {
                $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
                $_SESSION['funcao'] = $usuario['funcao'];
            } else {
                $_SESSION['nivel_acesso'] = 1; // Default para compatibilidade
            }
        } else {
            return 4; // Menor nível se não logado
        }
    }
    return $_SESSION['nivel_acesso'];
}

function temPermissao($pagina) {
    $nivel = getNivelAcesso();
    
    $permissoes = [
        1 => ['dashboard', 'inspecoes', 'relatorios', 'usuarios', 'perfil', 'padronizar-tecnicos', 'detalhes', 'documentos-internos'],
        2 => ['dashboard', 'inspecoes', 'relatorios', 'usuarios', 'perfil', 'detalhes', 'documentos-internos'],
        3 => ['dashboard', 'relatorios', 'perfil', 'documentos-internos'],
        4 => ['perfil', 'documentos-internos']
    ];
    
    return in_array($pagina, $permissoes[$nivel] ?? []);
}

function verificarAcesso($pagina) {
    if (!temPermissao($pagina)) {
        header('Location: dashboard.php?erro=acesso_negado');
        exit;
    }
}

function getNomeNivel($nivel) {
    $niveis = [
        1 => 'Administrador Master',
        2 => 'Gerente',
        3 => 'Analista',
        4 => 'Segurança'
    ];
    return $niveis[$nivel] ?? 'Desconhecido';
}
