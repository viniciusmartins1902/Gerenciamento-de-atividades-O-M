# Dashboard Administrativo - PowerChina

Dashboard web PHP para gerenciamento e visualização de inspeções sincronizadas do aplicativo mobile.

## 📋 Funcionalidades

- ✅ Login com autenticação por sessão
- 📊 Dashboard com estatísticas e gráficos
- 📋 Lista de inspeções com filtros
- 🔍 Visualização detalhada de cada inspeção
- 📷 Galeria de fotos das inspeções
- 📈 Relatórios por campo, técnico e período
- 📥 Exportação para CSV/Excel
- 🎨 Design moderno e responsivo

## 🚀 Instalação

### Opção 1: XAMPP Local

1. Instale o XAMPP
2. Copie a pasta `admin-dashboard` para `C:\xampp\htdocs\`
3. Acesse: `http://localhost/admin-dashboard`

### Opção 2: InfinityFree

1. Faça upload de todos os arquivos via FTP para a pasta `htdocs`
2. Acesse: `https://seudominio.infinityfreeapp.com`

### Opção 3: PHP Built-in Server

```bash
cd c:\dev\admin-dashboard
php -S localhost:8000
```

Acesse: `http://localhost:8000`

## 🔐 Credenciais de Acesso

**Administrador:**
- Email: `admin@powerchina.com.br`
- Senha: `Admin@2026`

**Vinicius:**
- Email: `vinicius.pimenta@powerchina.com.br`
- Senha: `Mrt@2026`

## ⚙️ Configuração

Edite o arquivo `config.php` para ajustar:

- URL do Supabase
- Chave de API do Supabase
- Usuários administrativos
- Fuso horário

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

Desenvolvido para PowerChina - Sistema de Inspeções
