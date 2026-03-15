<?php $base = \NanoCDN\base_url(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Atualizar</p>
    <div class="admin-card">
        <h1>Atualizar do repositório</h1>
        <p>Puxa as alterações de <a href="https://github.com/schematizeio/nanocdn" target="_blank" rel="noopener">github.com/schematizeio/nanocdn</a>. Só funciona se o projeto tiver sido instalado via <code>git clone</code>. Depois de atualizar, <a href="<?= $base ?>/admin/migrations">rode as migrações</a> se houver pendentes.</p>
        <?php if (!empty($error)): ?>
        <div class="admin-alert admin-alert-error"><?= htmlspecialchars($error) ?></div>
        <?php if (!empty($manualUpdateCommand)): ?>
        <p>Comando:</p>
        <pre class="admin-code" id="manual-update-cmd"><?= htmlspecialchars($manualUpdateCommand) ?></pre>
        <button type="button" class="admin-btn admin-btn-sm" id="copy-update-cmd">Copiar comando</button>
        <script>document.getElementById('copy-update-cmd').onclick=function(){var el=document.getElementById('manual-update-cmd');navigator.clipboard.writeText(el.innerText).then(function(){this.textContent='Copiado!';}.bind(this));};</script>
        <?php endif; ?>
        <?php endif; ?>
        <?php if (isset($output) && $output !== ''): ?><pre class="admin-code"><?= htmlspecialchars($output) ?></pre><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <button type="submit" class="admin-btn admin-btn-primary">Atualizar (git pull)</button>
        </form>
    </div>
</body>
</html>
