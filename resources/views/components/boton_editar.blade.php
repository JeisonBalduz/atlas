{{--
    Componente: Botón Editar
    Descripción: Variante preconfigurada de boton_accion para editar un registro.
--}}
@component('components.boton_accion', [
    'accion' => 'editar',
    'id' => $id ?? '',
    'etiqueta' => $etiqueta ?? 'Editar',
    'icono' => $icono ?? 'pencil',
    'variante' => 'yellow',
    'href' => $href ?? null,
    'soloIcono' => $soloIcono ?? false,
])
@endcomponent
