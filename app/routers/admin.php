<?php

// Mapear la función de error personalizada
Flight::map('errorPage', function(string $titulo, string $mensaje, int $codigo) {
    Flight::response()->status($codigo);
        Flight::render('layouts/header'); // Renderizar la vista del encabezado
        Flight::render('layouts/errorPage', [
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'codigo' => $codigo
        ]); // Renderizar la vista de la página de error con los datos proporcionados
    Flight::render('layouts/footer'); // Renderizar la vista del pie de página
});

// Mapear notFound utilizando la función anterior ya registrada
Flight::map('notFound', function() {
    Flight::errorPage(
        'Página no encontrada',
        'La página que estás buscando no existe o ha sido eliminada.',
        404
    );
});

// Manejador personalizado para errores 500 (Error interno del servidor)
Flight::register('error', Exception::class, [], function(Exception $ex) {
    Flight::json([
        'error' => 'Error interno del servidor',
        'detalles' => $ex->getMessage()
    ], 500);
});

//Descargar contenido de los archivos
Flight::route('/descargar/@carpeta/@archivo', function ($carpeta, $archivo) {
    // Definimos la ruta real y segura hacia la carpeta storage en la raíz
    $rutaReal = __DIR__ . "/../../storage/{$carpeta}/{$archivo}";

    // Validamos si el archivo realmente existe en el disco
    if (file_exists($rutaReal) && is_file($rutaReal)) {
        // Obtenemos el tipo MIME correcto (imagen, pdf, txt, etc.)
        $mimeType = mime_content_type($rutaReal);

        // Configuramos las cabeceras HTTP
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($rutaReal));

        // Si quieres que se descargue automáticamente en lugar de solo visualizarse en el navegador,
        // descomenta la línea de abajo:
        // header('Content-Disposition: attachment; filename="' . $archivo . '"');

        // Leemos el archivo y lo enviamos a la salida
        readfile($rutaReal);
        exit;
    } else {
        // Si no existe, lanzamos un error 404 limpio
        Flight::halt(404, 'El archivo solicitado no existe en el sistema.');
    }
});


//Descargar contenido de los node
Flight::route('GET /node_modules/@path...', function ($path) {
    // Computa viam absolutam ad radicem projecti
    $file = __DIR__ . '/../../node_modules/' . $path;

    if (!file_exists($file) || is_dir($file)) {
        Flight::notFound();
        return;
    }

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext === 'js') {
        header('Content-Type: application/javascript');
    } elseif ($ext === 'css') {
        header('Content-Type: text/css');
    } else {
        header('Content-Type: ' . (mime_content_type($file) ?: 'text/plain'));
    }

    readfile($file);
    exit;
});

Flight::route('GET /app/lib/@path...', function ($path) {
    // Computa viam absolutam ad radicem projecti
    $file = __DIR__ . '/../../app/lib/' . $path;

    if (!file_exists($file) || is_dir($file)) {
        Flight::notFound();
        return;
    }

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext === 'js') {
        header('Content-Type: application/javascript');
    } elseif ($ext === 'css') {
        header('Content-Type: text/css');
    } else {
        header('Content-Type: ' . (mime_content_type($file) ?: 'text/plain'));
    }

    readfile($file);
    exit;
});