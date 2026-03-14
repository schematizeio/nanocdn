<?php
$base = \NanoCDN\base_url();
$tenants = $tenants ?? [];
$totalFiles = (int)($totalFiles['n'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <div class="admin-card">
        <h1>Tenants</h1>
        <div class="admin-stats">
            <div class="admin-stat"><strong><?= count($tenants) ?></strong> tenant(s)</div>
            <div class="admin-stat"><strong><?= $totalFiles ?></strong> arquivo(s)</div>
        </div>
        <?php if (empty($tenants)): ?>
        <div class="admin-empty">
            <p>Nenhum tenant ainda. Crie o primeiro para gerar uma API Key e começar a enviar arquivos.</p>
            <a href="<?= $base ?>/admin/tenants/new" class="admin-btn admin-btn-primary">Criar tenant</a>
        </div>
        <?php else: ?>
        <p><a href="<?= $base ?>/admin/tenants/new" class="admin-btn admin-btn-primary">Criar tenant</a></p>
        <table class="admin-table">
            <thead>
                <tr><th>Nome</th><th>Slug</th><th>Arquivos</th><th>Conversão</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $t): ?>
                <tr class="<?= empty($t['active']) ? 'inactive' : '' ?>">
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['slug']) ?></td>
                    <td><?= (int)($t['file_count'] ?? 0) ?></td>
                    <td><?= !empty($t['conversion_enabled']) ? 'Sim' : 'Não' ?></td>
                    <td>
                        <a href="<?= $base ?>/admin/tenants/<?= (int)$t['id'] ?>" class="admin-btn admin-btn-sm">Editar</a>
                        <a href="<?= $base ?>/admin/tenants/<?= (int)$t['id'] ?>/files" class="admin-btn admin-btn-sm">Arquivos</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>
