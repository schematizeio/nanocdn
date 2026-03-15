<?php
$base = \NanoCDN\base_url();
$user = $user ?? null;
$userError = $userError ?? '';
$isEdit = !empty($user['id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar usuário' : 'Novo usuário' ?> - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → <a href="<?= $base ?>/admin/users">Usuários</a> → <?= $isEdit ? 'Editar' : 'Novo' ?></p>

    <div class="admin-card">
        <h1><?= $isEdit ? 'Editar usuário' : 'Novo usuário' ?></h1>
        <?php if ($userError): ?><div class="admin-alert admin-alert-error"><?= htmlspecialchars($userError) ?></div><?php endif; ?>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <label>E-mail <?php if ($isEdit): ?><input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled></label><p style="font-size:0.85rem;color:#666;">E-mail não pode ser alterado.</p><?php else: ?><input type="email" name="email" required></label><?php endif; ?>
            <label>Nome <input type="text" name="name" value="<?= $isEdit ? htmlspecialchars($user['name'] ?? '') : '' ?>" placeholder="Opcional"></label>
            <?php if ($isEdit): ?>
            <label>Nova senha (deixe em branco para manter) <input type="password" name="new_password" autocomplete="new-password"></label>
            <?php else: ?>
            <label>Senha (mín. 6 caracteres) <input type="password" name="password" required></label>
            <?php endif; ?>
            <button type="submit" class="admin-btn admin-btn-primary"><?= $isEdit ? 'Salvar' : 'Criar usuário' ?></button>
            <a href="<?= $base ?>/admin/users" class="admin-btn">Cancelar</a>
        </form>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
