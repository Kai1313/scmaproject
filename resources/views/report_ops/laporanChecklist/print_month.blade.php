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
                <img src="{{ asset('img/logo_scma.png') }}" alt="" width="70">
            </td>
            <td class="header">PT SINAR CEMARAMAS ABADI</td>
            <td class="header left" style="width:171px;">No Dokumen : </td>
        </tr>
        <tr>
            <td class="header"></td>
            <td class="header left">Revisi : </td>
        </tr>
        <tr class="header">
            <td>Bulan : {{ $month }}</td>
            <td class="header left">Tgl. Berlaku : </td>
        </tr>
    </table>
    <table class="no-border-horizontal">
        <tr>
            <td></td>
        </tr>
    </table>
    <div>Lokasi : {{ $location }}, Pengguna : {{ $group->nama_grup_pengguna }}</div>
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
        @foreach ($jobs as $key => $job)
            <tr>
                <td class="center">{{ $key + 1 }}</td>
                <td>{{ $job->nama_pekerjaan }} {{ $job->id_pekerjaan }}</td>
                @for ($i = 1; $i <= $count_date; $i++)
                    <td class="center">

                    </td>
                @endfor
            </tr>
        @endforeach
    </table>
    {{-- @php
        $counter = 0;
    @endphp
    @foreach ($groupJobs as $ck => $cat)
        <div style="float:left;width:200px;margin-top:10px;">
            <b>Jenis Perawatan {{ isset($alpa[$ck]) ? $alpa[$ck] : '' }}</b>
            <ol style="margin-left:-25px;">
                @foreach ($cat as $k => $c)
                    <li>{{ $c->job_desc }}</li>
                @endforeach
            </ol>
        </div>
        @if ($counter % 5 == 4)
            <br clear="all" />
        @endif

        @php
            $counter++;
        @endphp
    @endforeach --}}
    <table style="width:100%">
        <tr>
            <td></td>
            <td class="center"><b>Dibuat oleh,</b></td>
            <td></td>
            <td class="center"><b>Mengetahui,</b></td>
        </tr>
        <tr>
            <td style="height:70px;width:30px;"></td>
            <td style="border-bottom:1px solid black"></td>
            <td style="width:50%;"></td>
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
