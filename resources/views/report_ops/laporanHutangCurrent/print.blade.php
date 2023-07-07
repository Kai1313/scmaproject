<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Hutang </title>
    <style>
        .table {
            border-collapse: collapse;
        }

        .table td {
            font-size: 10px;
            border: #000000 solid thin;
        }

        .table th {
            font-size: 12px;
            border: #000000 solid thin;
            max-width: 150px;
            text-align: center;
            font-weight: bold;
        }

        .number {
            text-align: right;
        }

        .table-header td {
            font-size: 15px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table style="width:100%;margin-bottom:10px;" class="table-header">
        <tr>
            <td style="width:80px;"><img src="{{ asset('images/logo2.jpg') }}" alt="logo" style="width:70px;"></td>
            <td style="text-align:center;">LAPORAN HUTANG </td>
        </tr>
    </table>
    <table style="width:100%;margin-bottom:5px;" class="table">
        <tr>
            <td>Cabang : {{ $cabang }}</td>
            <td>Tanggal : {{ $date }}</td>
        </tr>
    </table>
    <table width="100%" class="table">
        <thead>
            <tr>
                <th>Kode Pemasok</th>
                <th>Nama Pemasok</th>
                <th>No. Faktur</th>
                <th>Tgl Faktur</th>
                <th>Jatuh Tempo</th>
                <th>Nilai Faktur</th>
                <th>Hutang Asing</th>
                <th>Hutang Pajak</th>
                <th>Umur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $key => $data)
                <tr>
                    <td>{{ $data->kode_pemasok }}</td>
                    <td>{{ $data->nama_pemasok }}</td>
                    <td>{{ $data->id_transaksi }}</td>
                    <td>{{ $data->tanggal_pembelian }}</td>
                    <td>{{ $data->top }}</td>
                    <td class="number">{{ formatNumber($data->mtotal_pembelian, 2) }}</td>
                    <td class="number">{{ formatNumber($data->sisa, 2) }}</td>
                    <td class="number">{{ formatNumber($data->sisa_tax, 2) }}</td>
                    <td class="number">{{ $data->aging }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
