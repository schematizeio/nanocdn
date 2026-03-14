<?php
$baseUrl = base_url();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant <?= htmlspecialchars($tenant['name']) ?> - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 0 auto; padding: 1rem; }
        h1, h2 { font-size: 1.25rem; }
        nav { margin-bottom: 1rem; } nav a { color: #06c; }
        label { display: block; margin-top: 0.5rem; }
        input[type="text"], input[type="checkbox"] { margin-right: 0.5rem; }
        .btn { display: inline-block; padding: 0.4rem 0.8rem; background: #333; color: #fff; text-decoration: none; font-size: 0.9rem; border: none; cursor: pointer; margin-right: 0.5rem; }
        .key-box { background: #f5f5f5; padding: 0.75rem; font-family: monospace; word-break: break-all; margin: 0.5rem 0; }
        .warning { background: #ffc; padding: 0.5rem; margin: 0.5rem 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        th, td { text-align: left; padding: 0.4rem; border-bottom: 1px solid #ddd; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p style="font-size:0.9rem;color:#666;margin-bottom:0.25rem;"><a href="<?= $baseUrl ?>/admin" style="color:#06c;">Tenants</a> → <?= htmlspecialchars($tenant['name']) ?></p>
    <h1><?= htmlspecialchars($tenant['name']) ?></h1>

    <?php if ($newKey): ?>
    <div class="warning">
        <strong>Nova API Key (guarde em local seguro):</strong>
        <div class="key-box" id="new-api-key"><?= htmlspecialchars($newKey) ?></div>
        <button type="button" class="btn btn-sm" id="copy-key-btn">Copiar</button>
        <span style="font-size:0.9rem;color:#666;">Esta chave não será exibida novamente.</span>
        <script>
            document.getElementById('copy-key-btn').onclick = function() {
                var el = document.getElementById('new-api-key');
                navigator.clipboard.writeText(el.innerText).then(function() { this.textContent = 'Copiado!'; }.bind(this));
            };
        </script>
    </div>
    <?php endif; ?>

    <form method="post" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
        <input type="hidden" name="action" value="update">
        <label><input type="text" name="name" value="<?= htmlspecialchars($tenant['name']) ?>"> Nome</label>
        <label><input type="checkbox" name="active" value="1" <?= !empty($tenant['active']) ? 'checked' : '' ?>> Ativo</label>
        <label><input type="checkbox" name="conversion_enabled" value="1" <?= !empty($tenant['conversion_enabled']) ? 'checked' : '' ?>> Conversão de imagens</label>
        <button type="submit" class="btn">Salvar</button>
    </form>

    <h2>API Keys</h2>
    <?php if (empty($apiKeys)): ?>
    <p style="background:#ffc;padding:0.5rem;">Nenhuma API Key. Gere uma para usar a API.</p>
    <?php endif; ?>
    <form method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
        <input type="hidden" name="action" value="regenerate_key">
        <button type="submit" class="btn">Gerar nova API Key</button>
    </form>
    <table>
        <tr><th>Prefix</th><th>Nome</th><th>Último uso</th><th></th></tr>
        <?php foreach ($apiKeys as $k): ?>
        <tr>
            <td><?= htmlspecialchars($k['key_prefix']) ?></td>
            <td><?= htmlspecialchars($k['name'] ?? '') ?></td>
            <td><?= $k['last_used_at'] ? date('d/m/Y H:i', strtotime($k['last_used_at'])) : '-' ?></td>
            <td>
                <form method="post" action="<?= $baseUrl ?>/admin/tenants/<?= $tenant['id'] ?>/keys/delete/<?= (int)$k['id'] ?>" style="display:inline;" onsubmit="return confirm('Revogar esta API Key? Quem a usa deixará de acessar a API.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                    <button type="submit" class="btn" style="background:#c00;font-size:0.8rem;">Revogar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Arquivos recentes</h2>
    <p><a href="<?= $baseUrl ?>/admin/tenants/<?= $tenant['id'] ?>/files" class="btn">Ver todos</a> <?= $totalFiles ? (int)$totalFiles . ' arquivo(s)' : '' ?></p>
    <?php if (isset($totalPages) && $totalPages > 1): ?>
    <p style="font-size:0.9rem;">
        Página <?= (int)$page ?> de <?= $totalPages ?>.
        <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">← Anterior</a><?php endif; ?>
        <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>">Próxima →</a><?php endif; ?>
    </p>
    <?php endif; ?>
    <table>
        <tr><th>Nome</th><th>Data</th><th>Tamanho</th><th>Links</th><th></th></tr>
        <?php foreach (array_slice($files, 0, 10) as $f): ?>
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
                <form method="post" action="<?= $baseUrl ?>/admin/files/delete/<?= $f['id'] ?>" style="display:inline;" onsubmit="return confirm('Excluir este arquivo?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                    <button type="submit" class="btn" style="background:#c00;">Excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($files)): ?>
        <tr><td colspan="5">Nenhum arquivo.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
