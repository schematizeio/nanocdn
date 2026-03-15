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
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Usuários</p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Usuários do painel</h1>
        <?php if (!empty($_GET['created'])): ?><div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200">Usuário criado.</div><?php endif; ?>
        <?php if (!empty($_GET['updated'])): ?><div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200">Usuário atualizado.</div><?php endif; ?>
        <p class="mb-4"><a href="<?= $base ?>/admin/users/new" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Novo usuário</a></p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead><tr class="border-b border-slate-200 bg-slate-50"><th class="text-left py-3 px-3 font-semibold text-slate-600">Nome</th><th class="text-left py-3 px-3 font-semibold text-slate-600">E-mail</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Criado em</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="py-3 px-3"><?= htmlspecialchars($u['name'] ?? $u['email']) ?></td>
                        <td class="py-3 px-3"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="py-3 px-3 text-slate-600"><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
                        <td class="py-3 px-3">
                            <a href="<?= $base ?>/admin/users/<?= (int)$u['id'] ?>/edit" class="inline-block px-3 py-1.5 bg-slate-200 text-slate-700 rounded text-sm font-medium hover:bg-slate-300 mr-1">Editar</a>
                            <?php if ((int)$u['id'] !== $currentId): ?>
                            <form method="post" action="<?= $base ?>/admin/users/<?= (int)$u['id'] ?>/delete" class="inline" onsubmit="return confirm('Excluir este usuário?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                                <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 rounded text-sm font-medium hover:bg-red-200">Excluir</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($users)): ?><p class="py-8 text-center text-slate-600">Nenhum usuário. <a href="<?= $base ?>/admin/users/new" class="text-blue-600 hover:underline font-medium">Criar primeiro usuário</a></p><?php endif; ?>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
