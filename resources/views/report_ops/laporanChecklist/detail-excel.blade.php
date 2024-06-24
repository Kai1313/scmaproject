<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        td {
            border: 0.5px solid black;
            vertical-align: middle;
            /* font-weight: bold; */
        }

        table {
            /* width: 718px; */
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td style="width:30px"></td>
            <td style="width:80px;"></td>
            <td style="width:100px;"></td>
            <td style="width:200px;"></td>
            <td style="width:40px;"></td>
            <td style="width:70px;"></td>
            <td style="width:200px;"></td>
        </tr>
        <tr>
            <td rowspan="3" style="text-align:center;border: 0.5px solid black;" colspan="3">
                <img src="{{ public_path('images/logo.png') }}" alt="" width="50">
            </td>
            <td style="text-align:center;border: 0.5px solid black;font-weight:bold;" colspan="3">PT SINAR CEMARAMAS
                ABADI</td>
            <td style="border: 0.5px solid black;font-weight:bold;">No Dokumen : FR-HRGA-13</td>
        </tr>
        <tr>
            <td rowspan="2" style="text-align:center;border: 0.5px solid black;font-weight:bold;" colspan="3">
                CHECKLIST PERAWATAN
                <br> (berlaku untuk Gudang
                dan Prasarana)
            </td>
            <td style="border: 0.5px solid black;font-weight:bold;">Revisi : 00 </td>
        </tr>
        <tr>
            <td style="border: 0.5px solid black;font-weight:bold;">Tgl. Berlaku : 3 Juni 2024</td>
        </tr>
        <tr>
            <td colspan="2"
                style="border-top: 0.5px solid black;border-bottom: 0.5px solid black;border-left: 0.5px solid black;vertical-align:top;font-weight:bold;">
                TANGGAL <br>
                CHECKLIST <br>
                GRUP
            </td>
            <td colspan="2"
                style="border-top: 0.5px solid black;border-bottom:0.5px solid black;vertical-align:top;">
                : {{ date('d/m/Y', strtotime($data->tanggal_jawaban_checklist_pekerjaan)) }} <br>
                : {{ $data->nama_objek_kerja }} <br>
                : {{ $data->nama_grup_pengguna }}
            </td>
            <td colspan="2"
                style="border-top: 0.5px solid black;border-bottom:0.5px solid black;vertical-align:top;font-weight:bold;">
                KARYAWAN <br>
                PEMERIKSA
            </td>
            <td
                style="border-top: 0.5px solid black;border-bottom:0.5px solid black;border-right:0.5px solid black;vertical-align:top;">
                : {{ $data->nama_pengguna }} <br>
                : {{ $data->nama_pengguna_checker }}
            </td>
        </tr>
        <tr>
            <td rowspan="2" style="text-align:center;vertical-align:top;border: 0.5px solid black;font-weight:bold;">
                NO</td>
            <td rowspan="2" style="text-align:center;vertical-align:top;border: 0.5px solid black;font-weight:bold;"
                colspan="3">
                NAMA DAN NOMOR</td>
            <td colspan="2" style="text-align:center;vertical-align:top;border: 0.5px solid black;font-weight:bold;">
                KONDISI</td>
            <td style="text-align:center;vertical-align:top;border: 0.5px solid black;font-weight:bold;">KETERANGAN</td>
        </tr>
        <tr>
            <td style="text-align: center;border: 0.5px solid black;font-weight:bold;">OK</td>
            <td style="text-align: center;border: 0.5px solid black;font-weight:bold;">NOT OK</td>
            <td style="text-align: center;border: 0.5px solid black;font-weight:bold;">Informasi atau Perbaikan</td>
        </tr>
        @for ($i = 1; $i <= 25; $i++)
            @if ($data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'})
                <tr>
                    <td style="text-align:center;border: 0.5px solid black;">{{ $i }}</td>
                    <td colspan="3" style="text-wrap: wrap;border: 0.5px solid black;">
                        {{ $jobs[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}] }}</td>
                    @if ($data->{'jawaban' . $i . '_jawaban_checklist_pekerjaan'} == '1')
                        <td style="text-align:center;border: 0.5px solid black;">
                            @if (!$data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'})
                                v
                            @endif
                        </td>
                        <td style="text-align:center;border: 0.5px solid black;">
                            @if ($data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'})
                                v
                            @endif
                        </td>
                    @else
                        <td style="border: 0.5px solid black;"></td>
                        <td style="border: 0.5px solid black;"></td>
                    @endif
                    <td style="border: 0.5px solid black;">
                        @if ($data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'})
                            {{ $data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'} }}
                        @endif
                    </td>
                </tr>
            @endif
        @endfor
        <tr>
            <td colspan="7"></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2" style="text-align: center;">Dilaporkan oleh,</td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: center;">Mengetahui,</td>
        </tr>
        <tr>
            <td style="height:70px;"></td>
            <td colspan="2" style="border-bottom: 0.5px solid black;"></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="border-bottom: 0.5px solid black;"></td>
        </tr>
    </table>
</body>

</html>
