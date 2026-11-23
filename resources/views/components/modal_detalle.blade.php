@component('components.modal', [
    'id' => $id ?? 'modal-detalle',
    'titulo' => $titulo ?? 'Detalle',
    'subtitulo' => $subtitulo ?? '',
    'tamano' => $tamano ?? 'lg',
    'textoCancelar' => $textoCancelar ?? 'Cerrar',
])
    {!! $slot ?? '' !!}
@endcomponent
