{{--
    Componente: File
    Descripción: Campo estilizado para la carga de archivos con selector de extensiones,
                 texto de ayuda y contenedor de mensaje de error.

    Parámetros:
    - label (string, opcional): Texto de la etiqueta superior.
    - id (string, requerido): Identificador único del input de archivo.
    - name (string, requerido): Nombre del campo para el formulario.
    - accept (string, opcional): Extensiones o tipos MIME permitidos (default: '.pdf,.jpg,.jpeg,.png').
    - hint (string, opcional): Texto de ayuda o instrucciones (ej: 'Máximo 5 MB').
    - required (bool, opcional): Indica si el archivo es obligatorio (default: false).
    - disabled (bool, opcional): Deshabilita el input de archivo (default: false).
--}}
<div class="space-y-1.5">
    @if (!empty($label))
        <label for="{{ $id }}" class="block text-sm font-medium text-zinc-900">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="file"
        accept="{{ $accept ?? '.pdf,.jpg,.jpeg,.png' }}"
        class="
            block w-full cursor-pointer rounded-lg border border-slate-300 bg-slate-50
            text-sm text-zinc-500 outline-none transition duration-200
            focus:bg-white focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black
            file:mr-3 file:cursor-pointer file:border-0 file:border-r file:border-slate-300
            file:bg-white file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-zinc-900
            file:transition-colors hover:file:bg-slate-100"
        {{ $required ?? false ? 'required' : '' }}
        {{ $disabled ?? false ? 'disabled' : '' }}
    />

    @if (!empty($hint))
        <p class="text-xs text-zinc-500">{{ $hint }}</p>
    @endif

    <p id="{{ $id }}-error"
       class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200"
       role="alert"></p>
</div>
