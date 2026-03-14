<?php
if (\NanoCDN\Auth::check()) {
    $navBase = \NanoCDN\base_url();
?>
<header class="admin-header">
    <div class="admin-header-inner">
        <a href="<?= $navBase ?>/admin" class="admin-logo">NanoCDN</a>
        <nav class="admin-nav">
            <a href="<?= $navBase ?>/admin">Tenants</a>
            <a href="<?= $navBase ?>/admin/tenants/new">Criar tenant</a>
            <a href="<?= $navBase ?>/admin/check">Checker</a>
            <a href="<?= $navBase ?>/admin/conversion">Conversão</a>
            <a href="<?= $navBase ?>/admin/review">Revisão</a>
            <a href="<?= $navBase ?>/admin/password">Senha</a>
            <a href="<?= $navBase ?>/admin/update">Atualizar</a>
            <a href="<?= $navBase ?>/admin/migrations">Migrações</a>
            <a href="<?= $navBase ?>/admin/logout">Sair</a>
        </nav>
    </div>
</header>
<main class="admin-main">
<?php } ?>
