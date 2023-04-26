<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LAPORAN QC PENERIMAAN BARANG</title>
    <style>
        body {
            width: 20cm;
            padding-left: 10px;
            padding-right: 10px;
        }

        @page {
            margin: 3cm 2cm 2cm 2cm;
        }

        .table {
            border-collapse: collapse;
        }

        .table th {
            border-top: 0.2mm solid #000;
            border-bottom: 0.2mm solid #000;
            text-align: center;
            border: 1px solid #000;
            font-size: 12px;
        }

        .table tr td {
            padding: 3px;
            border-bottom: 0.2mm solid #000;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 12px;
        }

        td {
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div style="display:flex;margin-bottom:10px;align-items: center;">
        <img src="{{ asset('images/logo2.jpg') }}" style="width:60px;">
        <h3 style="flex:1;text-align:center;">LAPORAN QC PENERIMAAN BARANG</h3>
        <div>
            <table style="width:200px;">
                <tr>
                    <td style="width:60px;font-weight:bold;">Cabang</td>
                    <td>:</td>
                    <td>{{ $cabang }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Tanggal</td>
                    <td>:</td>
                    <td>{{ $date }}</td>
                </tr>
            </table>
        </div>
    </div>
    <table style="width:100%" class="table">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th style="width:111px;">Kode Pembelian</th>
                <th style="width:96px;">Nama Barang</th>
                <th style="width:62px;">Jumlah</th>
                <th style="width:56px;">Status</th>
                <th style="width:100px;">Alasan</th>
                <th style="width:70px;">Tanggal</th>
                <th>Hasil Analisa</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="text-align:center;">{{ $key + 1 }}</td>
                    <td>{{ $data->nama_pembelian }}</td>
                    <td>{{ $data->nama_barang }}</td>
                    <td style="text-align:right">{{ formatNumber($data->jumlah_pembelian_detail) }}
                        {{ $data->nama_satuan_barang }}</td>
                    <td>{{ $arrayStatus[$data->status_qc] }}</td>
                    <td>{{ $data->reason }}</td>
                    <td>{{ $data->tanggal_qc }}</td>
                    <td>
                        SG : {{ formatNumber($data->sg_pembelian_detail) }} <br>
                        BE : {{ formatNumber($data->be_pembelian_detail) }} <br>
                        PH : {{ formatNumber($data->ph_pembelian_detail) }} <br>
                        Warna : {{ $data->warna_pembelian_detail }} <br>
                        Bentuk : {{ $data->bentuk_pembelian_detail }} <br>
                        Keterangan : {{ $data->keterangan_pembelian_detail }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
<script>
    window.print()
    window.addEventListener('afterprint', (e) => {
        window.close()
    })
</script>

</html>
