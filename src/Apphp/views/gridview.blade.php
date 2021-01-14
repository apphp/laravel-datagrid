@if($responsive)
<div class="table-responsive">
@endif
    <table class="table table-bordered table-striped">
        {{$headers}}}
        {{$rows}}}
    </table>
@if($responsive)
</div>
@endif