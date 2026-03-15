<?php
$baseUrl = \NanoCDN\base_url();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arquivos - <?= htmlspecialchars($tenant['name']) ?> - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $baseUrl ?>/admin">Tenants</a> → <a href="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>"><?= htmlspecialchars($tenant['name']) ?></a> → Arquivos</p>

    <div class="admin-card">
        <h2>Enviar arquivo</h2>
        <?php if (!empty($_GET['upload']) && $_GET['upload'] === 'ok'): ?><div class="admin-alert admin-alert-success">Arquivo enviado com sucesso.</div><?php endif; ?>
        <?php if (!empty($_GET['reconverted'])): ?><div class="admin-alert admin-alert-success">Variantes reconvertidas.</div><?php endif; ?>
        <?php if (!empty($uploadError)): ?><div class="admin-alert admin-alert-error"><?= htmlspecialchars($uploadError) ?></div><?php endif; ?>
        <form method="post" action="<?= $baseUrl ?>/admin/tenants/<?= htmlspecialchars($tenant['uuid']) ?>/upload" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <label>Arquivo (imagens até <?= (int)(\NanoCDN\config()['upload']['max_size_mb'] ?? 50) ?> MB) <input type="file" name="file" accept="image/jpeg,image/png,image/gif,image/webp,image/avif" required></label>
            <button type="submit" class="admin-btn admin-btn-primary">Enviar</button>
        </form>
    </div>

    <div class="admin-card">
        <h1>Arquivos – <?= htmlspecialchars($tenant['name']) ?></h1>
        <?php if (isset($totalFiles) && $totalFiles > 0): ?>
        <p><?= (int)$totalFiles ?> arquivo(s). <?php if (isset($totalPages) && $totalPages > 1): ?> Página <?= (int)$page ?> de <?= $totalPages ?>. <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">← Anterior</a><?php endif; ?> <?php if (isset($page) && $page < $totalPages): ?><a href="?page=<?= $page + 1 ?>">Próxima →</a><?php endif; ?><?php endif; ?></p>
        <?php endif; ?>
        <table class="admin-table">
            <tr><th>Nome</th><th>Data</th><th>Tamanho</th><th>Variantes (todas)</th><th></th></tr>
            <?php foreach ($files as $f): ?>
            <?php $variants = $f['variants'] ?? []; $onlyOriginal = count($variants) <= 1; ?>
            <tr>
                <td><?= htmlspecialchars($f['original_name']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($f['created_at'])) ?></td>
                <td><?= number_format($f['size_bytes'] / 1024, 1) ?> KB</td>
                <td>
                    <ul class="admin-variant-list">
                        <?php foreach ($variants as $v): ?>
                        <li><a href="<?= $baseUrl ?>/f/<?= $tenant['uuid'] ?>/<?= $f['file_uuid'] ?>/<?= htmlspecialchars(basename($v['path'])) ?>"><?= htmlspecialchars($v['size_key'] . '.' . $v['format']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($onlyOriginal && !empty($variants)): ?>
                    <span class="admin-muted">Apenas original. Habilite conversão em <a href="<?= $baseUrl ?>/admin/conversion">Conversão (global)</a> e no tenant para gerar variantes.</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="<?= $baseUrl ?>/admin/files/reconvert/<?= $f['id'] ?>" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                        <button type="submit" class="admin-btn admin-btn-sm" title="Regenerar variantes a partir do original (ex.: após ajustar qualidade)">Reconverter</button>
                    </form>
                    <form method="post" action="<?= $baseUrl ?>/admin/files/delete/<?= $f['id'] ?>" style="display:inline;" onsubmit="return confirm('Excluir?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($files)): ?>
            <tr><td colspan="5">Nenhum arquivo. Envie pela interface acima ou via API.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
