<?php
if (\NanoCDN\Auth::check()) {
    $navBase = \NanoCDN\base_url();
?>
<div class="flex min-h-screen bg-slate-100">
  <aside class="w-60 min-w-[15rem] bg-sidebar text-white flex flex-col shrink-0">
    <a href="<?= $navBase ?>/admin" class="flex items-center gap-2 px-5 py-4 border-b border-slate-600/50 font-semibold text-white hover:bg-sidebarhover transition-colors">
      <?php
      $logoUrl = \NanoCDN\app_logo_url();
      if ($logoUrl !== ''): ?>
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars(\NanoCDN\app_name()) ?>" class="h-8 max-w-[120px] object-contain">
      <?php else: ?>
        <?= htmlspecialchars(\NanoCDN\app_name()) ?>
      <?php endif; ?>
    </a>
    <nav class="flex-1 overflow-y-auto py-3">
      <div class="mb-2">
        <p class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-400">CDN</p>
        <a href="<?= $navBase ?>/admin" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Tenants</a>
        <a href="<?= $navBase ?>/admin/tenants/new" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Criar tenant</a>
      </div>
      <div class="mb-2">
        <p class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-400">Sistema</p>
        <a href="<?= $navBase ?>/admin/conversion" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Conversão</a>
        <a href="<?= $navBase ?>/admin/check" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Checker</a>
        <a href="<?= $navBase ?>/admin/review" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Revisão</a>
      </div>
      <div class="mb-2">
        <p class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-400">Painel</p>
        <a href="<?= $navBase ?>/admin/settings" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Configurações</a>
        <a href="<?= $navBase ?>/admin/users" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Usuários</a>
        <a href="<?= $navBase ?>/admin/invites" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Convites</a>
        <a href="<?= $navBase ?>/admin/password" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Senha</a>
      </div>
      <div class="mb-2">
        <p class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-400">Manutenção</p>
        <a href="<?= $navBase ?>/admin/update" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Atualizar</a>
        <a href="<?= $navBase ?>/admin/migrations" class="block px-4 py-2 text-sm text-slate-300 hover:bg-sidebarhover hover:text-white transition-colors">Migrações</a>
      </div>
    </nav>
    <div class="border-t border-slate-600/50 px-4 py-3">
      <a href="<?= $navBase ?>/admin/logout" class="text-sm text-slate-400 hover:text-white transition-colors">Sair</a>
    </div>
  </aside>
  <main class="flex-1 p-6 md:p-8 overflow-x-auto">
<?php } ?>
