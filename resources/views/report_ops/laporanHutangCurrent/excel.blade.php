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
                <td colspan="7" style="text-align:center;font-weight:bold;font-size:20px;">
                    LAPORAN HUTANG
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Cabang : </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Tanggal : </td>
            </tr>
            <tr>
                <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                <td style="border: #000000 solid thin;">{{ $date }}</td>
            </tr>
            <tr></tr>
        </tbody>
        <thead>
            <tr>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Kode Pemasok</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nama Pemasok</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">No. Faktur
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Tgl Faktur</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Jatuh Tempo</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Nilai Faktur</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Hutang Asing</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Hutang Pajak</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Umur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="border: #000000 solid thin;">{{ $data->kode_pemasok }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_pemasok }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->id_transaksi }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->tanggal_pembelian }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->top }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ number_format($data->mtotal_pembelian, 2) }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ number_format($data->sisa, 2) }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ number_format($data->sisa_tax, 2) }}
                    </td>
                    <td style="border: #000000 solid thin;">{{ $data->aging }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
