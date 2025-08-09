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
                    LAPORAN PIUTANG
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Cabang : </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Tanggal : </td>
                <td style="font-weight:bold;border: #000000 solid thin;">Pelanggan : </td>
            </tr>
            <tr>
                <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                <td style="border: #000000 solid thin;">{{ $date }}</td>
                <td style="border: #000000 solid thin;">{{ $pelanggan }}</td>
            </tr>
            <tr></tr>
        </tbody>
        <thead>
            <tr>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Kode Pelanggan
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nama Pelanggan
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">No. Faktur
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Tgl Faktur</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Jatuh Tempo</th>
                <th style="border: #000000 solid thin;width:200px;text-align:center;font-weight:bold;">Nilai Faktur</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Total Pembayaran
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Sisa Piutang</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Umur</th>
            </tr>
        </thead>
        <tbody>
            @php
                $mtotal_penjualanSum = 0;
                $bayarSum = 0;
                $sisaSum = 0;
            @endphp
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="border: #000000 solid thin;">{{ $data->kode_pelanggan }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->nama_pelanggan }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->id_transaksi }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->tanggal_penjualan }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->top }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ $data->mtotal_penjualan }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->bayar }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">{{ $data->sisa }}</td>
                    <td style="border: #000000 solid thin;">{{ $data->aging }}</td>
                </tr>
                @php
                    $mtotal_penjualanSum += $data->mtotal_penjualan;
                    $bayarSum += $data->bayar;
                    $sisaSum += $data->sisa;
                @endphp
            @endforeach
            <tr>
                <td style="border: #000000 solid thin;background-color:#e0e0e0;" colspan="5">TOTAL</td>
                <td style="border: #000000 solid thin;text-align:right;background-color:#e0e0e0;">
                    {{ $mtotal_penjualanSum }}</td>
                <td style="border: #000000 solid thin;text-align:right;background-color:#e0e0e0;">
                    {{ $bayarSum }}</td>
                <td style="border: #000000 solid thin;text-align:right;background-color:#e0e0e0;">
                    {{ $sisaSum }}</td>
                <td style="border: #000000 solid thin;background-color:#e0e0e0;"></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
