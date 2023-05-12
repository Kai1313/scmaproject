<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
</head>

<body>
    <table>
        <tr>
            @if($data["type"] == 'recap')
                <th colspan="4" style="text-align: center;font-size: 16;"><b>Export Excel Report Laba Rugi {{ ucfirst($data["type"]) }}</b></th>
            @else
                <th colspan="5" style="text-align: center;font-size: 16;"><b>Export Excel Report Laba Rugi {{ ucfirst($data["type"]) }}</b></th>
            @endif
        </tr>
        <tr>
            <th style="font-size: 13; text-align: left;">Cabang : </th>
            <th style="font-size: 13; text-align: left;">{{ $data["cabang"] }}</th>
        </tr>
        <tr>
            <th style="font-size: 13; text-align: left;">Periode : </th>
            <th style="font-size: 13; text-align: left;">{{ $data["periode"] }}</th>
        </tr>
        <tr></tr><tr></tr>
        <tr>
            @if($data["type"] == 'recap')
                <th colspan="3" style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Header</th>
            @else
                <th colspan="4" style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Header</th>
            @endif
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Total</th>
        </tr>
        @php
            $fontSize = 14;
            $space = 0;
        @endphp
        @include('accounting.report.balance.balance_list_excel',['data' => $data['data'], 'type' => $data["type"], 'space' => $space])
        {{-- @foreach ($data["data"] as $item) --}}
            {{-- <tr>
                <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $item->kode_akun }}</td>
                <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($item->saldo_awal, 2,",",".") }}</td>
            </tr> --}}
        {{-- @endforeach --}}
    </table>
</body>

</html>
