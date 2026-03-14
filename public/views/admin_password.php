<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar senha - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 400px; margin: 2rem auto; padding: 1rem; }
        h1 { font-size: 1.25rem; }
        label { display: block; margin-top: 0.75rem; font-weight: 500; }
        input[type="password"] { width: 100%; padding: 0.5rem; margin-top: 0.25rem; }
        button { margin-top: 1rem; padding: 0.5rem 1rem; background: #333; color: #fff; border: none; cursor: pointer; }
        .error { background: #fee; color: #c00; padding: 0.5rem; margin: 0.5rem 0; }
        .success { background: #efe; color: #060; padding: 0.5rem; margin: 0.5rem 0; }
        nav { margin-bottom: 1rem; } nav a { color: #06c; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <h1>Alterar senha</h1>
    <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
        <label>Senha atual <input type="password" name="current_password" required></label>
        <label>Nova senha (mín. 6 caracteres) <input type="password" name="new_password" required minlength="6"></label>
        <label>Confirmar nova senha <input type="password" name="confirm_password" required minlength="6"></label>
        <button type="submit">Alterar senha</button>
    </form>
</body>
</html>
