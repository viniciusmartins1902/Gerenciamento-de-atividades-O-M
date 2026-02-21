<?php
/**
 * Funções de Autenticação
 */

require_once 'config.php';
require_once 'controle-acesso.php';

/**
 * Verifica login no banco de dados ou fallback para array
 */
function verificarLogin($email, $senha) {
    global $admin_users;
    
    // Tentar autenticar no banco de dados
    require_once 'supabase.php';
    $supabase = new Supabase();
    $usuario = $supabase->getUsuarioPorEmail($email);
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['logado'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
        $_SESSION['funcao'] = $usuario['funcao'];
        $_SESSION['user_id'] = $usuario['id'];
        return true;
    }
    
    // Fallback para array hardcoded
    if (isset($admin_users[$email])) {
        if (password_verify($senha, $admin_users[$email]['senha'])) {
            $_SESSION['logado'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['nome'] = $admin_users[$email]['nome'];
            $_SESSION['nivel_acesso'] = 1; // Admin total por padrão
            $_SESSION['funcao'] = 'Administrador';
            return true;
        }
    }
    
    return false;
}

/**
 * Verifica se usuário está logado
 */
function estaLogado() {
    return isset($_SESSION['logado']) && $_SESSION['logado'] === true;
}

/**
 * Redireciona se não estiver logado
 */
function requerLogin() {
    if (!estaLogado()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Faz logout
 */
function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

/**
 * Nome do usuário logado
 */
function nomeUsuario() {
    return $_SESSION['nome'] ?? 'Usuário';
}
