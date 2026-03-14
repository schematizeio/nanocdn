<?php
$base = \NanoCDN\base_url();
$global = $globalConversion ?? \NanoCDN\ImageConverter::getGlobalConversionOptions();
$hasConversionBase = !empty($global['enabled']) && !empty($global['sizes']) && !empty($global['formats']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar tenant - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Novo tenant</p>
    <div class="admin-card">
        <h1>Criar tenant</h1>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <label>Nome <input type="text" name="name" required placeholder="Ex: Meu site"></label>
            <label style="margin-top:1rem;"><input type="checkbox" name="conversion_enabled" value="1" id="conv-enable"> Habilitar conversão de imagens</label>
            <?php if ($hasConversionBase): ?>
            <fieldset style="margin-top:1rem;padding:1rem;border:1px solid #ddd;border-radius:4px;">
                <legend>Tamanhos e formatos (subconjunto do global; vazio = usar todos)</legend>
                <p><strong>Tamanhos:</strong>
                <?php foreach ($global['sizes'] as $s): ?>
                <label style="display:inline-block;margin-right:0.75rem;"><input type="checkbox" name="conversion_sizes[]" value="<?= htmlspecialchars($s['key']) ?>"> <?= htmlspecialchars($s['key']) ?></label>
                <?php endforeach; ?>
                </p>
                <p><strong>Formatos:</strong>
                <?php foreach ($global['formats'] as $f): ?>
                <label style="display:inline-block;margin-right:0.75rem;"><input type="checkbox" name="conversion_formats[]" value="<?= htmlspecialchars($f) ?>"> <?= htmlspecialchars($f) ?></label>
                <?php endforeach; ?>
                </p>
            </fieldset>
            <?php elseif (empty($global['enabled'])): ?>
            <p class="admin-alert admin-alert-info" style="margin-top:0.5rem;">Conversão está desabilitada na configuração global. Habilite em <code>config</code> para este tenant poder usar.</p>
            <?php endif; ?>
            <button type="submit" class="admin-btn admin-btn-primary">Criar tenant</button>
        </form>
    </div>
</body>
</html>
