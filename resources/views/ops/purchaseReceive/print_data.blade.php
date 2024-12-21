<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Penerimaan Bahan {{ $data->nama_pembelian }}</title>
    <style type="text/css">
        * {
            font-family: Arial, Helvetica, sans-serif;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            border-bottom: 1px solid #000000;
        }

        .table td {
            font-size: 11px !important;
            padding: 4px;
            border-bottom: 1px dotted #000000;
            border-right: 1px solid #000000;
            border-left: 1px solid #000000;
        }

        .table th {
            font-size: 11px;
            border: 1px solid #000000;
            /* max-width: 150px; */
            text-align: center;
            font-weight: bold;
            padding: 4px;
        }

        .number {
            text-align: right;
        }

        .table-header {
            width: 100%;
            border-collapse: collapse;
        }

        .table-header th {
            font-size: 11px;
            font-weight: bold;
            border: 1px solid black;
            /* font-weight: normal; */
            padding: 2px;
        }

        .table-subheader {
            border-left: 1px solid black;
            border-right: 1px solid black;
            width: 100%;
        }

        .table-subheader td {
            font-size: 11px !important;
        }

        .table-signature {
            width: 100%;
            border-collapse: collapse;
        }

        .table-signature td {
            font-size: 11px !important;
            vertical-align: top;
            border: 1px solid black;
            padding: 2px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        @page {
            margin: 184px 25px 25px 25px;
        }

        header {
            position: fixed;
            top: -149px;
            left: 0px;
            right: 0px;
        }

        .upper-bold {
            text-transform: uppercase;
            font-weight: bold;
        }

        .text-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header>
        <table class="table-header">
            <tr>
                <th style="width:80px;" rowspan="4">
                    <img src="{{ asset('images/logo.jpg') }}" alt="logo" style="width:70px;">
                </th>
                <th style="width:100%;text-align:center;" rowspan="2">
                    <span style="font-size:15px;margin-bottom:5px;font-weight:bold;">PT. SINAR CEMARAMAS ABADI</span>
                </th>
                <th style="width:100px;text-weight:bold" class="text-left">No. Dokumen</th>
                <th style="width:80px;" class="text-left">: FR-WH-06</th>
            </tr>
            <tr>

                <th class="text-left">Status Revisi</th>
                <th class="text-left">: 00</th>
            </tr>
            <tr>
                <th rowspan="2">
                    <span style="font-size:17px;margin-bottom:5px;font-weight:bold;">FORMULIR PENERIMAAN BAHAN</span>
                </th>
                <th class="text-left">Tanggal Berlaku</th>
                <th class="text-left">: 3 Juni 2024</th>
            </tr>
            <tr>
                <th class="text-left">Halaman</th>
                <th class="text-left">: </th>
            </tr>
        </table>
        <table class="table-subheader">
            <tr>
                <td style="width:10%;vertical-align:top;">Tanggal</td>
                <td style="width:40%;vertical-align:top;">: {{ $data->tanggal_pembelian }}</td>
                <td style="width:10%;vertical-align:top;">No. PO</td>
                <td style="width:40%;vertical-align:top;">: {{ $data->nomor_po_pembelian }}</td>
            </tr>
            <tr>
                <td style="vertical-align:top;">No. Form</td>
                <td style="vertical-align:top;">: {{ $data->nama_pembelian }}</td>
                <td style="vertical-align:top;">Pemasok</td>
                <td style="vertical-align:top;height:28px;">: {{ $data->pemasok->nama_pemasok }}</td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <th style="width:5%;">NO</th>
                <th style="width:32.5%">NAMA BAHAN</th>
                <th style="width:8%;">BRUTO</th>
                <th style="width:8%;">NETTO</th>
                <th style="width:8%;">SATUAN</th>
                <th style="width:28.5%;">KETERANGAN</th>
            </tr>
        </table>
    </header>
    <main>
        <table class="table">
            @foreach ($data->details as $key => $detail)
                <tr>
                    <td class="text-center" style="width:5%;">{{ $key + 1 }}</td>
                    <td style="width:32.5%">{{ $detail->text }}</td>
                    <td class="text-center" style="width:8%">{{ $detail->total_jumlah_purchase }}</td>
                    <td class="text-center" style="width:8%">{{ $detail->jumlah_pembelian_detail }}</td>
                    <td class="text-center" style="width:8%;">{{ $detail->satuan->nama_satuan_barang }}</td>
                    <td style="width:28.5%;">
                        @if ($detail->jumlah_zak > 0)
                            {{ $detail->jumlah_zak }} {{ $detail->nama_wrapper }}
                        @else
                            {{ $detail->keterangan_pembelian_detail }}
                        @endif
                    </td>
                </tr>
            @endforeach
            @for ($i = 0; $i < 4 - count($data->details); $i++)
                <tr>
                    <td class="text-center" style="width:5%;">&nbsp;</td>
                    <td style="width:32.5%"></td>
                    <td class="text-center" style="width:8%"></td>
                    <td class="text-center" style="width:8%"></td>
                    <td class="text-center" style="width:8%;"></td>
                    <td style="width:28.5%;"></td>
                </tr>
            @endfor
        </table>
        <table class="table-signature">
            <tr>
                <td rowspan="2"><span style="text-bold">Catatan</span> : {{ $data->keterangan_pembelian }}</td>
                <td style="width:15%;text-align:center;">Diterima oleh</td>
                <td style="width:15%;text-align:center;">Diperiksa oleh</td>
                <td style="width:15%;text-align:center;">Disetujui oleh</td>
            </tr>
            <tr>
                <td style="height:80px;"></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </main>
</body>

</html>
