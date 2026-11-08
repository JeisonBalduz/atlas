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
@endpush