<?php
$base = \NanoCDN\base_url();
$tenants = $tenants ?? [];
$totalFiles = (int)($totalFiles['n'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a></p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Tenants</h1>
        <div class="flex flex-wrap gap-4 mb-6">
            <div class="bg-slate-50 rounded-lg px-4 py-3 border border-slate-200">
                <span class="text-2xl font-bold text-slate-800"><?= count($tenants) ?></span>
                <span class="text-slate-600 text-sm"> tenant(s)</span>
            </div>
            <div class="bg-slate-50 rounded-lg px-4 py-3 border border-slate-200">
                <span class="text-2xl font-bold text-slate-800"><?= $totalFiles ?></span>
                <span class="text-slate-600 text-sm"> arquivo(s)</span>
            </div>
        </div>
        <?php if (empty($tenants)): ?>
        <div class="text-center py-12 text-slate-600">
            <p class="mb-4">Nenhum tenant ainda. Crie o primeiro para gerar uma API Key e começar a enviar arquivos.</p>
            <a href="<?= $base ?>/admin/tenants/new" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">Criar tenant</a>
        </div>
        <?php else: ?>
        <p class="mb-4"><a href="<?= $base ?>/admin/tenants/new" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">Criar tenant</a></p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="text-left py-3 px-3 font-semibold text-slate-600">Nome</th>
                        <th class="text-left py-3 px-3 font-semibold text-slate-600">Slug</th>
                        <th class="text-left py-3 px-3 font-semibold text-slate-600">Arquivos</th>
                        <th class="text-left py-3 px-3 font-semibold text-slate-600">Conversão</th>
                        <th class="text-left py-3 px-3 font-semibold text-slate-600">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $t): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50 <?= empty($t['active']) ? 'opacity-60' : '' ?>">
                        <td class="py-3 px-3"><?= htmlspecialchars($t['name']) ?></td>
                        <td class="py-3 px-3 font-mono text-slate-600"><?= htmlspecialchars($t['slug']) ?></td>
                        <td class="py-3 px-3"><?= (int)($t['file_count'] ?? 0) ?></td>
                        <td class="py-3 px-3"><?= !empty($t['conversion_enabled']) ? 'Sim' : 'Não' ?></td>
                        <td class="py-3 px-3">
                            <a href="<?= $base ?>/admin/tenants/<?= htmlspecialchars($t['uuid']) ?>" class="inline-block px-3 py-1.5 mr-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 text-sm font-medium">Editar</a>
                            <a href="<?= $base ?>/admin/tenants/<?= htmlspecialchars($t['uuid']) ?>/files" class="inline-block px-3 py-1.5 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 text-sm font-medium">Arquivos</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
