<link rel="shortcut icon" href="../images/logo.png" type="image/x-icon" />
<style type="text/css">
    * {
        font-family: Arial;
        margin: 0px;
        padding: 0px;
    }

    @page {
        margin-left: 3cm 2cm 2cm 2cm;
    }

    table.grid {
        width: 19cm;
        font-size: 12px;
        margin-left: 0.5cm;
        border-collapse: collapse;
    }

    table.grid th {
        padding: 5px;
    }

    table.grid th {
        background: #F0F0F0;
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

    center {
        padding: 8px;
    }

    .atas {
        display: block;
        width: 19cm;
        margin-left: 0.5cm;
        margin-top: 0.0cm;
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
        width: 19cm;
        page-break-after: always;
        margin-bottom: 10px;
    }

    .akhir {
        width: 19cm;
        font-size: 13px;
    }

    .page {
        width: 19cm;
        font-size: 12px;
        padding: 10px;
    }

    table.footer {
        width: 18.5cm;
        font-size: 12px;
        margin-left: 0.7cm;
        border-collapse: collapse;
    }

    @media print {

        .no-print,
        .no-print * {
            display: none !important;
        }
    }
</style>
<button type="button" class="no-print" onclick="window.print()">Print</button><br />
<div class="atas">
    <table style="width:100%;">
        <tr>
            <td style="width:80px;height:60px;">
                <img src='{{ env('OLD_API_ROOT') }}/uploads/logo2.jpg' style='padding:0; margin:0;' width='60'>
            </td>
            <td>
                <h1>PT. SINAR CEMARAMAS ABADI</h1>
            </td>
            <td style="text-align:right;width:100px;">
                <h1>Permintaan Pembelian</h1>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td valign="top" style="width:70%;">
                <table class='kiri' style="width:100%;">
                    <tr>
                        <td width="80"><b>Cabang</b></td>
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
            <td valign="top" style="width:80%;">
                <table class='kanan' style="width:100%;">
                    <tr>
                        <td width="80"><b>Kode Permintaan</b></td>
                        <td width="5">:</td>
                        <td>{{ $data->purchase_request_code }}</td>
                    </tr>
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
</div>
<table class="grid" width="100%">
    <tr>
        <th width="20">No</th>
        <th width="200">Nama Barang</th>
        <th width="50">Jumlah</th>
        <th width="50">Satuan</th>
        <th>Catatan</th>
    </tr>
    @foreach ($data->formatdetail as $key => $detail)
        <tr>
            <td align="center">{{ $key + 1 }}</td>
            <td>{{ $detail->nama_barang }}</td>
            <td align="right">{{ $detail->qty }}</td>
            <td align="center">{{ $detail->nama_satuan_barang }}</td>
            <td align="right">{{ $detail->notes }}</td>
        </tr>
    @endforeach
</table>
<table width="100%" class="footer" style="margin-top: 4px">
    <tr>
        <td width="100%" valign="top" align="left" colspan="3">
            <b>Catatan : </b> {{ $data->catatan }}<br /><br />
        </td>
    </tr>
</table>
<script>
    //window.print();
</script>
