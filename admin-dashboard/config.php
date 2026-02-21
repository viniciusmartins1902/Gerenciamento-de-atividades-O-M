<?php
/**
 * Configuração do Dashboard Administrativo
 * Conexão com Supabase PostgreSQL
 */

// Configurações do Supabase
define('SUPABASE_URL', 'https://grfbyqyuhwoeyjlsobit.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdyZmJ5cXl1aHdvZXlqbHNvYml0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzAyMzIyOTUsImV4cCI6MjA4NTgwODI5NX0.JMeCAvgrFEHy9Ci4jWcNn7XMe8Nbz3sPMBIBApqCJP8');
define('API_KEY', 'powerchina_2026_secret_key_change_this');

// Credenciais de login do dashboard
$admin_users = [
    'admin@powerchina.com.br' => [
        'senha' => password_hash('Admin@2026', PASSWORD_DEFAULT),
        'nome' => 'Administrador'
    ],
    'vinicius.pimenta@powerchina.com.br' => [
        'senha' => password_hash('Mrt@2026', PASSWORD_DEFAULT),
        'nome' => 'Vinicius Pimenta'
    ]
];

// Configurações gerais
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
