<?php
if (\NanoCDN\Auth::check()) {
    $navBase = \NanoCDN\base_url();
?>
<div class="admin-layout">
<aside class="admin-sidebar">
    <a href="<?= $navBase ?>/admin" class="admin-logo"><?php
        $logoUrl = \NanoCDN\app_logo_url();
        if ($logoUrl !== ''): ?><img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars(\NanoCDN\app_name()) ?>" class="admin-logo-img"><?php
        else: ?><?= htmlspecialchars(\NanoCDN\app_name()) ?><?php endif; ?></a>
    <nav class="admin-nav">
        <div class="admin-nav-group">
            <span class="admin-nav-group-title">CDN</span>
            <a href="<?= $navBase ?>/admin">Tenants</a>
            <a href="<?= $navBase ?>/admin/tenants/new">Criar tenant</a>
        </div>
        <div class="admin-nav-group">
            <span class="admin-nav-group-title">Sistema</span>
            <a href="<?= $navBase ?>/admin/conversion">Conversão</a>
            <a href="<?= $navBase ?>/admin/check">Checker</a>
            <a href="<?= $navBase ?>/admin/review">Revisão</a>
        </div>
        <div class="admin-nav-group">
            <span class="admin-nav-group-title">Painel</span>
            <a href="<?= $navBase ?>/admin/settings">Configurações</a>
            <a href="<?= $navBase ?>/admin/users">Usuários</a>
            <a href="<?= $navBase ?>/admin/invites">Convites</a>
            <a href="<?= $navBase ?>/admin/password">Senha</a>
        </div>
        <div class="admin-nav-group">
            <span class="admin-nav-group-title">Manutenção</span>
            <a href="<?= $navBase ?>/admin/update">Atualizar</a>
            <a href="<?= $navBase ?>/admin/migrations">Migrações</a>
        </div>
    </nav>
    <div class="admin-sidebar-footer">
        <a href="<?= $navBase ?>/admin/logout" class="admin-nav-logout">Sair</a>
    </div>
</aside>
<main class="admin-main">
<?php } ?>
