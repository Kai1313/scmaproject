<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan pemakaian</title>
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
            <td style="text-align:center;">LAPORAN PEMAKAIAN</td>
        </tr>
    </table>
    <table style="width:100%;margin-bottom:5px;" class="table">
        <tr>
            <td>Cabang : {{ $cabang }}</td>
            <td>Gudang : {{ count(explode(', ', $cabang)) > 1 ? 'Semua Gudang' : $gudang }}</td>
            <td>Tanggal : {{ $date }}</td>
            <td>Jenis Laporan : {{ $type }}</td>
        </tr>
    </table>
    @if ($type == 'Rekap')
        <table width="100%" class="table">
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
        <table width="100%" class="table">
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
                    <th>Catatan Header</th>
                    <th>Catatan Detail</th>
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
                        <th>{{ $data->catatan_header }}</th>
                        <th>{{ $data->catatan_detail }}</th>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
