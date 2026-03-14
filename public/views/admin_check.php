<?php
$caps = $caps ?? ImageConverter::getServerCapabilities();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 560px; margin: 2rem auto; padding: 1rem; }
        h1 { font-size: 1.25rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 0.5rem; border-bottom: 1px solid #ddd; }
        .ok { color: #060; }
        .no { color: #c00; }
        a { color: #06c; }
        nav { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <h1>Verificação do servidor</h1>
    <table>
        <tr><th>Recurso</th><th>Status</th></tr>
        <tr><td>PHP</td><td><?= htmlspecialchars($phpVersion ?? PHP_VERSION) ?></td></tr>
        <tr><td>Banco de dados</td><td class="<?= !empty($dbOk) ? 'ok' : 'no' ?>"><?= !empty($dbOk) ? 'Conectado' : 'Erro de conexão' ?></td></tr>
        <tr><td>Storage gravável</td><td class="<?= !empty($storageWritable) ? 'ok' : 'no' ?>"><?= !empty($storageWritable) ? 'Sim' : 'Não (verifique permissões)' ?></td></tr>
        <tr><td colspan="2" style="padding-top:0.5rem;"><strong>Conversão de imagens</strong></td></tr>
        <tr><td>GD</td><td class="<?= $caps['gd'] ? 'ok' : 'no' ?>"><?= $caps['gd'] ? 'Suportado' : 'Não disponível' ?></td></tr>
        <tr><td>Imagick</td><td class="<?= $caps['imagick'] ? 'ok' : 'no' ?>"><?= $caps['imagick'] ? 'Suportado' : 'Não disponível' ?></td></tr>
        <tr><td>WebP</td><td class="<?= $caps['webp'] ? 'ok' : 'no' ?>"><?= $caps['webp'] ? 'Suportado' : 'Não disponível' ?></td></tr>
        <tr><td>AVIF</td><td class="<?= $caps['avif'] ? 'ok' : 'no' ?>"><?= $caps['avif'] ? 'Suportado' : 'Não disponível' ?></td></tr>
        <tr><td>Driver usado</td><td><?= htmlspecialchars($caps['driver'] ?? 'Nenhum') ?></td></tr>
        <tr><td>Conversão disponível</td><td class="<?= $caps['conversion_available'] ? 'ok' : 'no' ?>"><?= $caps['conversion_available'] ? 'Sim' : 'Não' ?></td></tr>
    </table>
    <?php if (!empty($appVersion)): ?><p style="margin-top:1.5rem;font-size:0.85rem;color:#999;">NanoCDN <?= htmlspecialchars($appVersion) ?></p><?php endif; ?>
</body>
</html>
