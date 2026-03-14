<?php $base = \NanoCDN\base_url(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar tenant - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Novo tenant</p>
    <div class="admin-card">
        <h1>Criar tenant</h1>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <label>Nome <input type="text" name="name" required placeholder="Ex: Meu site"></label>
            <label style="margin-top:1rem;"><input type="checkbox" name="conversion_enabled" value="1"> Habilitar conversão de imagens (vários tamanhos e formatos)</label>
            <button type="submit" class="admin-btn admin-btn-primary">Criar tenant</button>
        </form>
    </div>
</body>
</html>
