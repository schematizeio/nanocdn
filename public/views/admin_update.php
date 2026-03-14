<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 2rem auto; padding: 1rem; }
        h1 { font-size: 1.25rem; }
        .error { background: #fee; color: #c00; padding: 0.75rem; margin: 0.5rem 0; }
        .output { background: #f5f5f5; padding: 0.75rem; font-family: monospace; font-size: 0.85rem; white-space: pre-wrap; margin: 0.5rem 0; }
        button { margin-top: 0.5rem; padding: 0.5rem 1rem; background: #333; color: #fff; border: none; cursor: pointer; }
        p { color: #666; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <h1>Atualizar do repositório</h1>
    <p>Puxa as últimas alterações de <a href="https://github.com/schematizeio/nanocdn" target="_blank" rel="noopener">github.com/schematizeio/nanocdn</a>. Só funciona se o projeto tiver sido instalado via <code>git clone</code> (o diretório precisa ter <code>.git</code>). Em hospedagem compartilhada, <code>exec()</code> pode estar desabilitada.</p>
    <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($output !== ''): ?><div class="output"><?= htmlspecialchars($output) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
        <button type="submit">Atualizar agora (git pull)</button>
    </form>
</body>
</html>
