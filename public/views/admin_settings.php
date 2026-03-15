<?php
$base = \NanoCDN\base_url();
$appName = $appName ?? \NanoCDN\app_name();
$appLogoUrl = $appLogoUrl ?? \NanoCDN\app_logo_url();
$saved = $saved ?? false;
$allowRegistration = $allowRegistration ?? \NanoCDN\allow_registration();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - <?= htmlspecialchars($appName) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Configurações</p>

    <div class="admin-card">
        <h1>Whitelabel / Aparência</h1>
        <p>Defina o nome e o logo do painel. Deixe em branco para usar o padrão.</p>
        <?php if ($saved): ?><div class="admin-alert admin-alert-success">Configurações salvas.</div><?php endif; ?>

        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <label><strong>Nome do painel</strong> <input type="text" name="app_name" value="<?= htmlspecialchars($appNameValue ?? '') ?>" placeholder="Padrão: Painel"></label>
            <label><strong>URL do logo</strong> (opcional) <input type="url" name="app_logo_url" value="<?= htmlspecialchars($appLogoUrl) ?>" placeholder="https://..."></label>
            <label class="admin-form-check"><input type="checkbox" name="allow_registration" value="1" <?= !empty($allowRegistration) ? 'checked' : '' ?>> Permitir cadastro de usuários (qualquer um pode criar conta)</label>
            <button type="submit" class="admin-btn admin-btn-primary">Salvar</button>
        </form>
    </div>
</body>
</html>
