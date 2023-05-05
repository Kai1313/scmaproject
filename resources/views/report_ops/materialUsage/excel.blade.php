<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    @if ($type == 'Rekap')
        <table width="100%">
            <tbody>
                <tr>
                    <td colspan="5" style="text-align:center;font-weight:bold;font-size:20px;">
                        LAPORAN PEMAKAIAN
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                    </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Cabang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Gudang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Tanggal : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Jenis Laporan : </td>
                </tr>
                <tr>
                    <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                    <td style="border: #000000 solid thin;">{{ $gudang }}</td>
                    <td style="border: #000000 solid thin;">{{ $date }}</td>
                    <td style="border: #000000 solid thin;">{{ $type }}</td>
                </tr>
                <tr></tr>
            </tbody>
            <thead>
                <tr>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Tanggal</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Kode
                        Transaksi</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Cabang</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Gudang</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="border: #000000 solid thin;">{{ $data->tanggal }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->kode_pemakaian }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_cabang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_gudang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->catatan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Detail')
        <table width="100%">
            <tbody>
                <tr>
                    <td colspan="9" style="text-align:center;font-weight:bold;font-size:20px;">
                        LAPORAN PEMAKAIAN
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                    </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Cabang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Gudang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Tanggal : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Jenis Laporan : </td>
                </tr>
                <tr>
                    <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                    <td style="border: #000000 solid thin;">{{ $gudang }}</td>
                    <td style="border: #000000 solid thin;">{{ $date }}</td>
                    <td style="border: #000000 solid thin;">{{ $type }}</td>
                </tr>
                <tr></tr>
            </tbody>
            <thead>
                <tr>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Tanggal</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Kode
                        Transaksi</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Cabang</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Gudang</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">QR Code</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nama Barang
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Jumlah</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Jumlah Zak
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Berat Zak
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="border: #000000 solid thin;">{{ $data->tanggal }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->kode_pemakaian }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_cabang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_gudang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->kode_batang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->jumlah }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->jumlah_zak }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->weight_zak }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
