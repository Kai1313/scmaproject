@foreach ($data as $item)
    <tr>
        @if(isset($item['children']))
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
                {{$item['header']}} (Rp)
            </td>
            <td style="font-size:{{ $fontSize }}px; text-align:right;" >
                {{number_format($item['total'], 2, ",", ".")}}
            </td>
        @endif
    </tr>
    @if(isset($item['children']))
        @include('accounting.report.profit_loss.profit-loss-list',['data' => $item['children'], 'fontSize' => ($fontSize - 1), 'space' => ($space + 2)])

        @if(isset($item['children']))
            <tr>
                <td>
                    @php
                        echo str_repeat('&nbsp;', $space);
                    @endphp
                    <b style="font-size:{{ $fontSize }}px">Total {{$item['header']}} (Rp)</b>
                </td>
                <td style="text-align:right;">
                    <b style="font-size:{{ $fontSize }}px">{{number_format($item['total'], 2, ",", ".")}}</b>
                </td>
            </tr>
        @else
        @endif
    @endif
@endforeach
