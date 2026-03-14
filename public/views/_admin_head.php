<?php
$faviconDataUri = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="%231a1a2e"/><text x="16" y="22" font-family="system-ui,sans-serif" font-size="18" font-weight="bold" fill="%23fff" text-anchor="middle">N</text></svg>');
?>
<link rel="icon" href="<?= $faviconDataUri ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= (function_exists('NanoCDN\base_url') ? \NanoCDN\base_url() : '') ?>/admin.css">
