<?php
$base = \NanoCDN\base_url();
$g = $globalConv ?? \NanoCDN\ImageConverter::getGlobalConversionOptions();
$sizesText = implode("\n", array_map(fn($s) => $s['key'] ?? (($s['w'] ?? 0) . 'x' . ($s['h'] ?? 0)), $g['sizes'] ?? []));
$formatsList = $g['formats'] ?? ['webp', 'avif', 'png', 'jpeg'];
$quality = max(1, min(100, (int)($g['quality'] ?? 85)));
$defaultSizesText = implode("\n", array_map(fn($s) => $s['key'], \NanoCDN\ImageConverter::getDefaultSizes()));
$caps = \NanoCDN\ImageConverter::getServerCapabilities();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversão global - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Conversão (global)</p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-2">Configuração global de conversão</h1>
        <p class="text-slate-600 text-sm mb-4">Tamanhos e formatos disponíveis para todos os tenants. Cada tenant pode escolher um subconjunto em Editar tenant.</p>
        <?php if (!empty($saved)): ?><div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200">Configuração salva.</div><?php endif; ?>
        <?php if (!empty($convError)): ?><div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200"><?= htmlspecialchars($convError) ?></div><?php endif; ?>
        <?php if (empty($caps['conversion_available'])): ?><div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200">Nenhum driver de imagem (GD/Imagick) disponível.</div><?php endif; ?>

        <form method="post" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <label class="flex items-center gap-2"><input type="checkbox" name="conversion_enabled" value="1" <?= !empty($g['enabled']) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600"> Habilitar conversão de imagens</label>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Tamanhos (um por linha, LARGURAxALTURA)</label><textarea name="conversion_sizes" id="conversion_sizes" rows="10" class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-lg font-mono text-sm"><?= htmlspecialchars($sizesText) ?></textarea><p class="mt-1"><button type="button" class="px-3 py-1.5 bg-slate-200 rounded text-sm font-medium hover:bg-slate-300" id="btn-default-sizes">Usar tamanhos padrão</button> <span class="text-xs text-slate-500">(1920×1080, 1080×1920, etc.)</span></p></div>
            <script>document.getElementById('btn-default-sizes').onclick=function(){document.getElementById('conversion_sizes').value=<?= json_encode($defaultSizesText) ?>;};</script>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Compressão / qualidade (1–100)</label><input type="number" name="conversion_quality" min="1" max="100" value="<?= $quality ?>" class="w-20 px-3 py-2 border border-slate-300 rounded-lg"> <span class="text-sm text-slate-500">Recomendado: 80–90</span></div>
            <div><p class="text-sm font-medium text-slate-700 mb-2">Formatos de saída</p><div class="flex flex-wrap gap-4"><?php foreach (['webp', 'avif', 'png', 'jpeg'] as $fmt): ?><label class="inline-flex items-center gap-1"><input type="checkbox" name="conversion_formats[]" value="<?= htmlspecialchars($fmt) ?>" <?= in_array($fmt, $formatsList, true) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600"> <?= $fmt ?></label><?php endforeach; ?></div></div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Salvar configuração global</button>
        </form>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-3">Estado atual</h2>
        <p class="text-sm text-slate-600">Conversão: <strong><?= !empty($g['enabled']) ? 'Habilitada' : 'Desabilitada' ?></strong>. Tamanhos: <?= empty($g['sizes']) ? 'nenhum' : implode(', ', array_column($g['sizes'], 'key')) ?>. Formatos: <?= empty($g['formats']) ? 'nenhum' : implode(', ', $g['formats']) ?>. Driver: <?= htmlspecialchars($caps['driver'] ?? 'nenhum') ?>. Qualidade: <?= (int)($g['quality'] ?? 85) ?>.</p>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
