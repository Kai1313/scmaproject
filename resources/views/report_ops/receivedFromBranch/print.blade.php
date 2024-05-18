<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan terima dari cabang</title>
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
            <td style="text-align:center;">LAPORAN TERIMA DARI CABANG</td>
        </tr>
    </table>
    <table style="width:100%;margin-bottom:5px;" class="table">
        <tr>
            <td>Cabang : {{ $cabang }}</td>
            <td>Gudang : {{ count(explode(', ', $cabang)) > 1 ? 'Semua Gudang' : $gudang }}</td>
            <td>Tanggal : {{ $date }}</td>
            <td>Status : {{ $status }}</td>
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
                    <th>Cabang Asal</th>
                    <th>Keterangan</th>
                    <th>Jasa Pengiriman</th>
                    <th>Nomor Polisi</th>
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
                        <td>{{ $data->nama_cabang2 }}</td>
                        <td>{{ $data->keterangan_pindah_barang }}</td>
                        <td>{{ $data->transporter }}</td>
                        <td>{{ $data->nomor_polisi }}</td>
                        <td>{{ $data->status_pindah_barang }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Detail')
        <table width="100%" class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>KodeTransaksi</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Cabang Asal</th>
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
                        <td>{{ $data->nama_cabang2 }}</td>
                        <td>{{ $data->qr_code }}</td>
                        <td>{{ $data->nama_barang }}</td>
                        <td>{{ $data->nama_satuan_barang }}</td>
                        <td class="number">{{ formatNumber($data->qty) }}</td>
                        <td>{{ $data->batch }}</td>
                        <td>{{ $data->status_diterima }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Outstanding')
        <table width="100%" class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Transaksi</th>
                    <th>Cabang</th>
                    <th>Gudang</th>
                    <th>Cabang Asal</th>
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
                        <td>{{ $data->nama_cabang2 }}</td>
                        <td>{{ $data->qr_code }}</td>
                        <td>{{ $data->nama_barang }}</td>
                        <td>{{ $data->nama_satuan_barang }}</td>
                        <td class="number">{{ formatNumber($data->qty) }}</td>
                        <td>{{ $data->batch }}</td>
                        <td>{{ $data->status_diterima }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
