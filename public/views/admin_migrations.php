<?php $base = \NanoCDN\base_url(); $pending = $pending ?? []; $executedIds = $executedIds ?? []; $migrationResult = $migrationResult ?? null; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrações - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Migrações</p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Migrações do banco</h1>
        <p class="text-slate-600 text-sm mb-4">Execute as migrações pendentes após atualizar o código. Cada migração roda apenas uma vez.</p>
        <?php if ($migrationResult !== null): ?>
        <div class="mb-4 p-3 rounded-lg <?= empty($migrationResult['errors']) ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-sm">
            <?php if ($migrationResult['run'] === 0): ?>Nenhuma migração pendente.
            <?php elseif (empty($migrationResult['errors'])): ?><?= (int)$migrationResult['ok'] ?> migração(ões) executada(s) com sucesso.
            <?php else: ?><?= (int)$migrationResult['ok'] ?> ok, <?= count($migrationResult['errors']) ?> erro(s):<br><?php foreach ($migrationResult['errors'] as $err): ?><span><?= htmlspecialchars($err) ?></span><br><?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (empty($pending)): ?>
        <p class="font-medium text-slate-700">Nenhuma migração pendente. O banco está em dia.</p>
        <?php else: ?>
        <p class="font-medium text-slate-700 mb-2"><?= count($pending) ?> migração(ões) pendente(s):</p>
        <ul class="list-disc list-inside text-sm text-slate-600 mb-4"><?php foreach ($pending as $m): ?><li><code class="bg-slate-100 px-1 rounded"><?= htmlspecialchars($m['id']) ?></code> – <?= htmlspecialchars($m['name'] ?? '') ?></li><?php endforeach; ?></ul>
        <form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Rodar migrações pendentes</button></form>
        <?php endif; ?>
    </div>
    <?php if (!empty($executedIds)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-3">Já executadas</h2>
        <ul class="text-sm text-slate-500 space-y-1"><?php foreach ($executedIds as $id): ?><li><code class="bg-slate-100 px-1 rounded"><?= htmlspecialchars($id) ?></code></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
