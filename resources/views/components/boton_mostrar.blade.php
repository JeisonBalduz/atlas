{{--
    Componente: Botón Mostrar
    Descripción: Variante preconfigurada de boton_accion para visualizar detalles en modal o vista.
--}}
@component('components.boton_accion', [
    'accion' => 'mostrar',
    'id' => $id ?? '',
    'etiqueta' => $etiqueta ?? 'Mostrar',
    'icono' => $icono ?? 'eye',
    'variante' => 'sky',
    'href' => $href ?? null,
    'soloIcono' => $soloIcono ?? false,
])
@endcomponent
