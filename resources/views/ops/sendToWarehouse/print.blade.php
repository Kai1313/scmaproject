<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style type="text/css">
        * {
            font-family: Arial;
            /* margin: 0px; */
            padding: 0px;
        }

        body {
            width: 19cm;
            padding-left: 10px;
            padding-right: 10px;
        }

        @page {
            margin-left: 3cm 2cm 2cm 2cm;
        }

        table.grid {
            font-size: 12px;
            border-collapse: collapse;
        }

        table.grid th {
            padding: 5px;
        }

        table.grid th {
            border-top: 0.2mm solid #000;
            border-bottom: 0.2mm solid #000;
            text-align: center;
            border: 1px solid #000;
        }

        table.grid tr td {
            padding: 2px;
            border-bottom: 0.2mm solid #000;
            border: 1px solid #000;
        }

        h1 {
            font-size: 18px;
        }

        h2 {
            font-size: 14px;
        }

        h3 {
            font-size: 12px;
        }

        p {
            font-size: 12px;
        }

        .atas {
            display: block;
            padding: 0px;
        }

        .top tr td {
            font-size: 12px;
        }

        .kanan tr td {
            font-size: 12px;
        }

        .kiri tr td {
            font-size: 12px;
        }

        .attr {
            font-size: 9pt;
            width: 100%;
            padding-top: 2pt;
            padding-bottom: 2pt;
            border-top: 0.2mm solid #000;
            border-bottom: 0.2mm solid #000;
        }

        .pagebreak {
            page-break-after: always;
            margin-bottom: 10px;
        }

        .akhir {
            width: 19cm;
            font-size: 13px;
        }

        .page {
            font-size: 12px;
        }

        table.footer {
            font-size: 12px;
            margin-left: 0.7cm;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        @media print {

            .no-print,
            .no-print * {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="atas">
        <table style="width:100%;">
            <tr>
                <td style="width:80px;height:60px;">
                    <img src='{{ env('OLD_API_ROOT') }}/uploads/logo2.jpg' style='padding:0; margin:0;' width='60'>
                </td>
                <td>
                    <h1>PT. SINAR CEMARAMAS ABADI</h1>
                </td>
                <td style="text-align:right;width:150px;">
                    <h1>Kirim Ke Gudang</h1>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td valign="top" style="width:67%;">
                    <table class='kiri' style="width:100%;">
                        <tr>
                            <td width="100"><b>Cabang</b></td>
                            <td width="5">:</td>
                            <td>{{ $data->cabang->nama_cabang }}</td>
                        </tr>
                        <tr>
                            <td><b>Gudang</b></td>
                            <td>:</td>
                            <td>{{ $data->gudang->nama_gudang }}</td>
                        </tr>
                        <tr>
                            <td><b>Gudang Tujuan</b></td>
                            <td>:</td>
                            <td>{{ $data->gudang2->nama_gudang }}</td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <table class='kanan' style="width:100%;">
                        <tr>
                            <td width="100"><b>Kode Transaksi</b></td>
                            <td width="5">:</td>
                            <td>{{ $data->kode_pindah_barang }}</td>
                        </tr>
                        <tr>
                            <td><b>Tanggal</b></td>
                            <td>:</td>
                            <td>{{ $data->tanggal_pindah_barang }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2"><b>Keterangan :</b> {{ $data->keterangan_pindah_barang }}</td>
            </tr>
        </table>
    </div>
    <table class="grid" width="100%">
        <tr>
            <th width="20">No</th>
            <th width="50">QR Code</th>
            <th width="150">Nama Barang</th>
            <th width="70">Qty</th>
            <th width="70">Batch</th>
            <th width="50">kadaluarsa</th>
        </tr>
        @foreach ($data->formatdetail as $key => $detail)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td class="text-center">{{ $detail->qr_code }}</td>
                <td>{{ $detail->nama_barang }}</td>
                <td class="text-right">
                    {{ number_format($detail->qty, 4, ',', '.') }}
                    {{ $detail->nama_satuan_barang }}
                </td>
                <td class="text-center">{{ $detail->batch }}</td>
                <td class="text-center">
                    {{ $detail->tanggal_kadaluarsa == '0000-00-00' ? '' : $detail->tanggal_kadaluarsa }}
                </td>
            </tr>
        @endforeach
    </table>
    <div class="text-right" style="margin:10px;">
        {!! DNS2D::getBarcodeSVG($data->kode_pindah_barang, 'QRCODE', 4.5, 4.5) !!}
    </div>
    <script>
        //window.print();
    </script>
</body>

</html>
