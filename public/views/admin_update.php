<?php $base = \NanoCDN\base_url(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Atualizar</p>
    <div class="admin-card">
        <h1>Atualizar do repositório</h1>
        <p>Puxa as alterações de <a href="https://github.com/schematizeio/nanocdn" target="_blank" rel="noopener">github.com/schematizeio/nanocdn</a>. Só funciona se o projeto tiver sido instalado via <code>git clone</code>.</p>
        <?php if (!empty($error)): ?><div class="admin-alert admin-alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if (isset($output) && $output !== ''): ?><pre class="admin-code"><?= htmlspecialchars($output) ?></pre><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <button type="submit" class="admin-btn admin-btn-primary">Atualizar (git pull)</button>
        </form>
    </div>
</body>
</html>
