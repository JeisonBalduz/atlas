@component('components.modal', [
    'id' => $id ?? 'modal-confirmacion',
    'titulo' => $titulo ?? 'Confirmar acción',
    'subtitulo' => $subtitulo ?? '',
    'tamano' => $tamano ?? 'sm',
    'textoCancelar' => $textoCancelar ?? 'Cancelar',
    'textoConfirmar' => $textoConfirmar ?? 'Confirmar',
    'varianteConfirmar' => $varianteConfirmar ?? 'red',
])
    {!! $slot ?? '' !!}
@endcomponent
