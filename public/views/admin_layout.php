<?php
$adminTitle = $adminTitle ?? 'Painel';
$adminBase = \NanoCDN\base_url();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminTitle) ?> - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <link rel="stylesheet" href="<?= $adminBase ?>/admin.css">
</head>
<body class="admin">
    <header class="admin-header">
        <div class="admin-header-inner">
            <a href="<?= $adminBase ?>/admin" class="admin-logo"><?php $logoUrl = \NanoCDN\app_logo_url(); if ($logoUrl !== ''): ?><img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars(\NanoCDN\app_name()) ?>" class="admin-logo-img"><?php else: ?><?= htmlspecialchars(\NanoCDN\app_name()) ?><?php endif; ?></a>
            <nav class="admin-nav">
                <a href="<?= $adminBase ?>/admin">Tenants</a>
                <a href="<?= $adminBase ?>/admin/tenants/new">Criar tenant</a>
                <a href="<?= $adminBase ?>/admin/check">Checker</a>
                <a href="<?= $adminBase ?>/admin/password">Senha</a>
                <a href="<?= $adminBase ?>/admin/update">Atualizar</a>
                <a href="<?= $adminBase ?>/admin/logout">Sair</a>
            </nav>
        </div>
    </header>
    <main class="admin-main">
        <?php if (!empty($adminBreadcrumb)): ?>
        <p class="admin-breadcrumb"><?= $adminBreadcrumb ?></p>
        <?php endif; ?>
        <?= $adminContent ?? '' ?>
    </main>
</body>
</html>
