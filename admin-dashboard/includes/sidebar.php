<?php
require_once __DIR__ . '/../controle-acesso.php';
$nivel = getNivelAcesso();
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php if (temPermissao('dashboard')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'documentacao.php' ? 'active' : '' ?>" href="documentacao.php">
                    <i class="bi bi-journal-bookmark-fill"></i> Documentação
                </a>
            </li>
            <?php if (temPermissao('documentos-internos')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'documentos-internos.php' ? 'active' : '' ?>" href="documentos-internos.php">
                    <i class="bi bi-file-earmark-text"></i> Documentos Internos
                </a>
            </li>
            <?php endif; ?>
            <?php if (temPermissao('inspecoes')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'inspecoes.php' ? 'active' : '' ?>" href="inspecoes.php">
                    <i class="bi bi-clipboard-check"></i> Inspeções
                </a>
            </li>
            <?php endif; ?>
            <?php if (temPermissao('padronizar-tecnicos')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'padronizar-tecnicos.php' ? 'active' : '' ?>" href="padronizar-tecnicos.php">
                    <i class="bi bi-people"></i> Padronizar Técnicos
                </a>
            </li>
            <?php endif; ?>
            <?php if (temPermissao('relatorios')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : '' ?>" href="relatorios.php">
                    <i class="bi bi-bar-chart"></i> Relatórios
                </a>
            </li>
            <?php endif; ?>
            <?php if (temPermissao('usuarios')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : '' ?>" href="usuarios.php">
                    <i class="bi bi-person-gear"></i> Usuários
                </a>
            </li>
            <?php endif; ?>
            <?php if ($nivel <= 2): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'empresas-externas.php' ? 'active' : '' ?>" href="empresas-externas.php">
                    <i class="bi bi-building"></i> Empresas Externas
                </a>
            </li>
            <?php endif; ?>
            <?php if (temPermissao('perfil')): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : '' ?>" href="perfil.php">
                    <i class="bi bi-person"></i> Meu Perfil
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
