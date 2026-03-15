<?php
$base = \NanoCDN\base_url();
$invites = $invites ?? [];
$inviteError = $inviteError ?? '';
$inviteSuccess = $inviteSuccess ?? '';
$newLink = $newLink ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convites - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Convites</p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-2">Convites de cadastro</h1>
        <p class="text-slate-600 text-sm mb-4">Gere um link de uso único para alguém criar conta no painel. E-mail é opcional.</p>
        <?php if ($inviteError): ?><div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200"><?= htmlspecialchars($inviteError) ?></div><?php endif; ?>
        <?php if ($inviteSuccess): ?><div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200"><?= htmlspecialchars($inviteSuccess) ?></div><?php endif; ?>
        <?php if ($newLink): ?>
        <div class="mb-4 p-4 rounded-lg bg-blue-50 border border-blue-200">
            <p class="font-medium text-blue-900 mb-2">Link de convite (copie e envie):</p>
            <input type="text" readonly value="<?= htmlspecialchars($newLink) ?>" class="w-full max-w-xl px-3 py-2 bg-white border border-blue-200 rounded font-mono text-sm" id="invite-link" onclick="this.select();">
        </div>
        <?php endif; ?>

        <form method="post" class="space-y-4 mb-8">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <input type="hidden" name="action" value="create">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">E-mail do convidado (opcional)</label><input type="email" name="email" placeholder="convidado@exemplo.com" class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Gerar link de convite</button>
        </form>

        <h2 class="text-lg font-semibold text-slate-800 mb-3">Convites recentes</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead><tr class="border-b border-slate-200 bg-slate-50"><th class="text-left py-3 px-3 font-semibold text-slate-600">E-mail</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Criado em</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Criado por</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Status</th></tr></thead>
                <tbody>
                    <?php foreach ($invites as $i): ?><tr class="border-b border-slate-100 hover:bg-slate-50"><td class="py-3 px-3"><?= htmlspecialchars($i['email'] ?? '—') ?></td><td class="py-3 px-3 text-slate-600"><?= htmlspecialchars($i['created_at'] ?? '') ?></td><td class="py-3 px-3"><?= htmlspecialchars($i['created_by_name'] ?? '—') ?></td><td class="py-3 px-3"><?= !empty($i['used_at']) ? 'Usado em ' . htmlspecialchars($i['used_at']) : 'Pendente' ?></td></tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($invites)): ?><p class="py-6 text-center text-slate-500">Nenhum convite ainda.</p><?php endif; ?>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
