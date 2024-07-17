<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        .table td {
            border: 0.5px solid black;
            vertical-align: middle;
            padding: 5px;
            /* font-weight: bold; */
        }

        body {
            font-family: Arial;
            font-size: 13px;
            width: 1020px;
        }

        .table {
            /* width: 718px; */
            border-collapse: collapse;
            width: 100%;
        }

        .table-no-border {
            border: 0 !important;
        }

        .header {
            text-align: center;
            font-weight: bold;
        }

        .left {
            text-align: left;
        }

        .no-border-horizontal td {
            border-top: none;
            border-bottom: none;
        }

        .right-white {
            border-right: 1px solid white !important;
        }

        .left-white {
            border-left: 1px solid white !important;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>
    <table class="table">
        <tr>
            <td rowspan="3" class="header" style="width:150px;">
                <img src="{{ asset('images/logo.png') }}" alt="" width="70">
            </td>
            <td class="header">PT SINAR CEMARAMAS ABADI</td>
            <td class="header left right-white" style="width:90px;">No Dokumen </td>
            <td style="width:100px;">: </td>
        </tr>
        <tr>
            <td class="header">LAPORAN CHECKLIST PEKERJAAN</td>
            <td class="header left right-white">Revisi </td>
            <td>: 00</td>
        </tr>
        <tr>
            <td class="header">Bulan : {{ $month }} {{ $year }} </td>
            <td class="header left right-white">Tgl. Berlaku </td>
            <td>: 03 Juni 2024</td>
        </tr>
    </table>
    <table class="no-border-horizontal">
        <tr>
            <td></td>
        </tr>
    </table>
    <div style="font-weight:bold;margin-top:5px;margin-bottom:10px;">Lokasi : {{ $object->nama_objek_kerja }}</div>
    <table class="table">
        <tr>
            <td style="width:30px;" class="header" rowspan="2">No</td>
            <td style="width:200px;" class="header" rowspan="2">Kegiatan</td>
            <td class="header" colspan="{{ $count_date }}">Tanggal</td>
        </tr>
        <tr>
            @for ($i = 1; $i <= $count_date; $i++)
                <td class="header" style="width:15px;">{{ $i }}</td>
            @endfor
        </tr>
        @php
            $counter = 0;
        @endphp
        @foreach ($jobs as $key => $job)
            @php
                $counter++;
            @endphp
            <tr>
                <td class="center">{{ $counter }}</td>
                <td>{{ $job }}</td>
                @for ($i = 1; $i <= $count_date; $i++)
                    <td class="center">
                        @if ($answers[$key . '-' . $i])
                            <img src="{{ asset('images/check-icon.png') }}" alt="" style="width:15px;">
                        @endif
                    </td>
                @endfor
            </tr>
        @endforeach
    </table>
    <table style="width:100%;margin-top:20px;">
        <tr>
            <td></td>
            <td class="center"><b>Dibuat oleh,</b></td>
            <td></td>
            <td class="center"><b>Mengetahui,</b></td>
        </tr>
        <tr>
            <td style="height:70px;width:30px;"></td>
            <td style="border-bottom:1px solid black;vertical-align:bottom;text-align:center;">
                {{ session()->get('user')->nama_pengguna }}
            </td>
            <td style="width:70%;"></td>
            <td style="border-bottom:1px solid black"></td>
        </tr>
    </table>
</body>
<script>
    // window.print()
    // window.addEventListener('afterprint', (e) => {
    //     window.close()
    // })
</script>

</html>
