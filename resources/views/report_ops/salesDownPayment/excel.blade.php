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
                    LAPORAN UANG MUKA PENJUALAN
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
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Tanggal</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Cabang</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Kode Transaksi
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nomor SO</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Akun Slip</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Mata Uang</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="border: #000000 solid thin;">{{ $data->tanggal }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_cabang }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->kode_uang_muka_penjualan }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_permintaan_penjualan }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_slip }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_mata_uang }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nominal }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
