<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo tenant - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 480px; margin: 2rem auto; padding: 1rem; }
        h1 { font-size: 1.25rem; }
        label { display: block; margin-top: 0.75rem; }
        input[type="text"], input[type="checkbox"] { margin-right: 0.5rem; }
        button { margin-top: 1rem; padding: 0.5rem 1rem; background: #333; color: #fff; border: none; cursor: pointer; }
        nav { margin-bottom: 1rem; } nav a { color: #06c; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <h1>Novo tenant</h1>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
        <label>Nome <input type="text" name="name" required></label>
        <label><input type="checkbox" name="conversion_enabled" value="1"> Habilitar conversão de imagens (tamanhos/formatos)</label>
        <button type="submit">Criar</button>
    </form>
</body>
</html>
