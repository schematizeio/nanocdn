<?php
$caps = $caps ?? \NanoCDN\ImageConverter::getServerCapabilities();
$base = \NanoCDN\base_url();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Checker</p>
    <div class="admin-card">
        <h1>Verificação do servidor</h1>
        <table class="admin-table">
            <tr><th>Recurso</th><th>Status</th></tr>
            <tr><td>PHP</td><td><?= htmlspecialchars($phpVersion ?? PHP_VERSION) ?></td></tr>
            <tr><td>Banco de dados</td><td class="<?= !empty($dbOk) ? 'ok' : 'no' ?>"><?= !empty($dbOk) ? 'Conectado' : 'Erro de conexão' ?></td></tr>
            <tr><td>Storage gravável</td><td class="<?= !empty($storageWritable) ? 'ok' : 'no' ?>"><?= !empty($storageWritable) ? 'Sim' : 'Não' ?></td></tr>
            <tr><td colspan="2" style="padding-top:0.5rem;"><strong>Conversão de imagens</strong></td></tr>
            <tr><td>GD</td><td><?= $caps['gd'] ? 'Sim' : 'Não' ?></td></tr>
            <tr><td>Imagick</td><td><?= $caps['imagick'] ? 'Sim' : 'Não' ?></td></tr>
            <tr><td>WebP</td><td><?= $caps['webp'] ? 'Sim' : 'Não' ?></td></tr>
            <tr><td>AVIF</td><td><?= $caps['avif'] ? 'Sim' : 'Não' ?></td></tr>
            <tr><td>Driver</td><td><?= htmlspecialchars($caps['driver'] ?? 'Nenhum') ?></td></tr>
        </table>
        <?php if (!empty($appVersion)): ?><p style="margin-top:1rem;font-size:0.85rem;color:#999;">NanoCDN <?= htmlspecialchars($appVersion) ?></p><?php endif; ?>
    </div>
</body>
</html>
