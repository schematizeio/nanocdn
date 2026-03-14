<?php
$baseUrl = \NanoCDN\base_url();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arquivos - <?= htmlspecialchars($tenant['name']) ?> - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $baseUrl ?>/admin">Tenants</a> → <a href="<?= $baseUrl ?>/admin/tenants/<?= $tenant['id'] ?>"><?= htmlspecialchars($tenant['name']) ?></a> → Arquivos</p>
    <div class="admin-card">
        <h1>Arquivos – <?= htmlspecialchars($tenant['name']) ?></h1>
        <?php if (isset($totalFiles) && $totalFiles > 0): ?>
        <p><?= (int)$totalFiles ?> arquivo(s). <?php if (isset($totalPages) && $totalPages > 1): ?> Página <?= (int)$page ?> de <?= $totalPages ?>. <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">← Anterior</a><?php endif; ?> <?php if (isset($page) && $page < $totalPages): ?><a href="?page=<?= $page + 1 ?>">Próxima →</a><?php endif; ?><?php endif; ?></p>
        <?php endif; ?>
        <table class="admin-table">
            <tr><th>Nome</th><th>Data</th><th>Tamanho</th><th>URLs</th><th></th></tr>
            <?php foreach ($files as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['original_name']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($f['created_at'])) ?></td>
                <td><?= number_format($f['size_bytes'] / 1024, 1) ?> KB</td>
                <td>
                    <?php foreach ($f['variants'] as $v): ?>
                    <a href="<?= $baseUrl ?>/f/<?= $tenant['uuid'] ?>/<?= $f['file_uuid'] ?>/<?= htmlspecialchars(basename($v['path'])) ?>"><?= $v['size_key'] ?>.<?= $v['format'] ?></a>
                    <?php endforeach; ?>
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
            <tr><td colspan="5">Nenhum arquivo.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
