<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota Pengeluaran barang {{ $data->nomor_npb_penjualan }}</title>
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
            font-weight: normal;
            padding: 2px;
        }

        .table-subheader {
            width: 100%;
        }

        .table-subheader td {
            font-size: 11px !important;
        }

        .table-signature {
            width: 100%;
            border-spacing: 0.5em 0.5em;
        }

        .table-signature td {
            font-size: 11px !important;
            vertical-align: top;
            padding: 0px 5px 0px 5px;
            text-align: center;
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
            margin: 272px 25px 25px 25px;
        }

        header {
            position: fixed;
            top: -238px;
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
                <th style="width:100%;text-align:center;" rowspan="4">
                    <span style="font-size:17px;margin-bottom:5px;font-weight:bold;">PT. SINAR CEMARAMAS ABADI</span>
                    <br>
                    <span style="font-weight:normal">Pergudangan Meiko Abadi 1, Blok B No. 17-19, Wedi, Kec. Gedangan,
                        Kabupaten Sidoarjo, Jawa Timur
                        61254</span>
                    <br>
                    <span style="font-weight:normal">(031) 8015320 / (031) 8014717</span>
                </th>
                <th style="width:100px;" class="text-left">No. Dokumen</th>
                <th style="width:80px;" class="text-left">: FR-WH-04</th>
            </tr>
            <tr>
                <th class="text-left">Status Revisi</th>
                <th class="text-left">: 00</th>
            </tr>
            <tr>
                <th class="text-left">Tanggal Berlaku</th>
                <th class="text-left">: 3 Juni 2024</th>
            </tr>
            <tr>
                <th class="text-left">Halaman</th>
                <th class="text-left">: </th>
            </tr>
        </table>
        <table style="width:100%;">
            <tr>
                <th><span style="font-size:17px;">SURAT JALAN</span></th>
            </tr>
        </table>
        <table class="table-subheader">
            <tr>
                <td width="80"><b>Tanggal</b></td>
                <td width="5">:</td>
                <td class="upper-bold">
                    @php
                        $date = $data->tanggal_penjualan;
                        $exp = explode('-', $date);
                    @endphp
                    {{ $exp[2] }} {{ $month[(int) $exp[1] - 1] }} {{ $exp[0] }}
                </td>
            </tr>
            <tr>
                <td><b>No. Surat Jalan</b></td>
                <td>:</td>
                <td class="upper-bold">{{ $data->nomor_npb_penjualan }}</td>
            </tr>
            <tr>
                <td><b>No. Dokumen Lain</b></td>
                <td>:</td>
                <td class="upper-bold">{{ $data->nama_penjualan }}</td>
            </tr>
            <tr>
                <td><b>Penerima</b></td>
                <td>:</td>
                <td class="upper-bold">{{ $data->nama_pelanggan }}</td>
            </tr>
            <tr>
                <td style="vertical-align:top;"><b>Alamat Penerima</b></td>
                <td style="vertical-align:top;">:</td>
                <td class="upper-bold" style="height:30px;vertical-align:top;">
                    {{ $data->alamat_pelanggan }}
                </td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:32.5%">Nama Barang</th>
                <th style="width:10%;">Jumlah</th>
                <th style="width:10%;">Satuan</th>
                <th style="width:32.5%;">Keterangan</th>
            </tr>
        </table>
    </header>
    <main>
        <table class="table">
            @foreach ($details as $key => $detail)
                <tr>
                    <td class="text-center" style="width:5%;">{{ $key + 1 }}</td>
                    <td style="width:32.5%">{{ $detail->nama_barang }}</td>
                    <td class="text-center" style="width:10%">{{ $detail->sum_total_weight }}</td>
                    <td class="text-center" style="width:10%;">{{ $detail->nama_satuan_barang }}</td>
                    <td style="width:32.5%;">{{ $detail->total }} {{ $detail->nama_satuan_baru }} @
                        {{ $detail->jumlah_penjualan_detail }}
                        {{ $detail->nama_satuan_barang }}</td>
                </tr>
            @endforeach
        </table>
        <table class="table-subheader">
            <tr>
                <td width="40"><b>Catatan</b></td>
                <td width="5">:</td>
                <td>
                    @if ($data->nama_ekspedisi)
                        Dikirim Via {{ $data->nama_ekspedisi }}
                    @endif
                </td>
            </tr>
        </table>
        <table class="table-signature">
            <tr>
                <td style="height:70px;vertical-align:top;" class="text-bold">Dibuat,</td>
                <td></td>
                <td class="text-bold">Mengetahui,</td>
                <td></td>
                <td class="text-bold">Pengirim,</td>
                <td></td>
                <td class="text-bold">Penerima,</td>
            </tr>
            <tr>
                <td style="vertical-align:bottom;">
                    (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                </td>
                <td></td>
                <td style="vertical-align:bottom;">
                    (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                </td>
                <td></td>
                <td style="vertical-align:bottom;">
                    (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                </td>
                <td></td>
                <td style="vertical-align:bottom;">
                    (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                </td>
            </tr>
        </table>
    </main>
</body>

</html>
