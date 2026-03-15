<?php
$baseUrl = \NanoCDN\base_url();
$uploadUrl = rtrim($baseUrl, '/') . '/api/upload';
$s3Endpoint = rtrim($baseUrl, '/') . '/api/s3';
$s3BucketSlug = $tenant['slug'] ?? '';
$s3BucketUuid = $tenant['uuid'] ?? '';
$s3Region = 'auto';
$s3PublicUrl = rtrim($baseUrl, '/') . '/f/' . $s3BucketUuid;
$displayKey = !empty($newKey) ? $newKey : null;
$globalConv = $globalConversion ?? \NanoCDN\ImageConverter::getGlobalConversionOptions();
$hasConvBase = !empty($globalConv['enabled']) && !empty($globalConv['sizes']) && !empty($globalConv['formats']);
$tenantSizes = $tenantConversionSizes ?? [];
$tenantFormats = $tenantConversionFormats ?? [];
$useAllSizes = empty($tenantSizes) && $hasConvBase;
$useAllFormats = empty($tenantFormats) && $hasConvBase;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tenant['name']) ?> - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $baseUrl ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → <?= htmlspecialchars($tenant['name']) ?></p>

    <?php if (!empty($newKey)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
            <p class="font-medium text-blue-900 mb-2">Nova API Key (guarde em local seguro):</p>
            <div class="admin-code bg-slate-800 text-slate-100 p-3 rounded-lg font-mono text-sm overflow-x-auto mb-2" id="new-api-key"><?= htmlspecialchars($newKey) ?></div>
            <button type="button" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700" id="copy-key-btn">Copiar</button>
            <span class="text-sm text-slate-600 ml-2">Esta chave não será exibida novamente.</span>
        </div>
    </div>
    <script>document.getElementById('copy-key-btn').onclick=function(){navigator.clipboard.writeText(document.getElementById('new-api-key').innerText).then(function(){this.textContent='Copiado!';}.bind(this));};</script>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Configuração S3-compatible</h2>
        <p class="text-sm text-slate-600 mb-4">Use estes valores ao configurar um cliente ou aplicação S3-compatible. No NanoCDN, <strong>Access Key e Secret Key</strong> são a mesma API Key. Ao gerar uma nova API Key acima, use-a nos campos Access Key e Secret Key.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse max-w-3xl">
                <tbody class="border border-slate-200">
                    <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50 w-48">S3 Endpoint (opcional)</th><td class="py-3 px-3"><code class="font-mono text-slate-700"><?= htmlspecialchars($s3Endpoint) ?></code> <button type="button" class="ml-2 px-2 py-1 bg-slate-200 rounded text-xs font-medium admin-copy-line" data-target="<?= htmlspecialchars($s3Endpoint) ?>">Copiar</button></td></tr>
                    <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">S3 Access Key</th><td class="py-3 px-3"><?php if ($displayKey): ?><code class="font-mono"><?= htmlspecialchars($displayKey) ?></code> <button type="button" class="ml-2 px-2 py-1 bg-slate-200 rounded text-xs font-medium admin-copy-line" data-target="<?= htmlspecialchars($displayKey) ?>">Copiar</button><?php else: ?><span class="text-slate-500">Use a API Key (copie ao gerar uma nova acima)</span><?php endif; ?></td></tr>
                    <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">S3 Secret Key</th><td class="py-3 px-3"><?php if ($displayKey): ?><code class="font-mono"><?= htmlspecialchars($displayKey) ?></code> <span class="text-slate-500 text-xs">(mesmo valor do Access Key)</span><?php else: ?><span class="text-slate-500">Mesmo valor do Access Key</span><?php endif; ?></td></tr>
                    <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">S3 Bucket *</th><td class="py-3 px-3"><strong>Slug:</strong> <code class="font-mono"><?= htmlspecialchars($s3BucketSlug) ?></code> <button type="button" class="admin-copy-line ml-1 px-2 py-1 bg-slate-200 rounded text-xs" data-target="<?= htmlspecialchars($s3BucketSlug) ?>">Copiar</button> <span class="mx-2">|</span> <strong>UUID:</strong> <code class="font-mono"><?= htmlspecialchars($s3BucketUuid) ?></code> <button type="button" class="admin-copy-line ml-1 px-2 py-1 bg-slate-200 rounded text-xs" data-target="<?= htmlspecialchars($s3BucketUuid) ?>">Copiar</button></td></tr>
                    <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">S3 Region</th><td class="py-3 px-3"><code class="font-mono"><?= htmlspecialchars($s3Region) ?></code> <button type="button" class="admin-copy-line ml-1 px-2 py-1 bg-slate-200 rounded text-xs" data-target="<?= htmlspecialchars($s3Region) ?>">Copiar</button> <span class="text-slate-500 text-xs">(use auto ou us-east-1 se o cliente exigir)</span></td></tr>
                    <tr><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">S3 URL pública (opcional)</th><td class="py-3 px-3"><code class="font-mono text-xs break-all"><?= htmlspecialchars($s3PublicUrl) ?></code> <button type="button" class="admin-copy-line ml-1 px-2 py-1 bg-slate-200 rounded text-xs" data-target="<?= htmlspecialchars($s3PublicUrl) ?>">Copiar</button></td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <script>(function(){ document.querySelectorAll('.admin-copy-line').forEach(function(btn){ btn.onclick=function(){ var t=this.getAttribute('data-target'); if(t) navigator.clipboard.writeText(t).then(function(){ var b=btn; b.textContent='Copiado!'; setTimeout(function(){ b.textContent='Copiar'; }, 1500); }); }; }); })();</script>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Editar tenant</h2>
        <form method="post" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <input type="hidden" name="action" value="update">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Nome</label><input type="text" name="name" value="<?= htmlspecialchars($tenant['name']) ?>" class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
            <label class="flex items-center gap-2"><input type="checkbox" name="active" value="1" <?= !empty($tenant['active']) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600"> Ativo</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="conversion_enabled" value="1" <?= !empty($tenant['conversion_enabled']) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600"> Gerar variantes de imagem</label>
            <?php if (!$hasConvBase): ?><p class="p-3 rounded-lg bg-blue-50 text-blue-800 text-sm border border-blue-200">Configure primeiro a <a href="<?= $baseUrl ?>/admin/conversion" class="underline">conversão global</a>.</p><?php endif; ?>
            <?php if ($hasConvBase): ?>
            <fieldset class="p-4 border border-slate-200 rounded-lg">
                <legend class="text-sm font-medium text-slate-700">Variantes (subconjunto do <a href="<?= $baseUrl ?>/admin/conversion" class="text-blue-600 hover:underline">global</a>)</legend>
                <p class="mt-2 text-sm font-medium text-slate-600">Tamanhos:</p>
                <p class="flex flex-wrap gap-3 mt-1"><?php foreach ($globalConv['sizes'] as $s): ?><label class="inline-flex items-center gap-1"><input type="checkbox" name="conversion_sizes[]" value="<?= htmlspecialchars($s['key']) ?>" <?= ($useAllSizes || in_array($s['key'], $tenantSizes, true)) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600"> <?= htmlspecialchars($s['key']) ?></label><?php endforeach; ?></p>
                <p class="mt-2 text-sm font-medium text-slate-600">Formatos:</p>
                <p class="flex flex-wrap gap-3 mt-1"><?php foreach ($globalConv['formats'] as $f): ?><label class="inline-flex items-center gap-1"><input type="checkbox" name="conversion_formats[]" value="<?= htmlspecialchars($f) ?>" <?= ($useAllFormats || in_array($f, $tenantFormats, true)) ? 'checked' : '' ?> class="rounded border-slate-300 text-blue-600"> <?= htmlspecialchars($f) ?></label><?php endforeach; ?></p>
            </fieldset>
            <?php endif; ?>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg font-medium hover:bg-slate-700">Salvar</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Upload via API</h2>
        <p class="text-sm text-slate-600 mb-2">Endpoint (header <code class="bg-slate-100 px-1 rounded">API-Key</code>):</p>
        <div class="admin-code bg-slate-800 text-slate-100 p-3 rounded-lg font-mono text-sm overflow-x-auto mb-4"><?= htmlspecialchars($uploadUrl) ?></div>
        <p class="text-sm font-medium text-slate-700 mb-1">Exemplo cURL:</p>
        <div class="admin-code bg-slate-800 text-slate-100 p-3 rounded-lg font-mono text-xs overflow-x-auto">curl -X POST "<?= htmlspecialchars($uploadUrl) ?>" \<br>  -H "API-Key: nc_sua_chave" \<br>  -F "file=@/caminho/imagem.png"</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">API Keys</h2>
        <?php if (empty($apiKeys)): ?><p class="p-3 rounded-lg bg-blue-50 text-blue-800 text-sm border border-blue-200 mb-4">Nenhuma API Key. Gere uma para usar a API.</p><?php endif; ?>
        <form method="post" class="inline-block mb-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <input type="hidden" name="action" value="regenerate_key">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Gerar nova API Key</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead><tr class="border-b border-slate-200 bg-slate-50"><th class="text-left py-3 px-3 font-semibold text-slate-600">Prefix</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Nome</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Último uso</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($apiKeys as $k): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="py-3 px-3 font-mono"><?= htmlspecialchars($k['key_prefix']) ?>…</td>
                        <td class="py-3 px-3"><?= htmlspecialchars($k['name'] ?? '') ?></td>
                        <td class="py-3 px-3 text-slate-600"><?= $k['last_used_at'] ? date('d/m/Y H:i', strtotime($k['last_used_at'])) : '-' ?></td>
                        <td class="py-3 px-3">
                            <form method="post" action="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>/keys/delete/<?= (int)$k['id'] ?>" class="inline" onsubmit="return confirm('Revogar esta API Key?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                                <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 rounded text-sm font-medium hover:bg-red-200">Revogar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Arquivos</h2>
        <form method="post" action="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>/upload" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3 mb-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <input type="file" name="file" accept="image/jpeg,image/png,image/gif,image/webp,image/avif" class="text-sm">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Enviar arquivo</button>
        </form>
        <p class="mb-4"><a href="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>/files" class="text-blue-600 hover:underline font-medium">Ver todos</a> <?= isset($totalFiles) && $totalFiles ? (int)$totalFiles . ' arquivo(s)' : '' ?></p>
        <?php if (isset($totalPages) && $totalPages > 1): ?><p class="text-sm text-slate-500 mb-2">Página <?= (int)$page ?> de <?= $totalPages ?>. <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>" class="text-blue-600 hover:underline">← Anterior</a><?php endif; ?> <?php if (isset($page) && $page < $totalPages): ?><a href="?page=<?= $page + 1 ?>" class="text-blue-600 hover:underline">Próxima →</a><?php endif; ?></p><?php endif; ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead><tr class="border-b border-slate-200 bg-slate-50"><th class="text-left py-3 px-3 font-semibold text-slate-600">Nome</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Data</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Tamanho</th><th class="text-left py-3 px-3 font-semibold text-slate-600">Variantes</th><th></th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($files ?? [], 0, 10) as $f): $variants = $f['variants'] ?? []; $onlyOriginal = count($variants) <= 1; ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="py-3 px-3"><?= htmlspecialchars($f['original_name']) ?></td>
                        <td class="py-3 px-3 text-slate-600"><?= date('d/m/Y H:i', strtotime($f['created_at'])) ?></td>
                        <td class="py-3 px-3"><?= number_format($f['size_bytes'] / 1024, 1) ?> KB</td>
                        <td class="py-3 px-3">
                            <ul class="list-disc list-inside text-xs space-y-0.5">
                                <?php foreach ($variants as $v): ?><li><a href="<?= $baseUrl ?>/f/<?= $tenant['uuid'] ?>/<?= $f['file_uuid'] ?>/<?= htmlspecialchars(basename($v['path'])) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($v['size_key'] . '.' . $v['format']) ?></a></li><?php endforeach; ?>
                            </ul>
                            <?php if ($onlyOriginal && !empty($variants)): ?><span class="text-xs text-slate-500">Só original. <a href="<?= $baseUrl ?>/admin/conversion" class="text-blue-600 hover:underline">Habilite conversão</a>.</span><?php endif; ?>
                        </td>
                        <td class="py-3 px-3">
                            <form method="post" action="<?= $baseUrl ?>/admin/files/reconvert/<?= $f['id'] ?>" class="inline"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>"><button type="submit" class="px-2 py-1 bg-slate-200 rounded text-xs font-medium hover:bg-slate-300 mr-1">Reconverter</button></form>
                            <form method="post" action="<?= $baseUrl ?>/admin/files/delete/<?= $f['id'] ?>" class="inline" onsubmit="return confirm('Excluir?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>"><button type="submit" class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200">Excluir</button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($files)): ?><tr><td colspan="5" class="py-4 px-3 text-slate-500">Nenhum arquivo. Envie pelo formulário acima ou via API.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
