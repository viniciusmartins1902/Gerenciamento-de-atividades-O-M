-- ====================================
-- Sistema de Acesso Externo
-- Chaves de Acesso para Empresas Parceiras
-- ====================================

-- Tabela de Chaves de Acesso
CREATE TABLE IF NOT EXISTS access_keys (
    id SERIAL PRIMARY KEY,
    chave VARCHAR(255) UNIQUE NOT NULL,
    nome_empresa VARCHAR(255) NOT NULL,
    ativa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP
);

-- Criar índice na chave para buscas rápidas
CREATE INDEX IF NOT EXISTS idx_access_keys_chave ON access_keys(chave);

-- ====================================
-- Chaves de Exemplo
-- ====================================

-- Empresa Teste 1
INSERT INTO access_keys (chave, nome_empresa) 
VALUES (
    'POWERCHINA-EXT-2026-001',
    'Empresa Parceira 1'
) ON CONFLICT (chave) DO NOTHING;

-- Empresa Teste 2
INSERT INTO access_keys (chave, nome_empresa) 
VALUES (
    'POWERCHINA-EXT-2026-002',
    'Empresa Parceira 2'
) ON CONFLICT (chave) DO NOTHING;

-- ====================================
-- Verificar chaves criadas
-- ====================================
SELECT 
    id, 
    chave, 
    nome_empresa, 
    ativa,
    created_at,
    ultimo_acesso
FROM access_keys 
ORDER BY created_at DESC;

-- ====================================
-- Tabela de Solicitações de Intervenção
-- ====================================
CREATE TABLE IF NOT EXISTS intervention_requests (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(20) DEFAULT 'externo', -- externo/interno
    empresa_id INTEGER REFERENCES access_keys(id), -- NULL para solicitações internas
    usuario_interno_id INTEGER REFERENCES users(id), -- NULL para solicitações externas
    solicitante VARCHAR(255) NOT NULL,
    substituto VARCHAR(255),
    receptor VARCHAR(255),
    data_inicial DATE,
    data_final DATE,
    hora_inicial TIME,
    hora_final TIME,
    tipo_solicitacao VARCHAR(50), -- emergencial/programada
    equipamento VARCHAR(255),
    descricao TEXT,
    colaboradores JSONB, -- lista dinâmica de colaboradores [{nome, funcao, origem}]
    responsavel_nome VARCHAR(255),
    responsavel_funcao VARCHAR(255),
    empresa_responsavel VARCHAR(255),
    status VARCHAR(30) DEFAULT 'pendente', -- pendente, aprovado_seguranca, finalizado, rejeitado
    etapa_aprovacao SMALLINT DEFAULT 1, -- 1: enviado, 2: aprovado segurança, 3: finalizado
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índice para buscas rápidas por empresa e status
CREATE INDEX IF NOT EXISTS idx_intervention_empresa ON intervention_requests(empresa_id);
CREATE INDEX IF NOT EXISTS idx_intervention_usuario ON intervention_requests(usuario_interno_id);
CREATE INDEX IF NOT EXISTS idx_intervention_tipo ON intervention_requests(tipo);
CREATE INDEX IF NOT EXISTS idx_intervention_status ON intervention_requests(status);
