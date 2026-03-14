<?php
$base = \NanoCDN\base_url();
$pending = $pending ?? [];
$executedIds = $executedIds ?? [];
$migrationResult = $migrationResult ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrações - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Migrações</p>

    <div class="admin-card">
        <h1>Migrações do banco</h1>
        <p>Execute as migrações pendentes após atualizar o código (git pull). Cada migração roda apenas uma vez.</p>

        <?php if ($migrationResult !== null): ?>
        <div class="admin-alert <?= empty($migrationResult['errors']) ? 'admin-alert-success' : 'admin-alert-error' ?>">
            <?php if ($migrationResult['run'] === 0): ?>
            Nenhuma migração pendente.
            <?php elseif (empty($migrationResult['errors'])): ?>
            <?= (int) $migrationResult['ok'] ?> migração(ões) executada(s) com sucesso.
            <?php else: ?>
            <?= (int) $migrationResult['ok'] ?> ok, <?= count($migrationResult['errors']) ?> erro(s):<br>
            <?php foreach ($migrationResult['errors'] as $err): ?>
            <span><?= htmlspecialchars($err) ?></span><br>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($pending)): ?>
        <p><strong>Nenhuma migração pendente.</strong> O banco está em dia.</p>
        <?php else: ?>
        <p><strong><?= count($pending) ?> migração(ões) pendente(s):</strong></p>
        <ul>
            <?php foreach ($pending as $m): ?>
            <li><code><?= htmlspecialchars($m['id']) ?></code> – <?= htmlspecialchars($m['name'] ?? '') ?></li>
            <?php endforeach; ?>
        </ul>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <button type="submit" class="admin-btn admin-btn-primary">Rodar migrações pendentes</button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (!empty($executedIds)): ?>
    <div class="admin-card">
        <h2>Já executadas</h2>
        <ul style="font-size:0.9rem;color:#666;">
            <?php foreach ($executedIds as $id): ?>
            <li><code><?= htmlspecialchars($id) ?></code></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</body>
</html>
