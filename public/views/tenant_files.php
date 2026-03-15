<?php $baseUrl = \NanoCDN\base_url(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arquivos - <?= htmlspecialchars($tenant['name']) ?> - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $baseUrl ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → <a href="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($tenant['name']) ?></a> → Arquivos</p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Enviar arquivo</h2>
        <?php if (!empty($_GET['upload']) && $_GET['upload'] === 'ok'): ?><div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200">Arquivo enviado com sucesso.</div><?php endif; ?>
        <?php if (!empty($_GET['reconverted'])): ?><div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm border border-green-200">Variantes reconvertidas.</div><?php endif; ?>
        <?php if (!empty($uploadError)): ?><div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200"><?= htmlspecialchars($uploadError) ?></div><?php endif; ?>
        <form method="post" action="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>/upload" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Arquivo (imagens até <?= (int)(\NanoCDN\config()['upload']['max_size_mb'] ?? 50) ?> MB)</label><input type="file" name="file" accept="image/jpeg,image/png,image/gif,image/webp,image/avif" required class="text-sm"></div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Enviar</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Arquivos – <?= htmlspecialchars($tenant['name']) ?></h1>
        <?php if (isset($totalFiles) && $totalFiles > 0): ?><p class="text-sm text-slate-600 mb-2"><?= (int)$totalFiles ?> arquivo(s). <?php if (isset($totalPages) && $totalPages > 1): ?> Página <?= (int)$page ?> de <?= $totalPages ?>. <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>" class="text-blue-600 hover:underline">← Anterior</a><?php endif; ?> <?php if (isset($page) && $page < $totalPages): ?><a href="?page=<?= $page + 1 ?>" class="text-blue-600 hover:underline">Próxima →</a><?php endif; ?><?php endif; ?></p><?php endif; ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead><tr class="border-b border-slate-200 bg-slate-50"><th class="text-left py-3 px-3 font-semibold text-slate-600">Nome</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Data</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Tamanho</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Variantes</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($files as $f): $variants = $f['variants'] ?? []; $onlyOriginal = count($variants) <= 1; ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="py-3 px-3"><?= htmlspecialchars($f['original_name']) ?></td>
                        <td class="py-3 px-3 text-slate-600"><?= date('d/m/Y H:i', strtotime($f['created_at'])) ?></td>
                        <td class="py-3 px-3"><?= number_format($f['size_bytes'] / 1024, 1) ?> KB</td>
                        <td class="py-3 px-3"><ul class="list-disc list-inside text-xs space-y-0.5"><?php foreach ($variants as $v): ?><li><a href="<?= $baseUrl ?>/f/<?= $tenant['uuid'] ?>/<?= $f['file_uuid'] ?>/<?= htmlspecialchars(basename($v['path'])) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($v['size_key'] . '.' . $v['format']) ?></a></li><?php endforeach; ?></ul><?php if ($onlyOriginal && !empty($variants)): ?><span class="text-xs text-slate-500">Apenas original. <a href="<?= $baseUrl ?>/admin/conversion" class="text-blue-600 hover:underline">Habilite conversão</a>.</span><?php endif; ?></td>
                        <td class="py-3 px-3">
                            <form method="post" action="<?= $baseUrl ?>/admin/files/reconvert/<?= $f['id'] ?>" class="inline"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>"><button type="submit" class="px-2 py-1 bg-slate-200 rounded text-xs font-medium hover:bg-slate-300 mr-1">Reconverter</button></form>
                            <form method="post" action="<?= $baseUrl ?>/admin/files/delete/<?= $f['id'] ?>" class="inline" onsubmit="return confirm('Excluir?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>"><button type="submit" class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200">Excluir</button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($files)): ?><tr><td colspan="5" class="py-4 px-3 text-slate-500">Nenhum arquivo. Envie pela interface acima ou via API.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
