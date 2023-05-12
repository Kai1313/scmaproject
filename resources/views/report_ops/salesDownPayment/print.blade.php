<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan uang muka penjualan</title>
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
            <td style="text-align:center;">LAPORAN UANG MUKA PENJUALAN</td>
        </tr>
    </table>
    <table style="width:100%;margin-bottom:5px;" class="table">
        <tr>
            <td>Cabang : {{ $cabang }}</td>
            <td>Tanggal : {{ $date }}</td>
        </tr>
    </table>
    <table width="100%" class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Cabang</th>
                <th>Kode Transaksi</th>
                <th>Nomor SO</th>
                <th>Pelanggan</th>
                <th>Akun Slip</th>
                <th>Mata Uang</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td>{{ $data->tanggal }}</td>
                    <td>{{ $data->nama_cabang }}</td>
                    <td>{{ $data->kode_uang_muka_penjualan }}</td>
                    <td>{{ $data->nama_permintaan_penjualan }}</td>
                    <td>{{ $data->nama_pelanggan }}</td>
                    <td>{{ $data->nama_slip }}</td>
                    <td>{{ $data->nama_mata_uang }}</td>
                    <td class="number">{{ formatNumber($data->nominal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
