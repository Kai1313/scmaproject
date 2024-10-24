<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Piutang </title>
    <style>
        .table {
            border-collapse: collapse;
        }

        .table td {
            font-size: 10px;
            border: #000000 solid thin;
        }

        .table th {
            font-size: 12px;
            border: #000000 solid thin;
            max-width: 150px;
            text-align: center;
            font-weight: bold;
        }

        .number {
            text-align: right;
        }

        .table-header td {
            font-size: 15px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table style="width:100%;margin-bottom:10px;" class="table-header">
        <tr>
            <td style="width:80px;"><img src="{{ asset('images/logo2.jpg') }}" alt="logo" style="width:70px;"></td>
            <td style="text-align:center;">LAPORAN PIUTANG </td>
        </tr>
    </table>
    <table style="width:100%;margin-bottom:5px;" class="table">
        <tr>
            <td>Cabang : {{ $cabang }}</td>
            <td>Tanggal : {{ $date }}</td>
            <td>Pelanggan : {{ $pelanggan }}</td>
        </tr>
    </table>
    <table width="100%" class="table">
        <thead>
            <tr>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>No. Faktur</th>
                <th>Tgl Faktur</th>
                <th>Jatuh Tempo</th>
                <th>Nilai Faktur</th>
                <th>Piutang</th>
                <th>Umur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td>{{ $data->kode_pelanggan }}</td>
                    <td>{{ $data->nama_pelanggan }}</td>
                    <td>{{ $data->id_transaksi }}</td>
                    <td>{{ $data->tanggal_penjualan }}</td>
                    <td>{{ $data->top }}</td>
                    <td class="number">{{ formatNumber($data->mtotal_penjualan, 2) }}</td>
                    <td class="number">{{ formatNumber($data->sisa, 2) }}</td>
                    <td class="number">{{ $data->aging }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
