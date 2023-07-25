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
                    DATA PERHITUNGAN STOK MINIMAL BULAN {{ $historyHeader->bulan . ' ' . $historyHeader->tahun }}
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Cabang</td>
                <td style="font-weight:bold;border: #000000 solid thin;">{{ $cabang->nama_cabang }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Nama Bahan</td>
                <td style="font-weight:bold;border: #000000 solid thin;">{{ $barang->nama_barang }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Stok Minimal Hitung</td>
                <td style="font-weight:bold;border: #000000 solid thin;text-align:right;">
                    {{ formatNumber($historyHeader->stok_min_hitung, 4) }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Stok Minimal Khusus</td>
                <td style="font-weight:bold;border: #000000 solid thin;text-align:right;">
                    {{ empty($historyHeader->stok_min_khusus) ? '-' : formatNumber($historyHeader->stok_min_khusus, 4) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Stok Minimal</td>
                <td style="font-weight:bold;border: #000000 solid thin;text-align:right;">
                    {{ formatNumber($historyHeader->jumlah, 4) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Stok Aktif</td>
                <td style="font-weight:bold;border: #000000 solid thin;text-align:right;">
                    {{ formatNumber($sumStok, 4) }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Range Data Penjualan</td>
                <td style="font-weight:bold;border: #000000 solid thin;">{{ intval($historyHeader->range) }} Bulan</td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Periode Data Penjualan</td>
                <td style="font-weight:bold;border: #000000 solid thin;">{{ $historyHeader->penj_dari }} -
                    {{ $historyHeader->penj_sampai }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Konstanta Kenaikan</td>
                <td style="font-weight:bold;border: #000000 solid thin;">{{ $historyHeader->persen }}%</td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Konstanta Bahan Lokal</td>
                <td style="font-weight:bold;border: #000000 solid thin;">{{ $historyHeader->lokal }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold;border: #000000 solid thin;">Konstanta Bahan Import</td>
                <td style="font-weight:bold;border: #000000 solid thin;">{{ $historyHeader->import }}</td>
            </tr>
            <tr></tr>
        </tbody>
        <thead>
            <tr>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Digunakan Untuk
                    Produksi
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Data Penjualan
                </th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Rata-rata Per
                    Bulan</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Kenaikan</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Rata-rata +
                    Kenaikan</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">BOM</th>
                <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Kebutuhan Per
                    Bulan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
            @endphp
            @foreach ($datas as $key => $data)
                @php
                    $total += $data->pemakaian_per_barang_jadi;
                @endphp
                <tr>
                    <td style="border: #000000 solid thin;">{{ $data->nama_barang }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ formatNumber($data->total_jual, 4) }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ formatNumber($data->total_jual_per_bulan, 4) }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ formatNumber($data->plus_persen, 4) }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ formatNumber($data->per_bulan_plus_persen, 4) }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ formatNumber($data->avg_prorate, 4) }}</td>
                    <td style="border: #000000 solid thin;text-align:right;">
                        {{ formatNumber($data->pemakaian_per_barang_jadi, 4) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan=6 style="border: #000000 solid thin;text-align:right;">
                    Total Kebutuhan Per Bulan</td>
                <td style="border: #000000 solid thin;text-align:right;font-weight:bold">
                    {{ formatNumber($total, 4) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
