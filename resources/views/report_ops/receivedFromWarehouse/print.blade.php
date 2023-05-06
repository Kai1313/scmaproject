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
        <h3 style="text-align:center;">LAPORAN TERIMA DARI GUDANG</h3>
        <table width="100%">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Transaksi</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Gudang Asal</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td>{{ $data->tanggal_pindah_barang }}</td>
                        <td>{{ $data->kode_pindah_barang }}</td>
                        <td>{{ $data->nama_cabang }}</td>
                        <td>{{ $data->nama_gudang }}</td>
                        <td>{{ $data->nama_gudang2 }}</td>
                        <td>{{ $data->keterangan_pindah_barang }}</td>
                        <td>{{ $data->status_pindah_barang }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Detail')
        <h3 style="text-align:center;">LAPORAN TERIMA DARI GUDANG</h3>
        <table width="100%">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Transaksi</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Gudang Asal</th>
                    <th>QR Code</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th>Batch</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td>{{ $data->tanggal_pindah_barang }}</td>
                        <td>{{ $data->kode_pindah_barang }}</td>
                        <td>{{ $data->nama_cabang }}</td>
                        <td>{{ $data->nama_gudang }}</td>
                        <td>{{ $data->nama_gudang2 }}</td>
                        <td>{{ $data->qr_code }}</td>
                        <td>{{ $data->nama_barang }}</td>
                        <td>{{ $data->nama_satuan_barang }}</td>
                        <td class="number">{{ $data->qty }}</td>
                        <td>{{ $data->batch }}</td>
                        <td>{{ $data->status_diterima }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Outstanding')
        <h3 style="text-align:center;">LAPORAN TERIMA DARI GUDANG</h3>
        <table width="100%">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Transaksi</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Gudang Asal</th>
                    <th>QR Code</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th>Batch</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td>{{ $data->tanggal_pindah_barang }}</td>
                        <td>{{ $data->kode_pindah_barang }}</td>
                        <td>{{ $data->nama_cabang }}</td>
                        <td>{{ $data->nama_gudang }}</td>
                        <td>{{ $data->nama_gudang2 }}</td>
                        <td>{{ $data->qr_code }}</td>
                        <td>{{ $data->nama_barang }}</td>
                        <td>{{ $data->nama_satuan_barang }}</td>
                        <td class="number">{{ $data->qty }}</td>
                        <td>{{ $data->batch }}</td>
                        <td>{{ $data->status_diterima }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
