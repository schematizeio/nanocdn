<?php
$base = \NanoCDN\base_url();
$global = $globalConversion ?? \NanoCDN\ImageConverter::getGlobalConversionOptions();
$hasConversionBase = !empty($global['enabled']) && !empty($global['sizes']) && !empty($global['formats']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar tenant - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Novo tenant</p>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Criar tenant</h1>
        <form method="post" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Nome</label><input type="text" name="name" required placeholder="Ex: Meu site" class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <label class="flex items-center gap-2"><input type="checkbox" name="conversion_enabled" value="1" id="conv-enable" class="rounded border-slate-300 text-blue-600"> Habilitar conversão de imagens</label>
            <?php if ($hasConversionBase): ?>
            <fieldset class="p-4 border border-slate-200 rounded-lg">
                <legend class="text-sm font-medium text-slate-700">Tamanhos e formatos (vazio = usar todos)</legend>
                <p class="mt-2 text-sm font-medium text-slate-600">Tamanhos:</p>
                <p class="flex flex-wrap gap-3 mt-1"><?php foreach ($global['sizes'] as $s): ?><label class="inline-flex items-center gap-1"><input type="checkbox" name="conversion_sizes[]" value="<?= htmlspecialchars($s['key']) ?>" class="rounded border-slate-300 text-blue-600"> <?= htmlspecialchars($s['key']) ?></label><?php endforeach; ?></p>
                <p class="mt-2 text-sm font-medium text-slate-600">Formatos:</p>
                <p class="flex flex-wrap gap-3 mt-1"><?php foreach ($global['formats'] as $f): ?><label class="inline-flex items-center gap-1"><input type="checkbox" name="conversion_formats[]" value="<?= htmlspecialchars($f) ?>" class="rounded border-slate-300 text-blue-600"> <?= htmlspecialchars($f) ?></label><?php endforeach; ?></p>
            </fieldset>
            <?php elseif (empty($global['enabled'])): ?>
            <p class="p-3 rounded-lg bg-blue-50 text-blue-800 text-sm border border-blue-200">Conversão desabilitada globalmente. Habilite em <a href="<?= $base ?>/admin/conversion" class="underline">Conversão</a>.</p>
            <?php endif; ?>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Criar tenant</button>
        </form>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
