@extends('layouts.app')

@section('title', 'Lista de Empleados')

@section('content')

    <div class="text-3xl text-center">
        <h2>
            Bienvenido a Atlas, el sistema de gestión de empleados del INCES
        </h2>
    </div>

    <div class="flex flex-col items-center justify-center p-8 bg-white rounded-xl shadow-lg max-w-lg mx-auto mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Descargar Ficha / Foto por Cédula</h2>

        <!-- Añadimos id="formCedula" para controlarlo con JS -->
        <form id="formCedula" action="/descargar-foto-cedula" method="POST" class="flex flex-col gap-4 w-full">

            <div class="flex flex-col gap-2">
                <label class="text-sm font-semibold text-gray-600">Número de Cédula:</label>
                <input id="cedulaInput" type="text" name="cedula" placeholder="Ej: 30012937"
                    class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <button type="submit"
                class="bg-[#F53003] text-white font-medium py-3 px-4 rounded-lg hover:bg-opacity-90 transition shadow-md">
                Buscar y Descargar Foto
            </button>
        </form>
    </div>
@endsection

@push('scripts')
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

            fetch('descargar-foto-cedula', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // 'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        /* tus datos */
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                });
        });

        let idEmpleado = 5;

        fetch(`/empleado/create/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    nombre: 'Nuevo Nombre',
                    id: idEmpleado,
                    apellido: 'Nuevo Apellido',
                    cedula: '12345678',
                    ciudad: 'Nueva Ciudad',
                    direccion: 'Nueva Dirección',
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
            });
    </script>
@endpush
