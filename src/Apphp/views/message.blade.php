@if($message)
    <div class="alert alert-warning{{($important ? ' alert-important' : '')}}">{!! $message !!}</div>
@endif