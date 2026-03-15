<?php
$base = \NanoCDN\base_url();
$users = $users ?? [];
$currentId = (int)(\NanoCDN\Auth::user()['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Usuários</p>

    <div class="admin-card">
        <h1>Usuários do painel</h1>
        <?php if (!empty($_GET['created'])): ?><div class="admin-alert admin-alert-success">Usuário criado.</div><?php endif; ?>
        <?php if (!empty($_GET['updated'])): ?><div class="admin-alert admin-alert-success">Usuário atualizado.</div><?php endif; ?>
        <p><a href="<?= $base ?>/admin/users/new" class="admin-btn admin-btn-primary">Novo usuário</a></p>

        <table class="admin-table">
            <thead>
                <tr><th>Nome</th><th>E-mail</th><th>Criado em</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name'] ?? $u['email']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
                    <td>
                        <a href="<?= $base ?>/admin/users/<?= (int)$u['id'] ?>/edit" class="admin-btn admin-btn-sm">Editar</a>
                        <?php if ((int)$u['id'] !== $currentId): ?>
                        <form method="post" action="<?= $base ?>/admin/users/<?= (int)$u['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Excluir este usuário?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Excluir</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($users)): ?><p class="admin-empty">Nenhum usuário. <a href="<?= $base ?>/admin/users/new" class="admin-btn">Criar primeiro usuário</a></p><?php endif; ?>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
