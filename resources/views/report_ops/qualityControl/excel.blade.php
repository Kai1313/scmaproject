<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <table width="100%">
        <tbody>
            <tr>
                <td colspan="14" style="text-align:center;font-weight:bold;font-size:20px;">
                    LAPORAN QC PENERIMAAN PEMBELIAN
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Cabang : </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Tanggal QC: </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Status : </td>
            </tr>
            <tr>
                <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                <td style="border: #000000 solid thin;">{{ $date }}</td>
                <td style="border: #000000 solid thin;">{{ $status }}</td>
            </tr>
            <tr></tr>
        </tbody>
        <thead>
            <tr>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Tanggal Pembelian
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Kode Pembelian
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nama Barang</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Satuan</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Jumlah</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Tanggal QC</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Status</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Alasan</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">SG</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">BE</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">PH</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Warna</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Bentuk</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="border: #000000 solid thin;">{{ $data->tanggal_pembelian }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_pembelian }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_barang }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_satuan_barang }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->total_jumlah_purchase }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->tanggal_qc }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->status_qc }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->reason }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->sg_pembelian_detail }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->be_pembelian_detail }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->ph_pembelian_detail }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->warna_pembelian_detail }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->bentuk_pembelian_detail }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->keterangan_pembelian_detail }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
