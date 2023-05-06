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
    <h3 style="text-align:center;">LAPORAN QC PENERIMAAN</h3>
    <table width="100%">
        <thead>
            <tr>
                <th>Tanggal Pembelian
                </th>
                <th>Kode Pembelian
                </th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Tanggal QC</th>
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
                    <td>{{ $data->nama_pembelian }}</td>
                    <td>{{ $data->nama_barang }}</td>
                    <td>{{ $data->nama_satuan_barang }}</td>
                    <td class="number">{{ formatNumber($data->total_jumlah_purchase) }}</td>
                    <td>{{ $data->tanggal_qc }}</td>
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
