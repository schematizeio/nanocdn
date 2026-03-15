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
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → <a href="<?= $base ?>/admin/users" class="text-blue-600 hover:underline">Usuários</a> → <?= $isEdit ? 'Editar' : 'Novo' ?></p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4"><?= $isEdit ? 'Editar usuário' : 'Novo usuário' ?></h1>
        <?php if ($userError): ?><div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200"><?= htmlspecialchars($userError) ?></div><?php endif; ?>
        <form method="post" class="space-y-4 max-w-md">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                <?php if ($isEdit): ?><input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-slate-500"><p class="text-xs text-slate-500 mt-1">E-mail não pode ser alterado.</p><?php else: ?><input type="email" name="email" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php endif; ?>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Nome</label><input type="text" name="name" value="<?= $isEdit ? htmlspecialchars($user['name'] ?? '') : '' ?>" placeholder="Opcional" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <?php if ($isEdit): ?>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Nova senha (deixe em branco para manter)</label><input type="password" name="new_password" autocomplete="new-password" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <?php else: ?>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Senha (mín. 6 caracteres)</label><input type="password" name="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <?php endif; ?>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"><?= $isEdit ? 'Salvar' : 'Criar usuário' ?></button>
                <a href="<?= $base ?>/admin/users" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-300">Cancelar</a>
            </div>
        </form>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
