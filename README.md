📊 Gerenciamento de Atividades O&M

Dashboard para consolidação de dados operacionais, geração de relatórios técnicos e análise de performance em atividades de Operação & Manutenção.

🚀 Sobre o Projeto

O Gerenciamento de Atividades O&M é um sistema desenvolvido para centralizar informações enviadas pelo aplicativo dos técnicos de campo, transformando dados operacionais em:

📈 Gráficos de desempenho

📝 Relatórios técnicos estruturados

📂 Documentação padronizada

📊 Indicadores de performance operacional

O objetivo é melhorar a rastreabilidade, organização e análise estratégica das atividades de O&M.

🏗️ Arquitetura do Sistema
Técnicos de Campo (App)
          ↓
     Banco de Dados
          ↓
   Dashboard Web
          ↓
Relatórios | Gráficos | Indicadores
🎯 Funcionalidades

✅ Recebimento automático de dados do aplicativo de campo

✅ Consolidação de atividades por técnico, data e equipamento

✅ Geração automática de relatórios operacionais

✅ Criação de gráficos de desempenho

✅ Organização e padronização de documentação técnica

✅ Exportação de dados

🔄 Evolução contínua com novos indicadores

📊 Indicadores Monitorados

Atividades executadas por período

Tempo médio de atendimento

Ocorrências por tipo

Performance por técnico

Status de pendências

Histórico de intervenções

## 📊 Estrutura

```
admin-dashboard/
├── index.php           (Login)
├── dashboard.php       (Painel principal)
├── inspecoes.php       (Lista de inspeções)
├── detalhes.php        (Detalhes da inspeção)
├── relatorios.php      (Relatórios e exportação)
├── logout.php          (Sair)
├── config.php          (Configurações)
├── auth.php            (Autenticação)
├── supabase.php        (API Supabase)
├── includes/
│   ├── navbar.php
│   └── sidebar.php
└── assets/
    ├── css/admin.css
    └── js/admin.js
```

## 🔧 Requisitos

- PHP 7.4 ou superior
- Extensão cURL habilitada
- Conexão com internet (para API Supabase)

## 📝 Notas

- O dashboard conecta diretamente no Supabase via API REST
- Não utiliza banco de dados local
- Todas as consultas são feitas em tempo real
- As credenciais estão em `config.php` (altere em produção!)

## 🎨 Tecnologias

- PHP 7.4+
- Bootstrap 5
- Chart.js 4
- Supabase REST API

---

Desenvolvido por Vinicius Martins
