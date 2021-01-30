<tbody>
@foreach ($records as $record)
<tr>
    @foreach ($columns as $key => $column)
        @php
            /** @var array $column */
            $columnKey = is_array($column) ? $key : $column;
            $callback = (!empty($column['callback']) && (is_callable($column['callback']) || $column['callback'] instanceof Closure)) ? $column['callback'] : null;
            $class = !empty($column['class']) ? ' class="'.$column['class'].'"' : '';
        @endphp
        <td{!! $class !!}>
            @if (!empty($callback))
                {!! $callback($record) !!}
            @else
                {!! $record[$columnKey] ?? '' !!}
            @endif
        </td>
    @endforeach
</tr>
@endforeach
</tbody>

