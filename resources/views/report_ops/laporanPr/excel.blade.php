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
                    LAPORAN PURCHASE REQUEST (PR)
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                </td>
                <td style="font-weight:bold;border: #000000 solid thin;">CABANG : </td>
                <td style="font-weight:bold;border: #000000 solid thin;">GUDANG : </td>
                <td style="font-weight:bold;border: #000000 solid thin;">TANGGAL: </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Status PO: </td>
            </tr>
            <tr>
                <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                <td style="border: #000000 solid thin;">{{ $gudang }}</td>
                <td style="border: #000000 solid thin;">{{ $date }}</td>
                <td style="border: #000000 solid thin;">{{ $po_status }}</td>
            </tr>
            <tr></tr>
        </tbody>
        <thead>
            <tr>
                <th style="border: #000000 solid thin;width:170px;text-align:center;font-weight:bold;">Gudang</th>
                <th style="border: #000000 solid thin;width:100px;text-align:center;font-weight:bold;">Tanggal PR</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Kode PR</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Pemohon</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Barang</th>
                <th style="border: #000000 solid thin;width:100px;text-align:center;font-weight:bold;">Satuan</th>
                <th style="border: #000000 solid thin;width:100px;text-align:center;font-weight:bold;">Jumlah PR</th>
                <th style="border: #000000 solid thin;width:100px;text-align:center;font-weight:bold;">Tanggal PO</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Kode PO</th>
                <th style="border: #000000 solid thin;width:100px;text-align:center;font-weight:bold;">Jumlah PO</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="border: #000000 solid thin;">{{ $data->nama_gudang }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->purchase_request_date }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->purchase_request_code }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_pengguna }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_barang }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_satuan_barang }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->qty }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->tanggal_permintaan_pembelian }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_permintaan_pembelian }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->jumlah_permintaan_pembelian_detail }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
