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
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Configurações</p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-2">Whitelabel / Aparência</h1>
        <p class="text-slate-600 text-sm mb-4">Defina o nome e o logo do painel. Deixe em branco para usar o padrão.</p>
        <?php if ($saved): ?><div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200">Configurações salvas.</div><?php endif; ?>

        <form method="post" class="space-y-4 max-w-md">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nome do painel</label>
                <input type="text" name="app_name" value="<?= htmlspecialchars($appNameValue ?? '') ?>" placeholder="Padrão: Painel" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">URL do logo (opcional)</label>
                <input type="url" name="app_logo_url" value="<?= htmlspecialchars($appLogoUrl) ?>" placeholder="https://..." class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="allow_registration" value="1" <?= !empty($allowRegistration) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-slate-700">Permitir cadastro de usuários (qualquer um pode criar conta)</span>
            </label>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Salvar</button>
        </form>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
