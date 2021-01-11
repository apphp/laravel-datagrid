{!! $errors !!}
<div id="filter" class="card mb-3">
    <div class="card-header">
        <a class="pointer" onclick="$('#filter .card-body').toggleClass('collapse');$(this).find('i').toggleClass('fa-minus-square');$('#id').focus()">
            @lang('datagrid::filter.filter')
            <div class="float-right">
                <i class="fa fa-plus-square{{($mode == 'opened' || $filterFields['act'] === 'search' ? ' fa-minus-square' : '')}}" aria-hidden="true"></i>
            </div>
        </a>
    </div>
    <div class="card-body py-1'{{($mode == 'opened' || $filterFields['act'] === 'search' ? '' : ' collapse')}}">
        <form action="{{$submitRoute}}" method="GET">
            <input type="hidden" name="act" value="search">

            {!! $filterFieldsContent !!}

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <div class="mt-1">
                            <button type="submit" class="btn btn-primary btn-sm" name="search" data-remove-empties="true">
                                <i class="fa fa-search-{{(!empty($filterFields['act']) && $filterFields['act'] === 'search' ? 'minus' : 'plus')}} mr-1" aria-hidden="true"></i> @lang('datagrid::filter.search')
                            </button>
                            @if(!empty($filterFields['act']) && $filterFields['act'] === 'search')
                                <a class="btn btn-sm" href="{{$cancelRoute}}">@lang('datagrid::filter.cancel')</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>