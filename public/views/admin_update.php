<?php $base = \NanoCDN\base_url(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">
    <?php require __DIR__ . '/_admin_nav.php'; ?>
    <p class="text-sm text-slate-500 mb-4"><a href="<?= $base ?>/admin" class="text-blue-600 hover:underline">Tenants</a> → Atualizar</p>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Atualizar do repositório</h1>
        <p class="text-slate-600 text-sm mb-4">Puxa alterações de <a href="https://github.com/schematizeio/nanocdn" target="_blank" rel="noopener" class="text-blue-600 hover:underline">github.com/schematizeio/nanocdn</a>. Só funciona se instalado via <code class="bg-slate-100 px-1 rounded">git clone</code>. Depois, <a href="<?= $base ?>/admin/migrations" class="text-blue-600 hover:underline">rode as migrações</a> se houver pendentes.</p>
        <?php if (!empty($error)): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200"><?= htmlspecialchars($error) ?></div>
        <?php if (!empty($manualUpdateCommand)): ?>
        <p class="text-sm font-medium text-slate-700 mb-1">Comando:</p>
        <pre class="bg-slate-800 text-slate-100 p-3 rounded-lg font-mono text-sm overflow-x-auto mb-2" id="manual-update-cmd"><?= htmlspecialchars($manualUpdateCommand) ?></pre>
        <button type="button" class="px-3 py-1.5 bg-slate-200 rounded text-sm font-medium hover:bg-slate-300" id="copy-update-cmd">Copiar comando</button>
        <script>document.getElementById('copy-update-cmd').onclick=function(){var el=document.getElementById('manual-update-cmd');var btn=document.getElementById('copy-update-cmd');navigator.clipboard.writeText(el.innerText).then(function(){btn.textContent='Copiado!';setTimeout(function(){btn.textContent='Copiar comando';},1500);});};</script>
        <?php endif; ?>
        <?php endif; ?>
        <?php if (isset($output) && $output !== ''): ?><pre class="bg-slate-800 text-slate-100 p-3 rounded-lg font-mono text-sm overflow-x-auto mb-4"><?= htmlspecialchars($output) ?></pre><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">Atualizar (git pull)</button></form>
    </div>
    <?php require __DIR__ . '/_admin_footer.php'; ?>
</body>
</html>
