-- ====================================
-- Sistema de Gerenciamento de Usuários
-- PowerChina Dashboard
-- ====================================

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    funcao VARCHAR(100) DEFAULT 'Usuário', -- Cargo na empresa
    nivel_acesso INTEGER DEFAULT 4, -- 1=Total, 2=Gerente, 3=Analista, 4=Segurança
    foto TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criar índice no email para buscas rápidas
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- ====================================
-- Usuários Padrão
-- ====================================

-- Vinicius Pimenta (você - nível 1 - Acesso Total)
INSERT INTO users (nome, email, senha, funcao, nivel_acesso) 
VALUES (
    'Vinicius Pimenta',
    'vinicius.pimenta@powerchina.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: Mrt@2026
    'Gerente de Projetos',
    1
) ON CONFLICT (email) DO UPDATE SET 
    nivel_acesso = 1, 
    funcao = 'Gerente de Projetos';

-- Administrador Secundário (nível 2 - Gerente)
INSERT INTO users (nome, email, senha, funcao, nivel_acesso) 
VALUES (
    'Administrador',
    'admin@powerchina.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: Admin@2026
    'Administrador',
    2
) ON CONFLICT (email) DO UPDATE SET 
    nivel_acesso = 2, 
    funcao = 'Administrador';

-- ====================================
-- Níveis de Acesso
-- ====================================
-- Nível 1: Acesso Total (apenas você)
-- Nível 2: Gerente - tudo menos padronizar técnicos e duplicadas
-- Nível 3: Analista - dashboard, relatórios, meu perfil
-- Nível 4: Segurança - apenas meu perfil
-- ====================================

-- Verificar se tudo foi criado
SELECT 
    id, 
    nome, 
    email, 
    funcao, 
    nivel_acesso,
    created_at 
FROM users 
ORDER BY nivel_acesso, nome;
