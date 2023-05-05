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
                    <td colspan="7" style="text-align:center;font-weight:bold;font-size:20px;">
                        LAPORAN KIRIM KE GUDANG
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                    </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Cabang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Gudang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Tanggal : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Status : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Jenis Laporan : </td>
                </tr>
                <tr>
                    <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                    <td style="border: #000000 solid thin;">{{ $gudang }}</td>
                    <td style="border: #000000 solid thin;">{{ $date }}</td>
                    <td style="border: #000000 solid thin;">{{ $status }}</td>
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
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Gudang Tujuan
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Keterangan
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="border: #000000 solid thin;">{{ $data->tanggal_pindah_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->kode_pindah_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_cabang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_gudang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_gudang2 }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->keterangan_pindah_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->status_pindah_barang }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Detail')
        <table width="100%">
            <tbody>
                <tr>
                    <td colspan="11" style="text-align:center;font-weight:bold;font-size:20px;">
                        LAPORAN KIRIM KE GUDANG
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                    </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Cabang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Gudang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Tanggal : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Status : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Jenis Laporan : </td>
                </tr>
                <tr>
                    <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                    <td style="border: #000000 solid thin;">{{ $gudang }}</td>
                    <td style="border: #000000 solid thin;">{{ $date }}</td>
                    <td style="border: #000000 solid thin;">{{ $status }}</td>
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
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Gudang Tujuan
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">QR Code</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nama Barang
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Satuan</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Jumlah</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Batch
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Status
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="border: #000000 solid thin;">{{ $data->tanggal_pindah_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->kode_pindah_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_cabang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_gudang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_gudang2 }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->qr_code }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_satuan_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->qty }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->batch }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->status_diterima }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == 'Outstanding')
        <table width="100%">
            <tbody>
                <tr>
                    <td colspan="11" style="text-align:center;font-weight:bold;font-size:20px;">
                        LAPORAN KIRIM KE CABANG
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td rowspan="2" style="font-weight:bold;border: #000000 solid thin;vertical-align:middle;">Filter
                    </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Cabang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Gudang : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Tanggal : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Status : </td>
                    <td style="font-weight:bold;border: #000000 solid thin;">Jenis Laporan : </td>
                </tr>
                <tr>
                    <td style="border: #000000 solid thin;">{{ $cabang }}</td>
                    <td style="border: #000000 solid thin;">{{ $gudang }}</td>
                    <td style="border: #000000 solid thin;">{{ $date }}</td>
                    <td style="border: #000000 solid thin;">{{ $status }}</td>
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
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Gudang Tujuan
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">QR Code</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Nama Barang
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Satuan</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Jumlah</th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Batch
                    </th>
                    <th style="border: #000000 solid thin;width:150px;text-align:center;font-weight:bold;">Status
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $key => $data)
                    <tr>
                        <td style="border: #000000 solid thin;">{{ $data->tanggal_pindah_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->kode_pindah_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_cabang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_gudang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_gudang2 }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->qr_code }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->nama_satuan_barang }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->qty }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->batch }}</td>
                        <td style="border: #000000 solid thin;">{{ $data->status_diterima }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
