<?php
$baseUrl = \NanoCDN\base_url();
$uploadUrl = rtrim($baseUrl, '/') . '/api/upload';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tenant['name']) ?> - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $baseUrl ?>/admin">Tenants</a> → <?= htmlspecialchars($tenant['name']) ?></p>

    <?php if (!empty($newKey)): ?>
    <div class="admin-card">
        <div class="admin-alert admin-alert-info">
            <strong>Nova API Key (guarde em local seguro):</strong>
            <div class="admin-code" id="new-api-key"><?= htmlspecialchars($newKey) ?></div>
            <button type="button" class="admin-btn admin-btn-sm" id="copy-key-btn">Copiar</button>
            <span style="font-size:0.9rem;color:#666;">Esta chave não será exibida novamente.</span>
        </div>
    </div>
    <script>document.getElementById('copy-key-btn').onclick=function(){navigator.clipboard.writeText(document.getElementById('new-api-key').innerText).then(function(){this.textContent='Copiado!';}.bind(this));};</script>
    <?php endif; ?>

    <?php
$globalConv = $globalConversion ?? \NanoCDN\ImageConverter::getGlobalConversionOptions();
$hasConvBase = !empty($globalConv['enabled']) && !empty($globalConv['sizes']) && !empty($globalConv['formats']);
$tenantSizes = $tenantConversionSizes ?? [];
$tenantFormats = $tenantConversionFormats ?? [];
$useAllSizes = empty($tenantSizes) && $hasConvBase;
$useAllFormats = empty($tenantFormats) && $hasConvBase;
?>
    <div class="admin-card">
        <h2>Editar tenant</h2>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <input type="hidden" name="action" value="update">
            <label>Nome <input type="text" name="name" value="<?= htmlspecialchars($tenant['name']) ?>"></label>
            <label><input type="checkbox" name="active" value="1" <?= !empty($tenant['active']) ? 'checked' : '' ?>> Ativo</label>
            <label><input type="checkbox" name="conversion_enabled" value="1" <?= !empty($tenant['conversion_enabled']) ? 'checked' : '' ?>> Conversão de imagens</label>
            <?php if ($hasConvBase): ?>
            <fieldset style="margin-top:1rem;padding:1rem;border:1px solid #ddd;border-radius:4px;">
                <legend>Tamanhos e formatos (apenas subconjunto do global)</legend>
                <p><strong>Tamanhos:</strong>
                <?php foreach ($globalConv['sizes'] as $s): ?>
                <label style="display:inline-block;margin-right:0.75rem;"><input type="checkbox" name="conversion_sizes[]" value="<?= htmlspecialchars($s['key']) ?>" <?= ($useAllSizes || in_array($s['key'], $tenantSizes, true)) ? 'checked' : '' ?>> <?= htmlspecialchars($s['key']) ?></label>
                <?php endforeach; ?>
                </p>
                <p><strong>Formatos:</strong>
                <?php foreach ($globalConv['formats'] as $f): ?>
                <label style="display:inline-block;margin-right:0.75rem;"><input type="checkbox" name="conversion_formats[]" value="<?= htmlspecialchars($f) ?>" <?= ($useAllFormats || in_array($f, $tenantFormats, true)) ? 'checked' : '' ?>> <?= htmlspecialchars($f) ?></label>
                <?php endforeach; ?>
                </p>
            </fieldset>
            <?php endif; ?>
            <button type="submit" class="admin-btn">Salvar</button>
        </form>
    </div>

    <div class="admin-card">
        <h2>Upload via API</h2>
        <p>Endpoint para enviar arquivos (use a API Key no header <code>API-Key</code>):</p>
        <div class="admin-code"><?= htmlspecialchars($uploadUrl) ?></div>
        <p><strong>Exemplo cURL:</strong></p>
        <div class="admin-code">curl -X POST "<?= htmlspecialchars($uploadUrl) ?>" \<br>
  -H "API-Key: nc_sua_chave" \<br>
  -F "file=@/caminho/da/imagem.png"</div>
        <p style="font-size:0.9rem;">Documentação completa em <code>docs/API.md</code> no repositório.</p>
    </div>

    <div class="admin-card">
        <h2>API Keys</h2>
        <?php if (empty($apiKeys)): ?>
        <div class="admin-alert admin-alert-info">Nenhuma API Key. Gere uma para usar a API.</div>
        <?php endif; ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <input type="hidden" name="action" value="regenerate_key">
            <button type="submit" class="admin-btn admin-btn-primary">Gerar nova API Key</button>
        </form>
        <table class="admin-table">
            <tr><th>Prefix</th><th>Nome</th><th>Último uso</th><th></th></tr>
            <?php foreach ($apiKeys as $k): ?>
            <tr>
                <td><?= htmlspecialchars($k['key_prefix']) ?></td>
                <td><?= htmlspecialchars($k['name'] ?? '') ?></td>
                <td><?= $k['last_used_at'] ? date('d/m/Y H:i', strtotime($k['last_used_at'])) : '-' ?></td>
                <td>
                    <form method="post" action="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>/keys/delete/<?= (int)$k['id'] ?>" style="display:inline;" onsubmit="return confirm('Revogar esta API Key?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Revogar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="admin-card">
        <h2>Arquivos</h2>
        <form method="post" action="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>/upload" enctype="multipart/form-data" style="margin-bottom:1rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <input type="file" name="file" accept="image/jpeg,image/png,image/gif,image/webp,image/avif">
            <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">Enviar arquivo</button>
        </form>
        <p><a href="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>/files" class="admin-btn admin-btn-sm">Ver todos</a> <?= isset($totalFiles) && $totalFiles ? (int)$totalFiles . ' arquivo(s)' : '' ?></p>
        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <p class="admin-breadcrumb">Página <?= (int)$page ?> de <?= $totalPages ?>. <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">← Anterior</a><?php endif; ?> <?php if (isset($page) && $page < $totalPages): ?><a href="?page=<?= $page + 1 ?>">Próxima →</a><?php endif; ?></p>
        <?php endif; ?>
        <table class="admin-table">
            <tr><th>Nome</th><th>Data</th><th>Tamanho</th><th>Links</th><th></th></tr>
            <?php foreach (array_slice($files ?? [], 0, 10) as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['original_name']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($f['created_at'])) ?></td>
                <td><?= number_format($f['size_bytes'] / 1024, 1) ?> KB</td>
                <td>
                    <?php foreach (array_slice($f['variants'], 0, 2) as $v): ?>
                    <a href="<?= $baseUrl ?>/f/<?= $tenant['uuid'] ?>/<?= $f['file_uuid'] ?>/<?= htmlspecialchars(basename($v['path'])) ?>"><?= $v['size_key'] ?>.<?= $v['format'] ?></a>
                    <?php endforeach; ?>
                    <?php if (count($f['variants']) > 2): ?>...<?php endif; ?>
                </td>
                <td>
                    <form method="post" action="<?= $baseUrl ?>/admin/files/delete/<?= $f['id'] ?>" style="display:inline;" onsubmit="return confirm('Excluir?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($files)): ?>
            <tr><td colspan="5">Nenhum arquivo. Envie pelo formulário acima ou via API.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
