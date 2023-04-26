<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
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

        h3,
        h4 {
            margin-top: 0px;
            margin-bottom: 0px;
        }
    </style>
</head>

<body>
    <div style="display:flex;margin-bottom:10px;align-items: center;">
        <img src="{{ asset('images/logo2.jpg') }}" style="width:60px;">
        <div style="flex:1;text-align:center;">
            <h3 style="">LAPORAN UANG MUKA PENJUALAN</h3>
        </div>
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
    <table class="table" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Cabang</th>
                <th>Kode Transaksi</th>
                <th>Nomor SO</th>
                <th>Akun Slip</th>
                <th>Mata Uang</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td style="text-align:center;">{{ $key + 1 }}</td>
                    <td>{{ $data->tanggal }}</td>
                    <td>{{ $data->cabang->nama_cabang }}</td>
                    <td>{{ $data->kode_uang_muka_penjualan }}</td>
                    <td>{{ $data->salesOrder->nama_permintaan_penjualan }}</td>
                    <td>{{ $data->slip->nama_slip }}</td>
                    <td>{{ $data->mataUang->nama_mata_uang }}</td>
                    <td style="text-align:right;">{{ formatNumber($data->nominal) }}</td>
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
