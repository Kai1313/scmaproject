<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan QC penerimaan</title>
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
            <td style="text-align:center;">LAPORAN QC PENERIMAAN</td>
        </tr>
    </table>
    <table style="width:100%;margin-bottom:5px;" class="table">
        <tr>
            <td>Cabang : {{ $cabang }}</td>
            <td>Tanggal : {{ $date }}</td>
            <td>Status : {{ $status }}</td>
        </tr>
    </table>
    <table width="100%" class="table">
        <thead>
            <tr>
                <th>Tanggal Pembelian</th>
                <th>Tanggal QC</th>
                <th>Kode Pembelian</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Alasan</th>
                <th>SG</th>
                <th>BE</th>
                <th>PH</th>
                <th>Warna</th>
                <th>Bentuk</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td>{{ $data->tanggal_pembelian }}</td>
                    <td>{{ $data->tanggal_qc }}</td>
                    <td>{{ $data->nama_pembelian }}</td>
                    <td>{{ $data->nama_barang }}</td>
                    <td>{{ $data->nama_satuan_barang }}</td>
                    <td class="number">{{ formatNumber($data->total_jumlah_purchase) }}</td>
                    <td>{{ $data->status_qc }}</td>
                    <td>{{ $data->reason }}</td>
                    <td class="number">{{ formatNumber($data->sg_pembelian_detail) }}</td>
                    <td class="number">{{ formatNumber($data->be_pembelian_detail) }}</td>
                    <td class="number">{{ formatNumber($data->ph_pembelian_detail) }}</td>
                    <td>{{ $data->warna_pembelian_detail }}</td>
                    <td>{{ $data->bentuk_pembelian_detail }}</td>
                    <td>{{ $data->keterangan_pembelian_detail }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
