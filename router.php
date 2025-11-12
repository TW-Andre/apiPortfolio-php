<?php
// router.php
if (preg_match('/^\/api\//', $_SERVER['REQUEST_URI'])) {
    // Extrai o caminho após /api/
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/api', '', $path); // Remove /api
    $_SERVER['PATH_INFO'] = $path ?: '/';

    require 'api.php';
} else {
    return false;
}
?>