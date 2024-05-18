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
            @if($data["type"] == 'recap' || $data["type"] == 'awal')
                @if($data['nama_cabang'] == 'all')
                    <th colspan="6" style="text-align: center;font-size: 13;"><b>Export Excel Report Neraca {{ ucfirst($data["type"]) }}</b></th>
                @else
                    <th colspan="4" style="text-align: center;font-size: 13;"><b>Export Excel Report Neraca {{ ucfirst($data["type"]) }}</b></th>
                @endif
            @else
                @if($data['nama_cabang'] == 'all')
                    <th colspan="7" style="text-align: center;font-size: 13;"><b>Export Excel Report Neraca {{ ucfirst($data["type"]) }}</b></th>
                @else
                    <th colspan="5" style="text-align: center;font-size: 13;"><b>Export Excel Report Neraca {{ ucfirst($data["type"]) }}</b></th>
                @endif
            @endif
        </tr>
        <tr>
            <th style="font-size: 10; text-align: left;">Cabang : </th>
            <th style="font-size: 10; text-align: left;">{{ ucwords($data["nama_cabang"]) }}</th>
        </tr>
        <tr>
            <th style="font-size: 10; text-align: left;">Periode : </th>
            <th style="font-size: 10; text-align: left;">{{ $data["periode"] }}</th>
        </tr>
        <tr></tr><tr></tr>
        <tr>
            @if($data["type"] == 'recap' || $data["type"] == 'awal')
                <th colspan="3" style="border: #000000 solid thin; font-size: 11; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Header</th>
            @else
                <th colspan="4" style="border: #000000 solid thin; font-size: 11; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Header</th>
            @endif
            @if($data['nama_cabang'] == 'all')
                @foreach ($data['list_cabang'] as $cabang)
                    <th style="border: #000000 solid thin; font-size: 11; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Total {{ ucwords(str_replace('_', ' ', $cabang->new_nama_cabang)) }}</th>
                @endforeach
            @endif
            <th style="border: #000000 solid thin; font-size: 11; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Total</th>
        </tr>
        @php
            $fontSize = 10;
            $space = 0;
        @endphp

        @if($data['nama_cabang'] == 'all')
            @include('accounting.report.balance.balance_list_excel_konsolidasi',['data' => $data['data'], 'type' => $data["type"], 'space' => $space, 'list_cabang' => $data["list_cabang"]])
        @else
            @include('accounting.report.balance.balance_list_excel',['data' => $data['data'], 'type' => $data["type"], 'space' => $space])
        @endif
    </table>
</body>

</html>
