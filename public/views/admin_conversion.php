<?php
$base = \NanoCDN\base_url();
$g = $globalConv ?? \NanoCDN\ImageConverter::getGlobalConversionOptions();
$sizesText = implode("\n", array_map(fn($s) => $s['key'] ?? (($s['w'] ?? 0) . 'x' . ($s['h'] ?? 0)), $g['sizes'] ?? []));
$formatsList = $g['formats'] ?? ['webp', 'avif', 'png', 'jpeg'];
$caps = \NanoCDN\ImageConverter::getServerCapabilities();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversão global - NanoCDN</title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="admin">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="admin-breadcrumb"><a href="<?= $base ?>/admin">Tenants</a> → Conversão (global)</p>

    <div class="admin-card">
        <h1>Configuração global de conversão</h1>
        <p>Define os tamanhos e formatos disponíveis para todos os tenants. Cada tenant pode escolher um subconjunto em <strong>Editar tenant</strong>.</p>
        <?php if (!empty($saved)): ?><div class="admin-alert admin-alert-success">Configuração salva.</div><?php endif; ?>
        <?php if (!empty($convError)): ?><div class="admin-alert admin-alert-error"><?= htmlspecialchars($convError) ?></div><?php endif; ?>
        <?php if (empty($caps['conversion_available'])): ?>
        <div class="admin-alert admin-alert-error">Nenhum driver de imagem (GD/Imagick) disponível. Conversão não funcionará.</div>
        <?php endif; ?>

        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <label><input type="checkbox" name="conversion_enabled" value="1" <?= !empty($g['enabled']) ? 'checked' : '' ?>> Habilitar conversão de imagens (gerar variantes)</label>

            <label style="display:block;margin-top:1rem;"><strong>Tamanhos</strong> (um por linha, formato <code>LARGURAxALTURA</code>, ex: 1920x1080)</label>
            <textarea name="conversion_sizes" rows="8" style="width:100%;max-width:320px;font-family:monospace;"><?= htmlspecialchars($sizesText) ?></textarea>

            <p style="margin-top:1rem;"><strong>Formatos</strong> de saída:</p>
            <?php foreach (['webp', 'avif', 'png', 'jpeg'] as $fmt): ?>
            <label style="display:inline-block;margin-right:1rem;"><input type="checkbox" name="conversion_formats[]" value="<?= htmlspecialchars($fmt) ?>" <?= in_array($fmt, $formatsList, true) ? 'checked' : '' ?>> <?= htmlspecialchars($fmt) ?></label>
            <?php endforeach; ?>

            <p style="margin-top:1rem;"><button type="submit" class="admin-btn admin-btn-primary">Salvar configuração global</button></p>
        </form>
    </div>

    <div class="admin-card">
        <h2>Estado atual</h2>
        <p>Conversão: <strong><?= !empty($g['enabled']) ? 'Habilitada' : 'Desabilitada' ?></strong>.</p>
        <p>Tamanhos: <?= empty($g['sizes']) ? 'nenhum' : implode(', ', array_column($g['sizes'], 'key')) ?>.</p>
        <p>Formatos: <?= empty($g['formats']) ? 'nenhum' : implode(', ', $g['formats']) ?>.</p>
        <p>Driver disponível: <?= htmlspecialchars($caps['driver'] ?? 'nenhum') ?>.</p>
    </div>
</body>
</html>
