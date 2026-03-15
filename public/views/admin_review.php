<?php $r = $review ?? []; $base = \NanoCDN\base_url(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão do sistema - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Revisão do sistema</p>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-2">Revisão do sistema</h1>
        <p class="text-slate-600 text-sm">Visão geral do ambiente e da instalação <?= htmlspecialchars(\NanoCDN\app_name()) ?>.</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-3">Aplicação</h2>
        <table class="w-full text-sm border-collapse"><tbody class="border border-slate-200">
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50 w-40">Versão</th><td class="py-3 px-3"><?= htmlspecialchars($r['app_version'] ?? '') ?></td></tr>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Ambiente</th><td class="py-3 px-3"><?= htmlspecialchars($r['env'] ?? '') ?></td></tr>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">URL base</th><td class="py-3 px-3 font-mono text-sm"><?= htmlspecialchars($r['base_url'] ?? '') ?></td></tr>
            <tr><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">PHP</th><td class="py-3 px-3"><?= htmlspecialchars($r['php_version'] ?? PHP_VERSION) ?></td></tr>
        </tbody></table>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-3">Banco de dados</h2>
        <table class="w-full text-sm border-collapse"><tbody class="border border-slate-200">
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Conexão</th><td class="py-3 px-3 <?= !empty($r['db_ok']) ? 'text-green-600' : 'text-red-600' ?>"><?= !empty($r['db_ok']) ? 'Conectado' : 'Erro' ?></td></tr>
            <?php if (!empty($r['db_error'])): ?><tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Erro</th><td class="py-3 px-3"><?= htmlspecialchars($r['db_error']) ?></td></tr><?php endif; ?>
            <?php if (!empty($r['db_ok'])): ?>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Tenants</th><td class="py-3 px-3"><?= (int)($r['tenants_count'] ?? 0) ?></td></tr>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Arquivos</th><td class="py-3 px-3"><?= (int)($r['files_count'] ?? 0) ?></td></tr>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">API Keys</th><td class="py-3 px-3"><?= (int)($r['api_keys_count'] ?? 0) ?></td></tr>
            <?php endif; ?>
        </tbody></table>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-3">Storage</h2>
        <table class="w-full text-sm border-collapse"><tbody class="border border-slate-200">
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Diretório</th><td class="py-3 px-3 font-mono text-xs"><?= htmlspecialchars($r['storage_path'] ?? '') ?></td></tr>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Gravável</th><td class="py-3 px-3 <?= !empty($r['storage_writable']) ? 'text-green-600' : 'text-red-600' ?>"><?= !empty($r['storage_writable']) ? 'Sim' : 'Não' ?></td></tr>
            <?php if (isset($r['storage_free']) && $r['storage_free'] !== null): ?><tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Espaço livre</th><td class="py-3 px-3"><?= (float)$r['storage_free'] ?> MB</td></tr><?php endif; ?>
        </tbody></table>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-3">Conversão de imagens</h2>
        <table class="w-full text-sm border-collapse"><tbody class="border border-slate-200">
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">GD</th><td class="py-3 px-3"><?= !empty($r['caps']['gd']) ? 'Sim' : 'Não' ?></td></tr>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Imagick</th><td class="py-3 px-3"><?= !empty($r['caps']['imagick']) ? 'Sim' : 'Não' ?></td></tr>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">WebP</th><td class="py-3 px-3"><?= !empty($r['caps']['webp']) ? 'Sim' : 'Não' ?></td></tr>
            <tr class="border-b border-slate-200"><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">AVIF</th><td class="py-3 px-3"><?= !empty($r['caps']['avif']) ? 'Sim' : 'Não' ?></td></tr>
            <tr><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">Driver em uso</th><td class="py-3 px-3"><?= htmlspecialchars($r['caps']['driver'] ?? 'Nenhum') ?></td></tr>
        </tbody></table>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-3">Segurança e rede</h2>
        <table class="w-full text-sm border-collapse"><tbody class="border border-slate-200">
            <tr><th class="text-left py-3 px-3 font-medium text-slate-600 bg-slate-50">HTTPS</th><td class="py-3 px-3 <?= !empty($r['https']) ? 'text-green-600' : 'text-amber-600' ?>"><?= !empty($r['https']) ? 'Sim' : 'Não (recomenda-se HTTPS em produção)' ?></td></tr>
        </tbody></table>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
