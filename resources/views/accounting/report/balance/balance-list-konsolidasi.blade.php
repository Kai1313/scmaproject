@foreach ($data as $item)
    <tr>
        @if(isset($item['children']))
            <td>
                @php
                    echo str_repeat('&nbsp;', $space);
                @endphp
                <b style="font-size:{{ $fontSize }}px">{{$item['header']}}</b>
            </td>
            @foreach ($list_cabang as $cabang)
                <td></td>
            @endforeach
            <td></td>
        @else
            <td style="font-size:{{ $fontSize }}px">
                @php
                    echo str_repeat('&nbsp;', $space);
                @endphp
                {{$item['header']}} (Rp)
            </td>
            @foreach ($list_cabang as $cabang)
                @php
                    $format = 'total_' . $cabang;
                @endphp

                <td style="font-size:{{ $fontSize }}px; text-align:right;" >
                    {{number_format($item[$format], 2, ",", ".")}}
                </td>
            @endforeach
            <td style="font-size:{{ $fontSize }}px; text-align:right;" >
                {{number_format($item['total_all'], 2, ",", ".")}}
            </td>
        @endif
    </tr>
    @if(isset($item['children']))
        @include('accounting.report.balance.balance-list-konsolidasi',['data' => $item['children'], 'fontSize' => ($fontSize), 'space' => ($space + 2), 'list_cabang' => $list_cabang])

        @if(isset($item['children']))
            <tr>
                <td>
                    @php
                        echo str_repeat('&nbsp;', $space);
                    @endphp
                    <b style="font-size:{{ $fontSize }}px">Total {{$item['header']}} (Rp)</b>
                </td>
                @foreach ($list_cabang as $cabang)
                    @php
                        $format = 'total_' . $cabang;
                    @endphp
                    <td style="text-align:right;">
                        <b style="font-size:{{ $fontSize }}px">{{number_format($item[$format], 2, ",", ".")}}</b>
                    </td>
                @endforeach
                <td style="text-align:right;">
                    <b style="font-size:{{ $fontSize }}px">{{number_format($item['total_all'], 2, ",", ".")}}</b>
                </td>
            </tr>
        @else
        @endif
    @endif
@endforeach
