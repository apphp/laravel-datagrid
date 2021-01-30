<thead>
<tr>
@foreach ($columns as $key => $column)
    @php
        $title = ! empty($column['title']) ? $column['title'] : $column;
        $width = ! empty($column['width']) ? ' width="'.$column['width'].'"' : '';
        $class = ! empty($column['headClass']) ? ' class="'.$column['headClass'].'"' : '';
        /** @var bool $sortingEnabled */
        $isSortable = isset($column['sortable']) ? ($column['sortable'] ? true : false) : $sortingEnabled;
        /** @var string $sort */
        /** @var string $direction */
        $sortDir = ($sort === $key) ? ($direction == 'asc' ? 'desc' : 'asc') : '';
    @endphp

    <th {!! $width.$class !!}>
        @if ($sortingEnabled && $isSortable)
            <a href="{!! $currentURL.(strpos($currentURL, '?') === false ? '?' : '&').'sort='.$key.'&direction='.($sortDir ? $sortDir : 'asc') !!}">
                {!! $title !!}
            </a>
            <i class="fa fa-sort{{($sortDir ? '-'.$sortDir : '')}}"></i>
        @else
            {!! $title !!}
        @endif
    </th>
@endforeach
</tr>
</thead>