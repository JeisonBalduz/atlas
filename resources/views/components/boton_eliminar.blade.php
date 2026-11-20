{{--
    Componente: Botón Eliminar
    Descripción: Variante preconfigurada de boton_accion para eliminar o desactivar un registro.
--}}
@component('components.boton_accion', [
    'accion' => 'eliminar',
    'id' => $id ?? '',
    'etiqueta' => $etiqueta ?? 'Eliminar',
    'icono' => $icono ?? 'trash-2',
    'variante' => 'red',
    'href' => $href ?? null,
    'soloIcono' => $soloIcono ?? false,
])
@endcomponent
