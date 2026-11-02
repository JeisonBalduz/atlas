<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Si el archivo estático (CSS, JS, imágenes, fuentes) existe en public, sírvelo directo
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Capturar datos de la petición actual
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$time = date('H:i:s');

// Códigos ANSI para las etiquetas con fondo estilo Laravel
$reset = "\033[0m";
$gray = "\033[90m";

if ($method === 'GET') {
    $badge = "\033[44m\033[37m  GET  {$reset}"; // Fondo azul
} elseif ($method === 'POST') {
    $badge = "\033[42m\033[30m  POST {$reset}"; // Fondo verde
} else {
    $badge = "\033[43m\033[30m {$method} {$reset}"; // Fondo amarillo para otros
}

// Ocultar ruido innecesario (como Tracy Debugger o favicon)
if (!str_contains($path, '_tracy_bar') && !str_contains($path, 'favicon.ico')) {
    // MAGIA AQUÍ: php://stderr envía el log ÚNICAMENTE a la terminal y NO al navegador
    $log = "{$gray}[{$time}]{$reset} {$badge} {$path}\n";
    file_put_contents('php://stderr', $log);
}

// Cargar la aplicación (que a su vez llamará a tu enrutador en app/router/)
require_once dirname(__DIR__, 2) . '/public/index.php';