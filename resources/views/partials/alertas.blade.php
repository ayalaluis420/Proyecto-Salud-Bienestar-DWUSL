{{-- Visible text is a fallback if JavaScript is unavailable; SweetAlert2 reads it as text. --}}
@foreach (['success'=>['success','Operación completada'],'error'=>['error','No se pudo completar'],'status'=>['info','Información']] as $clave=>$tipo)
    @if (session($clave))
    <div class="alert alert-{{ $clave === 'error' ? 'danger' : ($clave === 'status' ? 'info' : 'success') }}" role="alert" data-panel-alert="{{ $tipo[0] }}" data-title="{{ $tipo[1] }}">{{ session($clave) }}</div>
    @endif
@endforeach
@if ($errors->any())
<div class="alert alert-danger" role="alert" data-panel-alert="error" data-title="Revisa los datos">{{ implode(' ', $errors->all()) }}</div>
@endif
