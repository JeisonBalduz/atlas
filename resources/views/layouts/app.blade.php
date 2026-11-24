<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS | @yield('titulo', 'Sistema Atlas - INCES')</title>
    @if (!empty($vite_activo))
    {{-- Vite Client solo cuando existe public/hot --}}
    <script type="module" src="http://localhost:5173/@vite/client"></script>
    @endif
    <script defer src="/lib/js/lucideIcons/lucide.min.js"></script>
    {{-- Estilos globales de la aplicacion --}}
    {{-- <link rel="stylesheet" href="/lib/vanilla-calendar-pro/core.css"> --}}
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/output.css">
    {{-- FAVICON --}}
    <link rel="shortcut icon" href="/assets/icons/favicon.webp" type="image/x-icon">
    
    @stack('styles')
    {{-- Inyección de css de las vistas --}}
</head>
<body class="">
    @yield('menu_lateral')
    <main id="main-content" class="@yield('main_class')">
        @yield('header')
        @yield('contenido')
    </main>
    {{-- Inyección de scripts --}}
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>
