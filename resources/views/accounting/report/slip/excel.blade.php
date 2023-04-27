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
            <th colspan="9" style="text-align: center;font-size: 16;"><b>Export Excel Report Slip</b></th>
        </tr>
        <tr>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Cabang : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: right; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $cabang->nama_cabang }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Slip : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: right; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $mutasis[0]->nama_slip }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Periode : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: right; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $from }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">s.d </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: right; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $to }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: right; width: 160px; font-weight: bold; background-color: #CCCCCC"></th>
        </tr>
        <tr></tr>
        <tr>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Tanggal</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">No Jurnal</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Slip</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Akun</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Keterangan</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">ID Transaksi</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Debet</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Credit</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Balance</th>
        </tr>

        @php
        $balance = 0;
        @endphp

        @foreach ($saldo_awal as $data)

        @php
        if($data->debet > 0){
            $balance += $data->debet;
        }

        if($data->credit > 0){
            $balance -= $data->credit;
        }
        @endphp

        <tr>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $from }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->kode_jurnal }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->nama_slip }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->nama_akun }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->keterangan }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->id_transaksi }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($data->debet, 2,",",".") }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($data->credit, 2,",",".") }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($balance, 2,",",".") }}</td>
        </tr>
        @endforeach
        @foreach ($mutasis as $data)

        @php
        if($data->debet > 0){
            $balance += $data->debet;
        }

        if($data->credit > 0){
            $balance -= $data->credit;
        }
        @endphp
        
        <tr>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->tanggal_jurnal }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->kode_jurnal }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->nama_slip }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->nama_akun }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->keterangan }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $data->id_transaksi }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($data->debet, 2,",",".") }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($data->credit, 2,",",".") }}</td>
            <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($balance, 2,",",".") }}</td>
        </tr>
        @endforeach
    </table>
</body>

</html>