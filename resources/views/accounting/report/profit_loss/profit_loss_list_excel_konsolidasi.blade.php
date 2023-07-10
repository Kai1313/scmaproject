@foreach ($data as $item)
    <tr>
        @if(isset($item['children']))
            @for ($i = 0; $i < $space; $i++)
                <td style="border: #000000 solid thin;"></td>
            @endfor
            <td style="border: #000000 solid thin; font-size:10px;">
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

            @foreach ($list_cabang as $cabang)
                <td style="border: #000000 solid thin;"></td>
            @endforeach
        @else
            @for ($i = 0; $i < $space; $i++)
                <td style="border: #000000 solid thin;"></td>
            @endfor
            <td style="border: #000000 solid thin; font-size:10px">
                {{$item['header']}} (Rp)
            </td>

            @foreach ($list_cabang as $cabang)
                @php
                    $format = 'total_' . $cabang->new_nama_cabang;
                @endphp
                <td style="border: #000000 solid thin; font-size:10px; text-align:right;" >
                    {{round($item[$format], 2)}}
                </td>
            @endforeach
            <td style="border: #000000 solid thin; font-size:10px; text-align:right;" >
                {{round($item['total_all'], 2)}}
            </td>
        @endif
    </tr>
    @if(isset($item['children']))
        @include('accounting.report.profit_loss.profit_loss_list_excel_konsolidasi',['data' => $item['children'], 'type' => $type, 'space' => ($space + 1), 'list_cabang' => $list_cabang])

        @if(isset($item['children']))
            <tr>
                @for ($i = 0; $i < $space; $i++)
                    <td style="border: #000000 solid thin;"></td>
                @endfor

                @if($type == 'recap' || $type =='awal')
                    <td  colspan="{{ (3 - intval($space)) }}" style="border: #000000 solid thin; font-size:10px;">
                        <b>Total {{$item['header']}} (Rp)</b>
                    </td>
                @else
                    <td colspan="{{ (4 - intval($space)) }}"  style="border: #000000 solid thin; font-size:10px;">
                        <b>Total {{$item['header']}} (Rp)</b>
                    </td>
                @endif

                @foreach ($list_cabang as $cabang)
                    @php
                        $format = 'total_' . $cabang->new_nama_cabang;
                    @endphp
                    <td  style="border: #000000 solid thin; text-align:right;font-size:10px;">
                        <b>{{round($item[$format], 2)}}</b>
                    </td>
                @endforeach

                <td  style="border: #000000 solid thin; text-align:right;font-size:10px;">
                    <b>{{round($item['total_all'], 2)}}</b>
                </td>
            </tr>
        @else
        @endif
    @endif
@endforeach
