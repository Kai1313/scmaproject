@foreach ($data as $item)
    <tr>
        @if(isset($item['children']))
            @for ($i = 0; $i < $space; $i++)
                <td style="border: #000000 solid thin;"></td>
            @endfor
            <td style="border: #000000 solid thin; font-size:13px;">
                <b>{{$item['header']}}</b>
            </td>
            <td style="border: #000000 solid thin;"></td>
            @if($type == 'recap' || $type =='awal')
                @for ($j = 2; $j > $space; $j--)
                    <td style="border: #000000 solid thin;"></td>
                @endfor
            @else
                @for ($j = 3; $j > $space; $j--)
                    <td style="border: #000000 solid thin;"></td>
                @endfor
            @endif
        @else
            @for ($i = 0; $i < $space; $i++)
                <td style="border: #000000 solid thin;"></td>
            @endfor
            <td style="border: #000000 solid thin; font-size:13px">
                {{$item['header']}} (Rp)
            </td>
            <td style="border: #000000 solid thin; font-size:13px; text-align:right;" >
                {{round($item['total'], 2)}}
            </td>
        @endif
    </tr>
    @if(isset($item['children']))
        @include('accounting.report.balance.balance_list_excel',['data' => $item['children'], 'type' => $type, 'space' => ($space + 1)])

        @if(isset($item['children']))
            <tr>
                @for ($i = 0; $i < $space; $i++)
                    <td style="border: #000000 solid thin;"></td>
                @endfor
                <td style="border: #000000 solid thin; font-size:13px;">
                    <b>Total {{$item['header']}} (Rp)</b>
                </td>
                @if($type == 'recap' || $type =='awal')
                    <td colspan="{{ (3 - intval($space)) }}" style="border: #000000 solid thin; text-align:right;font-size:13px;">
                        <b>{{round($item['total'], 2)}}</b>
                    </td>
                @else
                    <td colspan="{{ (4 - intval($space)) }}" style="border: #000000 solid thin; text-align:right;font-size:13px;">
                        <b>{{round($item['total'], 2)}}</b>
                    </td>
                @endif
            </tr>
        @else
        @endif
    @endif
@endforeach
