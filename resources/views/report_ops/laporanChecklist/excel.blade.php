<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        th {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <th colspan="4" style="text-align:center;">LAPORAN CHECKLIST PEKERJAAN
                {{ strtoupper($group->nama_grup_pengguna) }}</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align:center;">
                LOKASI {{ strtoupper($req->location) }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align:center;">TANGGAL {{ date('d-m-Y', strtotime($req->date)) }}</th>
        </tr>
    </table>
    <table>
        <tr>
            <th colspan="2" style="text-align:center;vertical-align:middle;">Lokasi</th>
            <th>Checklist<br>Pengguna</th>
            <th>Checklist<br>Pemeriksa</th>
        </tr>
        @foreach ($locations as $location)
            <tr>
                <th colspan="4">{{ $location->nama_objek_kerja }}</th>
            </tr>
            @foreach ($answers->where('id_objek_kerja', $location->id_objek_kerja) as $ans)
                @for ($i = 1; $i < 26; $i++)
                    @if (isset($jobs[$ans->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}]))
                        <tr>
                            <td width="5">{{ $i }}.</td>
                            <td width="50">
                                {{ $jobs[$ans->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}] }}
                            </td>
                            <td style="text-align:center;">
                                @if ($ans->{'jawaban' . $i . '_jawaban_checklist_pekerjaan'})
                                    OK
                                @endif
                            </td>
                            <td></td>
                        </tr>
                    @endif
                @endfor
            @endforeach
            <tr>
                <td colspan="2"></td>
            </tr>
        @endforeach
    </table>
</body>

</html>
