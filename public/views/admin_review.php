<?php
$r = $review ?? [];
$base = \NanoCDN\base_url();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão do sistema - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Revisão do sistema</p>

    <div class="admin-card">
        <h1>Revisão do sistema</h1>
        <p>Visão geral do ambiente e da instalação NanoCDN.</p>
    </div>

    <div class="admin-card">
        <h2>Aplicação</h2>
        <table class="admin-table">
            <tr><th>Versão</th><td><?= htmlspecialchars($r['app_version'] ?? '') ?></td></tr>
            <tr><th>Ambiente</th><td><?= htmlspecialchars($r['env'] ?? '') ?></td></tr>
            <tr><th>URL base</th><td><code><?= htmlspecialchars($r['base_url'] ?? '') ?></code></td></tr>
            <tr><th>PHP</th><td><?= htmlspecialchars($r['php_version'] ?? PHP_VERSION) ?></td></tr>
        </table>
    </div>

    <div class="admin-card">
        <h2>Banco de dados</h2>
        <table class="admin-table">
            <tr><th>Conexão</th><td class="<?= !empty($r['db_ok']) ? 'ok' : 'no' ?>"><?= !empty($r['db_ok']) ? 'Conectado' : 'Erro' ?></td></tr>
            <?php if (!empty($r['db_error'])): ?><tr><th>Erro</th><td><?= htmlspecialchars($r['db_error']) ?></td></tr><?php endif; ?>
            <?php if (!empty($r['db_ok'])): ?>
            <tr><th>Tenants</th><td><?= (int)($r['tenants_count'] ?? 0) ?></td></tr>
            <tr><th>Arquivos</th><td><?= (int)($r['files_count'] ?? 0) ?></td></tr>
            <tr><th>API Keys</th><td><?= (int)($r['api_keys_count'] ?? 0) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="admin-card">
        <h2>Storage</h2>
        <table class="admin-table">
            <tr><th>Diretório</th><td><code><?= htmlspecialchars($r['storage_path'] ?? '') ?></code></td></tr>
            <tr><th>Gravável</th><td class="<?= !empty($r['storage_writable']) ? 'ok' : 'no' ?>"><?= !empty($r['storage_writable']) ? 'Sim' : 'Não' ?></td></tr>
            <?php if (isset($r['storage_free']) && $r['storage_free'] !== null): ?><tr><th>Espaço livre</th><td><?= (float)$r['storage_free'] ?> MB</td></tr><?php endif; ?>
        </table>
    </div>

    <div class="admin-card">
        <h2>Conversão de imagens</h2>
        <table class="admin-table">
            <tr><th>GD</th><td><?= !empty($r['caps']['gd']) ? 'Sim' : 'Não' ?></td></tr>
            <tr><th>Imagick</th><td><?= !empty($r['caps']['imagick']) ? 'Sim' : 'Não' ?></td></tr>
            <tr><th>WebP</th><td><?= !empty($r['caps']['webp']) ? 'Sim' : 'Não' ?></td></tr>
            <tr><th>AVIF</th><td><?= !empty($r['caps']['avif']) ? 'Sim' : 'Não' ?></td></tr>
            <tr><th>Driver em uso</th><td><?= htmlspecialchars($r['caps']['driver'] ?? 'Nenhum') ?></td></tr>
        </table>
    </div>

    <div class="admin-card">
        <h2>Segurança e rede</h2>
        <table class="admin-table">
            <tr><th>HTTPS</th><td class="<?= !empty($r['https']) ? 'ok' : 'no' ?>"><?= !empty($r['https']) ? 'Sim' : 'Não (recomenda-se HTTPS em produção)' ?></td></tr>
        </table>
    </div>
</body>
</html>
