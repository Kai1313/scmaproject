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
                <th colspan="2" style="text-align: center; padding-bottom: 20px;"><b>Report Neraca {{ ucfirst($type) }}</b></th>
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
                            <td width="20%">Periode</td>
                            <td width="2%">:</td>
                            <td width="78%">
                                {{ $periode }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table width="100%" class="table-bordered" style="margin-top: 20px">
        <thead>
            <tr style="font-size: 18px;">
                <th width="70%">Header</th>
                <th width="30%">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $fontSize = 18;
                $space = 0;
            @endphp
            @include('accounting.report.balance.balance-list',['data' => $data, 'fontSize' => $fontSize, 'space' => ($space)])
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
