<?php
$base = \NanoCDN\base_url();
$invites = $invites ?? [];
$inviteError = $inviteError ?? '';
$inviteSuccess = $inviteSuccess ?? '';
$newLink = $newLink ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convites - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Convites</p>

    <div class="admin-card">
        <h1>Convites de cadastro</h1>
        <p>Gere um link de uso único para alguém criar conta no painel, ou envie um convite por e-mail (opcional).</p>
        <?php if ($inviteError): ?><div class="admin-alert admin-alert-error"><?= htmlspecialchars($inviteError) ?></div><?php endif; ?>
        <?php if ($inviteSuccess): ?><div class="admin-alert admin-alert-success"><?= htmlspecialchars($inviteSuccess) ?></div><?php endif; ?>
        <?php if ($newLink): ?>
        <div class="admin-alert admin-alert-info">
            <strong>Link de convite (copie e envie):</strong><br>
            <input type="text" readonly value="<?= htmlspecialchars($newLink) ?>" style="width:100%;max-width:500px;padding:0.5rem;margin-top:0.5rem;font-size:0.9rem;" id="invite-link" onclick="this.select();">
        </div>
        <?php endif; ?>

        <form method="post" class="admin-form" style="margin-bottom:2rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <input type="hidden" name="action" value="create">
            <label>E-mail do convidado (opcional; deixe em branco para só gerar link) <input type="email" name="email" placeholder="convidado@exemplo.com"></label>
            <button type="submit" class="admin-btn admin-btn-primary">Gerar link de convite</button>
        </form>

        <h2>Convites recentes</h2>
        <table class="admin-table">
            <thead>
                <tr><th>E-mail</th><th>Criado em</th><th>Criado por</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($invites as $i): ?>
                <tr>
                    <td><?= htmlspecialchars($i['email'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($i['created_at'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['created_by_name'] ?? '—') ?></td>
                    <td><?= !empty($i['used_at']) ? 'Usado em ' . htmlspecialchars($i['used_at']) : 'Pendente' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($invites)): ?><p class="admin-empty">Nenhum convite ainda.</p><?php endif; ?>
    </div>
</body>
</html>
