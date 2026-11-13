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

    <!-- FAVAICON -->
    <link rel="shortcut icon" href="./assets/icons/favicon.webp" type="image/x-icon">

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @stack('styles')
    <!--Inyección de css de las vistas -->

</head>

<body class="">

{{-- class="ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto bg-white px-6 md:px-10 pb-12 pt-6" --}}
    @yield('siderbar')
    <main id="main-content" class="@yield('main_class')">

        @yield('header')
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
