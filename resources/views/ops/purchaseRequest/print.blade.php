<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bukti permintaan pembelian {{ $data->purchase_request_code }}</title>
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
            margin: 180px 25px 25px 25px;
        }

        header {
            position: fixed;
            top: -150px;
            left: 0px;
            right: 0px;
        }
    </style>
</head>

<body>
    <header>
        <div style="font-size:13px;text-align:right;margin-top:-16px;">{{ $data->purchase_request_code }}</div>
        <table class="table-header">
            <tr>
                <th style="width:80px;">
                    <img src="{{ asset('images/logo2.jpg') }}" alt="logo" style="width:70px;">
                </th>
                <th style="width:100%;text-align:center;">PT. SINAR CEMARAMAS ABADI</th>
                <th style="width:150px;" class="text-right">Bukti Permintaan Pembelian</th>
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
                            <td><b>Tanggal</b></td>
                            <td>:</td>
                            <td>{{ $data->purchase_request_date }}</td>
                        </tr>
                        <tr>
                            <td><b>Estimasi</b></td>
                            <td>:</td>
                            <td>{{ $data->purchase_request_estimation_date }}</td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <table class='table-subheader'>
                        <tr>
                            <td><b>Pemohon</b></td>
                            <td>:</td>
                            <td>{{ $data->pengguna->nama_pengguna }}</td>
                        </tr>
                        <tr>
                            <td><b>Status</b></td>
                            <td>:</td>
                            <td>{{ $arrayStatus[$data->approval_status]['text'] }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>
    <main>
        <table class="table">
            <tr>
                <th width="15">No</th>
                <th>Nama Barang</th>
                <th width="50">Satuan</th>
                <th width="50">Qty</th>
                <th>Catatan</th>
            </tr>
            @foreach ($data->formatdetail as $key => $detail)
                <tr>
                    <td align="center">{{ $key + 1 }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td align="right">{{ formatNumber($detail->qty) }}</td>
                    <td align="center">{{ $detail->nama_satuan_barang }}</td>
                    <td>{{ $detail->notes }}</td>
                </tr>
            @endforeach
        </table>
        <table class='table-subheader'>
            <tr>
                <td valign="top" style="width:70px;"><b>Keterangan</b></td>
                <td style="width:10px;" valign="top">:</td>
                <td>{{ $data->catatan }}</td>
            </tr>
        </table>

        {{-- <table class="table-subheader">
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
            </tr>
        </table> --}}

    </main>
</body>

</html>
