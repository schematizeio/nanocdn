<?php $caps = $caps ?? \NanoCDN\ImageConverter::getServerCapabilities(); $base = \NanoCDN\base_url(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Checker</p>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Verificação do servidor</h1>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <tbody class="border border-slate-200">
                    <tr class="border-b border-slate-200"><td class="py-3 px-3 font-medium text-slate-600 bg-slate-50">PHP</td><td class="py-3 px-3"><?= htmlspecialchars($phpVersion ?? PHP_VERSION) ?></td></tr>
                    <tr class="border-b border-slate-200"><td class="py-3 px-3 font-medium text-slate-600 bg-slate-50">Banco de dados</td><td class="py-3 px-3 <?= !empty($dbOk) ? 'text-green-600' : 'text-red-600' ?>"><?= !empty($dbOk) ? 'Conectado' : 'Erro de conexão' ?></td></tr>
                    <tr class="border-b border-slate-200"><td class="py-3 px-3 font-medium text-slate-600 bg-slate-50">Storage gravável</td><td class="py-3 px-3 <?= !empty($storageWritable) ? 'text-green-600' : 'text-red-600' ?>"><?= !empty($storageWritable) ? 'Sim' : 'Não' ?></td></tr>
                    <tr class="border-b border-slate-200"><td colspan="2" class="py-2 px-3 font-semibold text-slate-700 bg-slate-100">Conversão de imagens</td></tr>
                    <tr class="border-b border-slate-200"><td class="py-3 px-3 font-medium text-slate-600 bg-slate-50">GD</td><td class="py-3 px-3"><?= $caps['gd'] ? 'Sim' : 'Não' ?></td></tr>
                    <tr class="border-b border-slate-200"><td class="py-3 px-3 font-medium text-slate-600 bg-slate-50">Imagick</td><td class="py-3 px-3"><?= $caps['imagick'] ? 'Sim' : 'Não' ?></td></tr>
                    <tr class="border-b border-slate-200"><td class="py-3 px-3 font-medium text-slate-600 bg-slate-50">WebP</td><td class="py-3 px-3"><?= $caps['webp'] ? 'Sim' : 'Não' ?></td></tr>
                    <tr class="border-b border-slate-200"><td class="py-3 px-3 font-medium text-slate-600 bg-slate-50">AVIF</td><td class="py-3 px-3"><?= $caps['avif'] ? 'Sim' : 'Não' ?></td></tr>
                    <tr><td class="py-3 px-3 font-medium text-slate-600 bg-slate-50">Driver</td><td class="py-3 px-3"><?= htmlspecialchars($caps['driver'] ?? 'Nenhum') ?></td></tr>
                </tbody>
            </table>
        </div>
        <?php if (!empty($appVersion)): ?><p class="mt-4 text-sm text-slate-500"><?= htmlspecialchars(\NanoCDN\app_name()) ?> <?= htmlspecialchars($appVersion) ?></p><?php endif; ?>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
