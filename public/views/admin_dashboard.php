<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 0 auto; padding: 1rem; }
        h1 { font-size: 1.5rem; }
        nav { margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; }
        nav a { color: #06c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.5rem; border-bottom: 1px solid #ddd; }
        .btn { display: inline-block; padding: 0.4rem 0.8rem; background: #333; color: #fff; text-decoration: none; font-size: 0.9rem; border: none; cursor: pointer; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
        .inactive { color: #999; }
        .stats { display: flex; gap: 1.5rem; margin-bottom: 1.5rem; }
        .stat { padding: 0.75rem 1rem; background: #f5f5f5; border-radius: 4px; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <h1>Tenants</h1>
    <div class="stats">
        <div class="stat"><?= count($tenants ?? []) ?> tenant(s)</div>
        <div class="stat"><?= (int)($totalFiles['n'] ?? 0) ?> arquivo(s)</div>
    </div>
    <p><a href="<?= base_url('admin/tenants/new') ?>" class="btn">Novo tenant</a></p>
    <table>
        <thead>
            <tr><th>Nome</th><th>Slug</th><th>Arquivos</th><th>Conversão</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($tenants ?? [] as $t): ?>
            <tr class="<?= empty($t['active']) ? 'inactive' : '' ?>">
                <td><?= htmlspecialchars($t['name']) ?></td>
                <td><?= htmlspecialchars($t['slug']) ?></td>
                <td><?= (int)($t['file_count'] ?? 0) ?></td>
                <td><?= !empty($t['conversion_enabled']) ? 'Sim' : 'Não' ?></td>
                <td>
                    <a href="<?= base_url('admin/tenants/' . $t['id']) ?>" class="btn btn-sm">Editar</a>
                    <a href="<?= base_url('admin/tenants/' . $t['id'] . '/files') ?>" class="btn btn-sm">Arquivos</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
