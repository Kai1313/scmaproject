@foreach ($data as $item)
@if(isset($item->header))
<tr>
    @if(isset($item->child))
        <td>
            @php
                echo str_repeat('&nbsp;', $space);
            @endphp
            <b style="font-size:{{ $fontSize }}px">{{$item->header}}</b>
        </td>
        <td></td>
    @else
        <td style="font-size:{{ $fontSize }}px">
            @php
                echo str_repeat('&nbsp;', $space);
            @endphp
            {{$item->header}}
        </td>
        <td style="font-size:{{ $fontSize }}px; text-align:right;" >
            Rp {{number_format($item->total, 2, ",", ".")}}
        </td>
    @endif
</tr>
@if(isset($item->child))
    @include('accounting.report.balance.balance-list',['data' => $item->child, 'fontSize' => ($fontSize - 1), 'space' => ($space + 1)])

    @if(isset($item->child))
        <tr>
            <td>
                @php
                    echo str_repeat('&nbsp;', $space);
                @endphp
                <b style="font-size:{{ $fontSize }}px">Total</b>
            </td>
            <td style="text-align:right;">
                <b style="font-size:{{ $fontSize }}px">Rp {{number_format($item->total, 2, ",", ".")}}</b>
            </td>
        </tr>
    @else
    @endif
@endif
@else
<tr>
    @if(isset($item['child']))
        <td>
            @php
                echo str_repeat('&nbsp;', $space);
            @endphp
            <b style="font-size:{{ $fontSize }}px">{{$item['header']}}</b>
        </td>
        <td></td>
    @else
        <td style="font-size:{{ $fontSize }}px">
            @php
                echo str_repeat('&nbsp;', $space);
            @endphp
            {{$item['header']}}
        </td>
        <td style="font-size:{{ $fontSize }}px; text-align:right;" >
            Rp {{number_format($item['total'], 2, ",", ".")}}
        </td>
    @endif
</tr>
@if(isset($item['child']))
    @include('accounting.report.balance.balance-list',['data' => $item['child'], 'fontSize' => ($fontSize - 1), 'space' => ($space + 2)])

    @if(isset($item['child']))
        <tr>
            <td>
                @php
                    echo str_repeat('&nbsp;', $space);
                @endphp
                <b style="font-size:{{ $fontSize }}px">Total</b>
            </td>
            <td style="text-align:right;">
                <b style="font-size:{{ $fontSize }}px">Rp {{number_format($item['total'], 2, ",", ".")}}</b>
            </td>
        </tr>
    @else
    @endif
@endif
@endif


@endforeach
