<?php
if (\NanoCDN\Auth::check()) {
    $navBase = \NanoCDN\base_url();
?>
<nav style="display:flex;gap:1rem;align-items:center;margin-bottom:1rem;flex-wrap:wrap;">
    <strong>NanoCDN</strong>
    <a href="<?= $navBase ?>/admin" style="color:#06c;">Tenants</a>
    <a href="<?= $navBase ?>/admin/check" style="color:#06c;">Checker</a>
    <a href="<?= $navBase ?>/admin/password" style="color:#06c;">Alterar senha</a>
    <a href="<?= $navBase ?>/admin/update" style="color:#06c;">Atualizar</a>
    <a href="<?= $navBase ?>/admin/logout" style="color:#06c;">Sair</a>
</nav>
<?php } ?>
