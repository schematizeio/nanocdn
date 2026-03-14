<?php
$baseUrl = base_url();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arquivos - <?= htmlspecialchars($tenant['name']) ?> - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 1000px; margin: 0 auto; padding: 1rem; }
        h1 { font-size: 1.25rem; }
        nav { margin-bottom: 1rem; } nav a { color: #06c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.4rem; border-bottom: 1px solid #ddd; font-size: 0.9rem; }
        .btn { display: inline-block; padding: 0.4rem 0.8rem; background: #333; color: #fff; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; margin-right: 0.25rem; }
        .btn-danger { background: #c00; }
        .url { font-size: 0.8rem; color: #666; max-width: 280px; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p style="font-size:0.9rem;color:#666;margin-bottom:0.25rem;"><a href="<?= $baseUrl ?>/admin" style="color:#06c;">Tenants</a> → <a href="<?= $baseUrl ?>/admin/tenants/<?= $tenant['id'] ?>" style="color:#06c;"><?= htmlspecialchars($tenant['name']) ?></a> → Arquivos</p>
    <h1>Arquivos – <?= htmlspecialchars($tenant['name']) ?></h1>
    <?php if (isset($totalFiles) && $totalFiles > 0): ?>
    <p style="margin-bottom:0.5rem;"><?= (int)$totalFiles ?> arquivo(s). <?php if (isset($totalPages) && $totalPages > 1): ?> Página <?= (int)$page ?> de <?= $totalPages ?>. <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">← Anterior</a><?php endif; ?> <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>">Próxima →</a><?php endif; ?><?php endif; ?></p>
    <?php endif; ?>
    <table>
        <tr><th>Nome</th><th>Data</th><th>Tamanho</th><th>Variações / URLs</th><th>Ação</th></tr>
        <?php foreach ($files as $f): ?>
        <tr>
            <td><?= htmlspecialchars($f['original_name']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($f['created_at'])) ?></td>
            <td><?= number_format($f['size_bytes'] / 1024, 1) ?> KB</td>
            <td>
                <?php foreach ($f['variants'] as $v): ?>
                <a href="<?= $baseUrl ?>/f/<?= $tenant['uuid'] ?>/<?= $f['file_uuid'] ?>/<?= htmlspecialchars(basename($v['path'])) ?>"><?= $v['size_key'] ?>.<?= $v['format'] ?></a>
                <span class="url" title="<?= $baseUrl ?>/f/<?= $tenant['uuid'] ?>/<?= $f['file_uuid'] ?>/<?= basename($v['path']) ?>"><?= $baseUrl ?>/f/<?= $tenant['uuid'] ?>/<?= $f['file_uuid'] ?>/<?= basename($v['path']) ?></span>
                <br>
                <?php endforeach; ?>
            </td>
            <td>
                <form method="post" action="<?= $baseUrl ?>/admin/files/delete/<?= $f['id'] ?>" style="display:inline;" onsubmit="return confirm('Excluir?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                    <button type="submit" class="btn btn-danger">Excluir</button>
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
