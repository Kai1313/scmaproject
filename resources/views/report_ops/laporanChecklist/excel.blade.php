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
            <th style="text-align:center;vertical-align:middle;">Keterangan</th>
            <th style="text-align:center;">Checklist<br>Pengguna</th>
            <th style="text-align:center;">Checklist<br>Pemeriksa</th>
        </tr>
        @foreach ($locations as $location)
            <tr>
                <th colspan="4">{{ $location->nama_objek_kerja }}</th>
            </tr>
            @php
                $counter = 1;
            @endphp
            @foreach ($jobs->where('id_objek_kerja', $location->id_objek_kerja) as $i => $job)
                <tr>
                    <td width="5">{{ $counter }}</td>
                    <td width="40" style="word-wrap: break-word;">{{ $job->nama_pekerjaan }}</td>
                    @if (isset($answers[$location->id_objek_kerja . '-' . $job->id_pekerjaan]))
                        <td width="30" style="word-wrap: break-word">
                            {{ $answers[$location->id_objek_kerja . '-' . $job->id_pekerjaan]['keterangan'] }}
                        </td>
                        <td style="text-align:center;">
                            @if ($answers[$location->id_objek_kerja . '-' . $job->id_pekerjaan]['jawaban'] == '1')
                                OK
                            @endif
                        </td>
                    @else
                        <td width="30"></td>
                        <td></td>
                    @endif
                    <td></td>
                </tr>
                @if (isset($answers[$location->id_objek_kerja . '-' . $job->id_pekerjaan]) &&
                        $answers[$location->id_objek_kerja . '-' . $job->id_pekerjaan]['keterangan'] != '')
                    <tr>
                        <td></td>
                        <td colspan="4">
                            @foreach ($answers[$location->id_objek_kerja . '-' . $job->id_pekerjaan]['media'] as $me)
                                <img src="{{ $me }}" alt="" width="100">
                            @endforeach
                        </td>
                    </tr>
                @endif
                @php
                    $counter++;
                @endphp
            @endforeach
            <tr>
                <td colspan="5"></td>
            </tr>
        @endforeach
    </table>
</body>

</html>
