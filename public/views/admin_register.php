<?php
$invite = $invite ?? null;
$regError = $regError ?? '';
$inviteEmail = is_array($invite) ? ($invite['email'] ?? '') : '';
$inviteToken = is_array($invite) ? ($invite['token'] ?? '') : (trim($_GET['token'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg border border-slate-200 p-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Criar conta – <?= htmlspecialchars(\NanoCDN\app_name()) ?></h1>
        <?php if ($invite === false): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200">Link de convite inválido ou já utilizado.</div>
            <p><a href="<?= htmlspecialchars(\NanoCDN\base_url('admin/login')) ?>" class="inline-block px-4 py-2 bg-slate-800 text-white rounded-lg font-medium hover:bg-slate-700">Ir para login</a></p>
        <?php else: ?>
            <?php if ($regError): ?><div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200"><?= htmlspecialchars($regError) ?></div><?php endif; ?>
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                <?php if ($inviteToken !== ''): ?><input type="hidden" name="invite_token" value="<?= htmlspecialchars($inviteToken) ?>"><?php endif; ?>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label><input type="email" name="email" value="<?= htmlspecialchars($inviteEmail) ?>" required autofocus class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Nome (opcional)</label><input type="text" name="name" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Senha (mín. 6 caracteres)</label><input type="password" name="password" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                <button type="submit" class="w-full py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Criar conta</button>
            </form>
            <p class="mt-4 text-sm text-slate-600"><a href="<?= htmlspecialchars(\NanoCDN\base_url('admin/login')) ?>" class="text-blue-600 hover:underline">Já tenho conta – entrar</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
