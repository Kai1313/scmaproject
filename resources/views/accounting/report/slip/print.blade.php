<html>

<head>
    <title>Document</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .table-header td {
            vertical-align: top;
        }

        .table-bordered {
            border-collapse: collapse;
        }

        .table-bordered th {
            border: 2px solid black;
        }

        .table-bordered td {
            border: 2px solid black;
        }
    </style>
</head>

<body>
    <table width="100%" class="table-header">
        <thead>
            <tr>
                <th colspan="2" style="text-align: center; padding-bottom: 20px;"><b>Report Slip</b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%">
                    <table border="1px">
                        <th><b>KOP PERUSAHAAN</b></th>
                    </table>
                </td>
                <td width="50%">
                    <table>
                        <tr>
                            <td width="20%">Cabang</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $cabang->nama_cabang }}
                            </td>
                        </tr>
                        <tr>
                            <td width="20%">Slip</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $slip->nama_slip }}
                            </td>
                        </tr>
                        <tr>
                            <td width="20%">From</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $from }}
                            </td>
                        </tr>
                        <tr>
                            <td width="20%">To</td>
                            <td width="2%">:</td>
                            <td width="78%">{{ $to }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table width="100%" class="table-bordered" style="margin-top: 20px">
        <thead>
            <tr style="font-size: 14px;">
                <th width="7%">Tanggal</th>
                <th width="14%">No Jurnal</th>
                <th width="11%">Slip</th>
                <th width="11%">Akun</th>
                <th width="17%">Keterangan</th>
                <th width="14%">ID Transaksi</th>
                <th width="10%">Debet</th>
                <th width="10%">Credit</th>
                <th width="10%">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
            $balance = 0;
            @endphp

            @foreach ($saldo_awal as $data)

            @php
            if($data->debet > 0){
            $balance += $data->debet;
            }

            if($data->credit > 0){
            $balance -= $data->credit;
            }
            @endphp
            <tr style="font-size: 10px;">
                <td align="center">{{ $from }}</td>
                <td align="left">{{ $data->kode_jurnal }}</td>
                <td align="left">{{ $data->nama_slip }}</td>
                <td align="left">{{ $data->nama_akun }}</td>
                <td align="left">{!! str_replace('\n', '<br>', $data->keterangan) !!}</td>
                <td align="left">{{ $data->id_transaksi }}</td>
                <td align="right">{{ number_format($data->debet, 2,",",".") }}</td>
                <td align="right">{{ number_format($data->credit, 2,",",".") }}</td>
                <td align="right">{{ number_format($balance, 2,",",".") }}</td>
            </tr>
            @endforeach
            @foreach ($mutasis as $data)

            @php
            if($data->debet > 0){
            $balance += $data->debet;
            }

            if($data->credit > 0){
            $balance -= $data->credit;
            }
            @endphp
            <tr style="font-size: 10px;">
                <td align="center">{{ $data->tanggal_jurnal }}</td>
                <td align="left">{{ $data->kode_jurnal }}</td>
                <td align="left">{{ $data->nama_slip }}</td>
                <td align="left">{{ $data->nama_akun }}</td>
                <td align="left">{!! $data->keterangan !!}</td>
                <td align="left">{{ $data->id_transaksi }}</td>
                <td align="right">{{ number_format($data->debet, 2,",",".") }}</td>
                <td align="right">{{ number_format($data->credit, 2,",",".") }}</td>
                <td align="right">{{ number_format($balance, 2,",",".") }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <table width="100%" style="margin-top: 50px">
        <tr>
            <td style="text-align: center;" width="33%">
                Dibukukan
                <br>
                <br>
                <br>
                <br>
                ..........
            </td>
            <td style="text-align: center;" width="33%">
                Mengetahui
                <br>
                <br>
                <br>
                <br>
                ..........
            </td>
            <td style="text-align: center;" width="33%">
                Menyetujui
                <br>
                <br>
                <br>
                <br>
                ..........
            </td>
        </tr>
    </table>
</body>

</html>