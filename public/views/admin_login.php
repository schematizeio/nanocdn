<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 360px; margin: 4rem auto; padding: 1rem; }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; }
        label { display: block; margin-top: 0.75rem; font-weight: 500; }
        input[type="email"], input[type="password"] { width: 100%; padding: 0.5rem; margin-top: 0.25rem; }
        button { margin-top: 1rem; padding: 0.6rem 1rem; width: 100%; background: #333; color: #fff; border: none; cursor: pointer; }
        .error { background: #fee; color: #c00; padding: 0.5rem; margin-bottom: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h1>NanoCDN – Login</h1>
    <?php if (!empty($_GET['expired'])): ?><div style="background:#e7f3ff;color:#006;padding:0.5rem;margin-bottom:1rem;font-size:0.9rem;">Sessão expirada. Faça login novamente.</div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
        <label>E-mail <input type="email" name="email" required autofocus></label>
        <label>Senha <input type="password" name="password" required></label>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
