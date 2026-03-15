<?php $base = \NanoCDN\base_url(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar senha - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Senha</p>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Alterar senha</h1>
        <?php if (!empty($error)): ?><div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if (!empty($success)): ?><div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <form method="post" class="space-y-4 max-w-md">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Senha atual</label><input type="password" name="current_password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Nova senha (mín. 6)</label><input type="password" name="new_password" required minlength="6" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Confirmar nova senha</label><input type="password" name="confirm_password" required minlength="6" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg font-medium hover:bg-slate-700">Alterar senha</button>
        </form>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
