<?php
$base = \NanoCDN\base_url();
$g = $globalConv ?? \NanoCDN\ImageConverter::getGlobalConversionOptions();
$sizesText = implode("\n", array_map(fn($s) => $s['key'] ?? (($s['w'] ?? 0) . 'x' . ($s['h'] ?? 0)), $g['sizes'] ?? []));
$formatsList = $g['formats'] ?? ['webp', 'avif', 'png', 'jpeg'];
$quality = (int) ($g['quality'] ?? 85);
$quality = max(1, min(100, $quality));
$defaultSizesText = implode("\n", array_map(fn($s) => $s['key'], \NanoCDN\ImageConverter::getDefaultSizes()));
$caps = \NanoCDN\ImageConverter::getServerCapabilities();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversão global - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
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

            <label style="display:block;margin-top:1rem;"><strong>Tamanhos</strong> (um por linha, <code>LARGURAxALTURA</code>; só são gerados se a imagem for &ge; alvo; recorte central, sem esticar)</label>
            <textarea name="conversion_sizes" id="conversion_sizes" rows="10" style="width:100%;max-width:400px;font-family:monospace;"><?= htmlspecialchars($sizesText) ?></textarea>
            <p><button type="button" class="admin-btn admin-btn-sm" id="btn-default-sizes">Usar tamanhos padrão</button> <span class="admin-muted">(1920×1080, 1080×1920, 1024×1024 × escalas x1;x2;x0,75;x0,5;x0,25;x0,125)</span></p>
            <script>document.getElementById('btn-default-sizes').onclick=function(){document.getElementById('conversion_sizes').value=<?= json_encode($defaultSizesText) ?>;};</script>

            <label style="display:block;margin-top:1rem;"><strong>Compressão / qualidade</strong> (1–100; afeta WebP, AVIF, JPEG)</label>
            <input type="number" name="conversion_quality" min="1" max="100" value="<?= (int)$quality ?>" style="width:80px;"> <span class="admin-muted">Recomendado: 80–90</span>

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
        <p>Qualidade: <?= (int)($g['quality'] ?? 85) ?>.</p>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
