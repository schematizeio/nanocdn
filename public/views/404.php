<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="text-center">
        <h1 class="text-xl font-semibold text-slate-600 mb-2">404 – Página não encontrada</h1>
        <p><a href="<?= \NanoCDN\base_url('admin') ?>" class="text-blue-600 hover:underline font-medium">Ir para o painel</a></p>
    </div>
</body>
</html>
