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
                <td style="font-weight:bold;border: #000000 solid thin;">Pemasok : </td>
            </tr>
            <tr>
                <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                <td style="border: #000000 solid thin;">{{ $date }}</td>
                <td style="border: #000000 solid thin;">{{ $pemasok }}</td>
            </tr>
            <tr></tr>
        </tbody>
        <thead>
            <tr>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Tgl Faktur</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">No. Faktur
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nama Pemasok</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Jatuh Tempo</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Nilai Faktur</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Uang Muka</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Pembayaran</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Total Terbayar
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Sisa</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Umur</th>
            </tr>
        </thead>
        <tbody>
            @php
                $mtotal_pembelianSum = 0;
                $uangMuka = 0;
                $terbayar = 0;
                $bayarSum = 0;
                $sisaSum = 0;
            @endphp
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="border: #000000 solid thin;">{{ $data->tanggal_pembelian }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->id_transaksi }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_pemasok }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->top }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ $data->mtotal_pembelian }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->uang_muka }}
                    </td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->bayar }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->terbayar }}
                    </td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->sisa }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->aging }}</td>
                </tr>
                @php
                    $mtotal_pembelianSum += $data->mtotal_pembelian;
                    $bayarSum += $data->bayar;
                    $sisaSum += $data->sisa;
                    $uangMuka += $data->uang_muka;
                    $terbayar += $data->terbayar;
                @endphp
            @endforeach
            <tr>
                <td style="border: #000000 solid thin;background-color:#e0e0e0;" colspan="4">TOTAL</td>
                <td style="border: #000000 solid thin;text-align:right;background-color:#e0e0e0;">
                    {{ $mtotal_pembelianSum }}</td>
                <td style="border: #000000 solid thin;text-align:right;background-color:#e0e0e0;">
                    {{ $uangMuka }}</td>
                <td style="border: #000000 solid thin;text-align:right;background-color:#e0e0e0;">
                    {{ $bayarSum }}</td>
                <td style="border: #000000 solid thin;text-align:right;background-color:#e0e0e0;">
                    {{ $terbayar }}</td>
                <td style="border: #000000 solid thin;text-align:right;background-color:#e0e0e0;">
                    {{ $sisaSum }}</td>
                <td style="border: #000000 solid thin;background-color:#e0e0e0;"></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
