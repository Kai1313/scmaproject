<html>

<head>
    <title>Print Slip Jurnal</title>
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
                <th colspan="2" style="text-align: center; padding-bottom: 20px;"><b>Bukti
                        {{ ucwords($data_jurnal_header->jenis_name) }}</b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%">
                    <table>
                        <tr>
                            <td width="35%">Kode Jurnal</td>
                            <td width="2%">:</td>
                            <td width="63%">{{ $data_jurnal_header->kode_jurnal }}</td>
                        </tr>
                        <tr>
                            <td width="35%">Tanggal</td>
                            <td width="2%">:</td>
                            <td width="63%">
                                <b>{{ date('d-m-Y', strtotime($data_jurnal_header->tanggal_jurnal)) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td width="35%">Cabang</td>
                            <td width="2%">:</td>
                            <td width="63%">{{ $data_jurnal_header->nama_cabang }}</td>
                        </tr>
                    </table>
                </td>
                <td width="50%">
                    <table>
                        <tr>
                            <td width="35%">Slip</td>
                            <td width="2%">:</td>
                            <td width="63%">{{ $data_jurnal_header->kode_slip }}</td>
                        </tr>
                        <tr>
                            <td width="35%">Catatan</td>
                            <td width="2%">:</td>
                            <td width="63%">{{ $data_jurnal_header->catatan }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table width="100%" class="table-bordered" style="margin-top: 20px">
        <thead>
            <tr>
                <th width="30%">Akun</th>
                <th width="40%">Keterangan</th>
                {{-- <th width="30%">Debet</th> --}}
                {{-- <th width="30%">Kredit</th> --}}
                <th width="30%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebet = 0;
                $totalCredit = 0;
            @endphp
            @foreach ($data_jurnal_detail as $detail)
                @php
                    $totalDebet += $detail->debet;
                    $totalCredit += $detail->credit;
                @endphp
                <tr>
                    <td>
                        {{ $detail->kode_akun }} - {{ $detail->nama_akun }}
                    </td>
                    <td>
                        {{ $detail->keterangan }}
                    </td>
                    @if (in_array($data_jurnal_header->jenis_jurnal, ['KK', 'BK', 'PG']))
                        <td style="text-align: right">
                            {{ number_format($detail->debet, 2) }}
                        </td>
                    @elseif(in_array($data_jurnal_header->jenis_jurnal, ['KM', 'BM', 'HG']))
                        <td style="text-align: right">
                            {{ number_format($detail->credit, 2) }}
                        </td>
                    @endif
                </tr>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: center;">Total</td>
                @if (in_array($data_jurnal_header->jenis_jurnal, ['KK', 'BK', 'PG']))
                    <td style="text-align: right">{{ number_format($totalDebet, 2) }}</td>
                @elseif(in_array($data_jurnal_header->jenis_jurnal, ['KM', 'BM', 'HG']))
                    <td style="text-align: right">{{ number_format($totalCredit, 2) }}</td>
                @endif
            </tr>
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
