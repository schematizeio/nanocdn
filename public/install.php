<?php
/**
 * Redireciona para o instalador na raiz do projeto.
 * Use quando o document root for public/ (ex.: /install.php).
 */
$base = dirname(__DIR__);
if (is_file($base . '/install.php')) {
    require $base . '/install.php';
} else {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Install script not found. Place install.php in the project root.';
}
