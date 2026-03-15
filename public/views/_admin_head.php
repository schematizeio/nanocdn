<?php
$letter = mb_substr(\NanoCDN\app_name(), 0, 1);
if ($letter === '') $letter = 'P';
$faviconDataUri = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="%231a1a2e"/><text x="16" y="22" font-family="system-ui,sans-serif" font-size="18" font-weight="bold" fill="%23fff" text-anchor="middle">' . htmlspecialchars($letter) . '</text></svg>');
$base = function_exists('NanoCDN\base_url') ? \NanoCDN\base_url() : '';
?>
<link rel="icon" href="<?= $faviconDataUri ?>" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          sidebar: '#0f172a',
          sidebarhover: '#1e293b',
        }
      }
    }
  }
</script>
<link rel="stylesheet" href="<?= $base ?>/admin.css">
