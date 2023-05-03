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
            <th colspan="9" style="text-align: center;font-size: 16;"><b>Export Excel Report {{ ucfirst($data["type"]) }} General Ledger</b></th>
        </tr>
        <tr>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Cabang : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: right; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $data["cabang"] }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">Periode : </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $data["start_date"] }}</th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">s.d </th>
            <th style="border: #000000 solid thin; font-size: 14; text-align: left; width: 160px; font-weight: bold; background-color: #CCCCCC">{{ $data["end_date"] }}</th>
        </tr>
        <tr></tr><tr></tr>
        @if ($data["type"] == "recap")
            <tr>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Kode Akun</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Nama Akun</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Saldo Awal</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Debet</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Kredit</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Saldo Akhir</th>
            </tr>
            @foreach ($data["data"] as $item)
                <tr>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $item->kode_akun }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: left; word-wrap: break-word;">{{ $item->nama_akun }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($item->saldo_awal, 2,",",".") }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($item->debet, 2,",",".") }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($item->kredit, 2,",",".") }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($item->saldo_akhir, 2,",",".") }}</td>
                </tr>
            @endforeach
        @elseif ($data["type"] == "detail")
            <tr>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Tanggal</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">No Jurnal</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Kode Akun</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Nama Akun</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Keterangan</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">ID Transaksi</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Debet</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Kredit</th>
                <th style="border: #000000 solid thin; font-size: 14; text-align: center; width: 160px; font-weight: bold; background-color: #CCCCCC">Balance</th>
            </tr>
            @foreach ($data["data"] as $item)
                <tr>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $item->tanggal_jurnal }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $item->kode_jurnal }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: center;">{{ $item->kode_akun }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: left; word-wrap: break-word;">{{ $item->nama_akun }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: left; word-wrap: break-word;">{{ $item->keterangan }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: left; word-wrap: break-word;">{{ $item->id_transaksi }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($item->debet, 2,",",".") }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($item->kredit, 2,",",".") }}</td>
                    <td style="border: #000000 solid thin; font-size: 12; text-align: right;">{{ number_format($item->saldo_balance, 2,",",".") }}</td>
                </tr>
            @endforeach
        @endif
        
    </table>
</body>

</html>