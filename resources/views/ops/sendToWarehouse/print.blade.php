<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Surat jalan pindah cabang {{ $data->kode_pindah_barang }}</title>
    <style type="text/css">
        .table {
            border-collapse: collapse;
            width: 100%;
        }

        .table td {
            font-size: 12px;
            border: #000000 solid thin;
        }

        .table th {
            font-size: 13px;
            border: #000000 solid thin;
            max-width: 150px;
            text-align: center;
            font-weight: bold;
        }

        .number {
            text-align: right;
        }

        .table-header {
            width: 100%;
        }

        .table-header th {
            font-size: 17px;
            font-weight: bold;
        }

        .table-subheader {
            width: 100%;
        }

        .table-subheader td {
            font-size: 12px;
        }

        .table-signature {
            border-spacing: 0.5em 0.5em;
        }

        .table-signature td {
            font-size: 12px;
            vertical-align: top;
            width: 100px;
            padding: 0px 5px 0px 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        @page {
            margin: 160px 25px 25px 25px;
        }

        header {
            position: fixed;
            top: -130px;
            left: 0px;
            right: 0px;
        }
    </style>
</head>

<body>
    <header>
        <table class="table-header">
            <tr>
                <th style="width:80px;"><img src="{{ asset('images/logo2.jpg') }}" alt="logo" style="width:70px;">
                </th>
                <th style="width:100%;text-align:center;">PT. SINAR CEMARAMAS ABADI</th>
                <th style="width:150px;" class="text-center">Surat Jalan <br>Pindah Gudang</th>
            </tr>
        </table>
        <table class="table-subheader">
            <tr>
                <td valign="top" style="width:67%;">
                    <table class='table-subheader'>
                        <tr>
                            <td width="70"><b>Cabang</b></td>
                            <td width="5">:</td>
                            <td>{{ $data->cabang->nama_cabang }}</td>
                        </tr>
                        <tr>
                            <td><b>Gudang</b></td>
                            <td>:</td>
                            <td>{{ $data->gudang->nama_gudang }}</td>
                        </tr>

                        <tr>
                            <td><b>Tujuan</b></td>
                            <td>:</td>
                            <td>{{ $data->gudang2->nama_gudang }}</td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <table class='table-subheader'>
                        <tr>
                            <td width="70"><b>Tanggal</b></td>
                            <td width="5">:</td>
                            <td>{{ $data->tanggal_pindah_barang }}</td>
                        </tr>
                        <tr>
                            <td><b>Kode Transaksi</b></td>
                            <td>:</td>
                            <td>{{ $data->kode_pindah_barang }}</td>
                        </tr>
                        @if ($data->id_produksi)
                            <tr>
                                <td><b>Kode Produksi</b></td>
                                <td>:</td>
                                <td>{{ $data->produksi->nama_produksi }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    </header>
    <main>
        <table class="table">
            <tr>
                <th width="15">No</th>
                <th width="60">Kode</th>
                <th>Nama Barang</th>
                <th width="50">Satuan</th>
                <th width="50">Qty</th>
                <th width="70">Batch</th>
                <th width="70">Kadaluarsa</th>
            </tr>
            @foreach ($data->formatdetail as $key => $detail)
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td>{{ $detail->qr_code }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td class="text-center">{{ $detail->nama_satuan_barang }}</td>
                    <td class="text-right">{{ formatNumber($detail->qty) }}</td>
                    <td class="text-center">{{ $detail->batch }}</td>
                    <td>
                        {{ $detail->tanggal_kadaluarsa == '0000-00-00' ? '' : $detail->tanggal_kadaluarsa }}
                    </td>
                </tr>
            @endforeach
        </table>
        <table class='table-subheader'>
            <tr>
                <td valign="top" style="width:70px;"><b>Keterangan</b></td>
                <td style="width:10px;" valign="top">:</td>
                <td>{{ $data->keterangan_pindah_barang }}</td>
            </tr>
        </table>

        <table class="table-subheader">
            <tr>
                <td>
                    <table class="table-signature">
                        <tr>
                            <td style="height:70px;">Pembuat</td>
                            <td>Disetujui</td>
                            <td>Penerima</td>
                        </tr>
                        <tr>
                            <td style="border-top:1px solid black;">Tgl:</td>
                            <td style="border-top:1px solid black;">Tgl:</td>
                            <td style="border-top:1px solid black;">Tgl:</td>
                        </tr>
                    </table>
                </td>
                <td>
                    {!! DNS2D::getBarcodeHTML($data->kode_pindah_barang, 'QRCODE', 5, 5) !!}
                </td>
            </tr>
        </table>

    </main>
</body>

</html>
