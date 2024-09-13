<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        table {
            /* margin-right: -8px; */
        }

        .table-head {
            width: 45px;
            /* text-decoration: underline; */
            font-weight: bold;
        }

        .label-date {
            text-align: right;
            margin-top: -19px;
        }

        body {
            margin: 5px;
        }

        @page {
            margin: 0px;
        }
    </style>
</head>

<body>
    @foreach ($details as $detail)
        <table style="border:1px solid black;">
            <tr>
                <td style="padding:5px;vertical-align:top;">
                    {!! DNS2D::getBarcodeHTML($detail->kode_batang_master_qr_code, 'QRCODE', 4, 4) !!}
                </td>
                <td>
                    <div style="margin:5px; font-size:12px;">
                        <div style="margin-left:2px;margin-bottom: 5px;line-height: 12px;">
                            QR Code : {{ $detail->kode_batang_master_qr_code }} ()
                            [{{ $detail->sg_master_qr_code }}]
                        </div>
                        <div style="margin-left:2px;margin-bottom: 5px;line-height: 12px;">{{ $detail->nama_barang }}
                        </div>
                        <table>
                            <tr>
                                <td class="table-head">SATUAN</td>
                                <td class="table-head">IN</td>
                                <td class="table-head">OUT</td>
                                <td class="table-head">GROSS</td>
                                <td class="table-head">BATCH</td>
                            </tr>
                            <tr>
                                <td>{{ $detail->nama_satuan_barang }}</td>
                                <td>{{ formatNumber($detail->jumlah_master_qr_code, 2) }}
                                </td>
                                <td>{{ formatNumber($detail->jumlah_master_qr_code - $detail->sisa_master_qr_code, 2) }}
                                </td>
                                <td>{{ formatNumber($detail->sisa_master_qr_code, 2) }}
                                </td>
                                <td>{{ $detail->batch_master_qr_code }}</td>
                            </tr>
                            <tr>
                                <td class="table-head">TARE</td>
                                <td class="table-head">NETT</td>
                            </tr>
                            <tr>
                                <td>{{ formatNumber($detail->total_tare, 2) }}</td>
                                <td>{{ formatNumber($detail->sisa_master_qr_code, 2) }} </td>
                            </tr>
                        </table>
                        <div class="label-date">Cetak: {{ date('d-M-Y') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    @endforeach
</body>

</html>
