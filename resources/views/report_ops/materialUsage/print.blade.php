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
    @if ($type == 'Rekap')
        <h3 style="text-align:center;">LAPORAN PEMAKAIAN</h3>
        <table width="100%">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Transaksi</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td>{{ $data->tanggal }}</td>
                        <td>{{ $data->kode_pemakaian }}</td>
                        <td>{{ $data->nama_cabang }}</td>
                        <td>{{ $data->nama_gudang }}</td>
                        <td>{{ $data->catatan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Detail')
        <h3 style="text-align:center;">LAPORAN PEMAKAIAN</h3>
        <table width="100%">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Transaksi</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>QR Code</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Jumlah Zak</th>
                    <th>Berat Zak</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td>{{ $data->tanggal }}</td>
                        <td>{{ $data->kode_pemakaian }}</td>
                        <td>{{ $data->nama_cabang }}</td>
                        <td>{{ $data->nama_gudang }}</td>
                        <td>{{ $data->kode_batang }}</td>
                        <td>{{ $data->nama_barang }}</td>
                        <td class="number">{{ $data->jumlah }}</td>
                        <td class="number">{{ $data->jumlah_zak }}</td>
                        <td class="number">{{ $data->weight_zak }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
