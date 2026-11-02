<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Atlas - INCES')</title>
    <!-- Vite Client & Styles para tu entorno local FlightPHP -->
    <script type="module" src="http://localhost:5173/@vite/client"></script>
    <link rel="stylesheet" href="http://localhost:5173/resources/css/input.css">
    <!-- Estilos globales de la aplicacion -->
    <link rel="stylesheet" href="/css/app.css">
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
document.getElementById('formCedula').addEventListener('submit', function(event) {
    // 1. Evitamos que el formulario recargue la página por defecto
    event.preventDefault();

    // 2. Capturamos el valor del input de la cédula
    const cedulaValor = document.getElementById('cedulaInput').value;

    console.log("Enviando cédula:", cedulaValor);

    // 3. Realizamos la petición POST usando fetch con FormData (para que $_POST lo reciba tal cual)
    const formData = new FormData();
    formData.append('cedula', cedulaValor);

    fetch('/descargar-foto-cedula', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Como tu ruta hace un var_dump y un exit, la respuesta llegará como texto
        return response.text();
    })
    .then(data => {
        console.log("Respuesta del servidor (var_dump):", data);
        alert("¡Petición enviada con éxito! Revisa la terminal para ver el var_dump.");
    })
    .catch(error => {
        console.error('Error en la petición:', error);
    });
});
</script>
</body>



</html>
