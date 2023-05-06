<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        table {
            border-collapse: collapse;
        }

        td {
            font-size: 10px;
            border: #000000 solid thin;
        }

        th {
            font-size: 12px;
            border: #000000 solid thin;
            max-width: 150px;
            text-align: center;
            font-weight: bold;
        }

        .number {
            text-align: right;
        }
    </style>
</head>

<body>
    <h3 style="text-align:center;">LAPORAN UANG MUKA PENJUALAN</h3>
    <table width="100%">
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
