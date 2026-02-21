-- ====================================
-- UPDATE: Adição do Status "Em Execução"
-- Data: 12 de fevereiro de 2026
-- ====================================

-- Este arquivo contém as atualizações necessárias para suportar
-- o novo status "em_execucao" nas solicitações de intervenção.

-- ====================================
-- 1. NOVO STATUS ADICIONADO
-- ====================================

-- Status possíveis na tabela intervention_requests:
-- - 'pendente': Solicitação enviada, aguardando aprovação
-- - 'aprovado_om': Aprovado pelo departamento O&M (níveis 1-2)
-- - 'aprovado_seguranca': Aprovado pelo departamento de Segurança (nível 4)
-- - 'em_execucao': Solicitação em execução pela empresa externa (NOVO)
-- - 'finalizado': Solicitação finalizada
-- - 'rejeitado': Solicitação rejeitada

-- ====================================
-- 2. ALTERAÇÕES NO CÓDIGO PHP
-- ====================================

-- As seguintes alterações foram feitas nos arquivos PHP:
-- - aprovacao-solicitacoes.php: Adicionado status 'em_execucao' na função statusLabel()
-- - minhas-solicitacoes.php: Adicionado status 'em_execucao' na função statusLabel()
-- - minhas-solicitacoes.php: Adicionado botão "Marcar como Em Execução"
-- - marcar-execucao.php: Criado arquivo para processar mudança para status 'em_execucao'

-- ====================================
-- 3. WORKFLOW ATUALIZADO
-- ====================================

-- Novo fluxo de aprovação:
-- 1. Pendente → Aprovado O&M → Aprovado Segurança → Em Execução → Finalizado
--    - Empresas externas podem marcar como "Em Execução" APENAS após aprovação da Segurança
--    - O&M e Segurança podem finalizar solicitações em qualquer status

-- ====================================
-- 4. COMANDOS SQL PARA VERIFICAÇÃO (OPCIONAIS)
-- ====================================

-- Verificar solicitações com o novo status:
-- SELECT id, empresa_id, status, etapa_aprovacao, criado_em
-- FROM intervention_requests
-- WHERE status = 'em_execucao'
-- ORDER BY criado_em DESC;

-- Contar solicitações por status:
-- SELECT status, COUNT(*) as quantidade
-- FROM intervention_requests
-- GROUP BY status
-- ORDER BY status;

-- ====================================
-- 5. NÃO HÁ ALTERAÇÕES ESTRUTURAIS NECESSÁRIAS
-- ====================================

-- Como o campo 'status' é VARCHAR(30), o novo status 'em_execucao'
-- é suportado sem necessidade de alterações na estrutura da tabela.
-- Apenas o código PHP foi atualizado para reconhecer o novo status.