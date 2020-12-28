<div class="pt-1 d-flex flex-fill row">
    <div class="flex-fill col-12 col-lg-8">
        {{$links}}
    </div>
    <div class="flex-fill text-lg-right col-12 col-lg-4">
        Shows: {{$paginationFields['fromRecord']}} - {{$paginationFields['toRecord']}}
        from {{$total}}
    </div>
</div>