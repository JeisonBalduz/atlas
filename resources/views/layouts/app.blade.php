<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Atlas - INCES')</title>
    <!-- Vite Client & Styles para tu entorno local FlightPHP -->
    <script type="module" src="http://localhost:5173/@vite/client"></script>

    <script src="./lib/js/lucideIcons/lucide.min.js"></script>

    <!-- Estilos globales de la aplicacion -->
    <link rel="stylesheet" href="./css/app.css">
    <link rel="stylesheet" href="./css/output.css">




    @stack('styles') <!--Inyección de css de las vistas -->

</head>

<body class="">
    <!-- Inyección de contenido -->
    <main class="">
        @yield('content')
    </main>

    <!-- Inyección de scripts -->
    @stack('scripts')

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>

</html>
