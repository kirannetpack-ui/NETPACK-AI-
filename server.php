<?php

// Shared router for Laravel's local server and the configured serverless bridge.
// Database, encryption keys, sessions and cache must come from deployment config.
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$publicRoot = realpath(__DIR__.'/public');
$asset = realpath(__DIR__.'/public'.$uri);
if (PHP_SAPI === 'cli-server' && $uri !== '/' && $asset !== false
    && str_starts_with($asset, $publicRoot.DIRECTORY_SEPARATOR) && is_file($asset)) {
    return false;
}

require __DIR__.'/public/index.php';
