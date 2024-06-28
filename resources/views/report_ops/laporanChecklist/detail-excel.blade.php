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
            width: 718px;
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
                <img src="{{ asset('images/logo.png') }}" alt="" width="50">
            </td>
            <td class="header">PT SINAR CEMARAMAS ABADI</td>
            <td class="header left" style="width:171px;">No Dokumen : FR-HRGA-13</td>
        </tr>
        <tr>
            <td rowspan="2" class="header">
                CHECKLIST PERAWATAN
                <br> (berlaku untuk Gudang
                dan Prasarana)
            </td>
            <td class="header left">Revisi : 00 </td>
        </tr>
        <tr>
            <td class="header left">Tgl. Berlaku : 3 Juni 2024</td>
        </tr>
    </table>
    <table class="table no-border-horizontal">
        <tr>
            <td class="header left right-white" style="vertical-align: middle;width:100px;height:55px;">
                TANGGAL <br>
                LOKASI <br>
                GRUP
            </td>
            <td class="right-white" style="vertical-align: middle;width:340px;">
                : {{ date('d/m/Y', strtotime($data->tanggal_jawaban_checklist_pekerjaan)) }} <br>
                : {{ $data->nama_objek_kerja }} <br>
                : {{ $data->nama_grup_pengguna }}
            </td>
            <td class="header left right-white" style="vertical-align: middle;width:100px;">
                KARYAWAN <br>
                PEMERIKSA
            </td>
            <td class="left-white" style="vertical-align: middle;">
                : {{ $data->nama_pengguna }} <br>
                : {{ $data->nama_pengguna_checker }}
            </td>
        </tr>
    </table>
    <table class="table">
        <tr>
            <td rowspan="2" style="width:30px;" class="header">NO</td>
            <td rowspan="2" style="width:250px;" class="header">NAMA DAN NOMOR</td>
            <td colspan="2" style="" class="header">KONDISI</td>
            <td style="" class="header">KETERANGAN</td>
        </tr>
        <tr>
            <td class="header" style="width:30px;">OK</td>
            <td class="header" style="width:51px;">NOT OK</td>
            <td class="header">Informasi atau Perbaikan</td>
        </tr>
        @for ($i = 1; $i <= 25; $i++)
            @if ($data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'})
                <tr>
                    <td class="center">{{ $i }}</td>
                    <td style="text-wrap: wrap;">
                        {{ $jobs[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}] }}</td>
                    @if ($data->{'jawaban' . $i . '_jawaban_checklist_pekerjaan'} == '1')
                        <td class="center">
                            @if (!$data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'})
                                v
                            @endif
                        </td>
                        <td class="center">
                            @if ($data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'})
                                v
                            @endif
                        </td>
                    @else
                        <td></td>
                        <td></td>
                    @endif
                    <td>
                        @if ($data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'})
                            {{ $data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'} }}
                        @endif
                    </td>
                </tr>
            @endif
        @endfor
    </table>
    <br>
    <table class="table-no-border">
        <tr>
            <td width="20px;"></td>
            <td class="center">Dilaporkan oleh,</td>
            <td width="430px;"></td>
            <td class="center">Mengetahui,</td>
        </tr>
        <tr>
            <td style="height:70px;"></td>
            <td style="border-bottom: 0.5px solid black;"></td>
            <td></td>
            <td style="border-bottom: 0.5px solid black;"></td>
        </tr>
    </table>
</body>
<script>
    window.print()
    window.addEventListener('afterprint', (e) => {
        window.close()
    })
</script>

</html>
