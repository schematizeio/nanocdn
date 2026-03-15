<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-xl shadow-lg border border-slate-200 p-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-6"><?= htmlspecialchars(\NanoCDN\app_name()) ?> – Login</h1>
        <?php if (!empty($_GET['expired'])): ?><div class="mb-4 p-3 rounded-lg bg-blue-50 text-blue-800 text-sm border border-blue-200">Sessão expirada. Faça login novamente.</div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                <input type="email" name="email" required autofocus class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Senha</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="w-full py-2.5 bg-slate-800 text-white rounded-lg font-medium hover:bg-slate-700 transition-colors">Entrar</button>
        </form>
        <?php if (\NanoCDN\allow_registration()): ?>
        <p class="mt-4 text-sm text-slate-600"><a href="<?= htmlspecialchars(\NanoCDN\base_url('admin/register')) ?>" class="text-blue-600 hover:underline">Criar conta</a></p>
        <?php endif; ?>
        <?php if (!empty($_GET['registered'])): ?><div class="mt-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200">Conta criada. Faça login.</div><?php endif; ?>
    </div>
</body>
</html>
