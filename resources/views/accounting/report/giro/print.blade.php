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
                <th colspan="2" style="text-align: center; padding-bottom: 20px;"><b>Report Giro</b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%">
                    <table>
                        <img src="{{ asset('images/logo2.jpg') }}" alt="Logo Perusahaan" style="width:60px; height:45px">
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
                            <td width="20%">Slip</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $slip }}
                            </td>
                        </tr>
                        <tr>
                            <td width="20%">Tipe</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $tipe }}
                            </td>
                        </tr>
                        <tr>
                            <td width="20%">Tanggal</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $tanggal }}
                            </td>
                        </tr>
                        <tr>
                            <td width="20%">Status</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $status }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table width="100%" class="table-bordered" style="margin-top: 20px">
        <thead style="font-size: 14px;">
            <tr style="font-size: 12px;">
                <th colspan="6">Giro</th>
                <th colspan="3">Cair</th>
                <th colspan="2">Tolak</th>
            </tr>
            <tr style="font-size: 12px;">
                <th width="10%">No Jurnal</th>
                <th width="7%">Tanggal Jurnal</th>
                <th width="11%">No Giro</th>
                <th width="7%">Tanggal Giro</th>
                <th width="7%">Jatuh Tempo Giro</th>
                <th width="11%">Total</th>
                <th width="11%">No Jurnal</th>
                <th width="7%">Tanggal</th>
                <th width="11%">Slip</th>
                <th width="11%">No Jurnal</th>
                <th width="7%">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $val)
            <tr style="font-size: 10px;">
                <td align="left">{{ $val->kode_jurnal }}</td>
                <td align="center">{{ $val->tanggal_jurnal }}</td>
                <td align="left">{{ $val->no_giro }}</td>
                <td align="center">{{ $val->tanggal_giro }}</td>
                <td align="center">{{ $val->tanggal_giro_jt }}</td>
                <td align="right">{{ number_format($val->total, 2, ",", ".") }}</td>
                <td align="left">{{ $val->cair_kode_jurnal }}</td>
                <td align="center">{{ $val->cair_tanggal_giro }}</td>
                <td align="left">{{ $val->cair_slip }}</td>
                <td align="left">{{ $val->tolak_kode_jurnal }}</td>
                <td align="center">{{ $val->tolak_tanggal_giro }}</td>
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