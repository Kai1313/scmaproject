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
            <th colspan="9" style="text-align: center;font-size: 16;"><b>Export Excel Report Giro</b></th>
        </tr>
        <tr>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Cabang : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $cabang }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Slip : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $slip }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Tipe : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $tipe }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Tanggal : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $tanggal }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Status : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $status }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: right; width: 160px; font-weight: bold; background-color: #CCCCCC"></th>
        </tr>
        <tr></tr>
        <tr>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC" colspan="6">Giro</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC" colspan="3">Cair</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC" colspan="2">Tolak</th>
        </tr>
        <tr>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">No Jurnal</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Tanggal Jurnal</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">No Giro</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Tanggal Giro</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Jatuh Tempo Giro</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Total</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">No Jurnal</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Tanggal</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Slip</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">No Jurnal</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Tanggal</th>
        </tr>
        @foreach ($data as $val)
        <tr>
            <td style="border: #000000 solid thin; font-size: 12; text-align: left;">{{ $val->kode_jurnal }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $val->tanggal_jurnal }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: left;">{{ $val->no_giro }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $val->tanggal_giro }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $val->tanggal_giro_jt }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($val->total, 2, ",", ".") }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: left;">{{ $val->cair_kode_jurnal }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $val->cair_tanggal_giro }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: left;">{{ $val->cair_slip }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: left;">{{ $val->tolak_kode_jurnal }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $val->tolak_tanggal_giro }}</td>
        </tr>
        @endforeach
    </table>
</body>

</html>