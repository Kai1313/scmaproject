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
                <th colspan="2" style="text-align: center; padding-bottom: 20px;"><b>Report {{ ucfirst($type) }} Ledger</b></th>
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
                                {{ $cabang }}
                            </td>
                        </tr>
                        <tr>
                            <td width="20%">Periode</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $start_date.' - '.$end_date }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    @if ($type == "recap")
        <table width="100%" class="table-bordered" style="margin-top: 20px">
            <thead>
                <tr style="font-size: 14px;">
                    <th width="15%">Kode Akun</th>
                    <th width="25%">Nama Akun</th>
                    <th width="15%">Saldo Awal</th>
                    <th width="15%">Debet</th>
                    <th width="15%">Kredit</th>
                    <th width="15%">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr style="font-size: 12px;">
                        <td align="center">{{ $item->kode_akun }}</td>
                        <td align="center">{{ $item->nama_akun }}</td>
                        <td align="right">{{ number_format($item->saldo_awal, 2, ",", ".") }}</td>
                        <td align="right">{{ number_format($item->debet, 2, ",", ".") }}</td>
                        <td align="right">{{ number_format($item->kredit, 2, ",", ".") }}</td>
                        <td align="right">{{ number_format($item->saldo_akhir, 2, ",", ".") }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type == "detail")
        <table width="100%" class="table-bordered" style="margin-top: 20px">
            <thead>
                <tr style="font-size: 14px;">
                    <th width="10%">Tanggal</th>
                    <th width="10%">No Jurnal</th>
                    <th width="10%">Kode Akun</th>
                    <th width="15%">Nama Akun</th>
                    <th width="15%">Keterangan</th>
                    <th width="10%">ID Transaksi</th>
                    <th width="10%">Debet</th>
                    <th width="10%">Kredit</th>
                    <th width="10%">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr style="font-size: 10px;">
                        <td align="center">{{ $item->tanggal_jurnal }}</td>
                        <td align="center">{{ $item->kode_jurnal }}</td>
                        <td align="center">{{ $item->kode_akun }}</td>
                        <td align="center">{{ $item->nama_akun }}</td>
                        <td align="left">{{ $item->keterangan }}</td>
                        <td align="center">{{ $item->id_transaksi }}</td>
                        <td align="right">{{ number_format($item->debet, 2, ",", ".") }}</td>
                        <td align="right">{{ number_format($item->kredit, 2, ",", ".") }}</td>
                        <td align="right">{{ number_format($item->saldo_balance, 2, ",", ".") }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
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