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
            <th colspan="2" style="text-align:center;vertical-align:middle;" width="20">Lokasi</th>
            <th style="text-align:center;vertical-align:middle;">Keterangan</th>
            <th style="text-align:center;" width="10">Checklist<br>Pengguna</th>
            <th style="text-align:center;" width="10">Checklist<br>Pemeriksa</th>
            <th style="text-align:center;" colspan="3">Gambar</th>
        </tr>
        @foreach ($locations as $location)
            <tr>
                <th colspan="8">{{ $location->nama_objek_kerja }}</th>
            </tr>
            @php
                $counter = 1;
            @endphp
            @foreach ($jobs->where('id_objek_kerja', $location->id_objek_kerja) as $i => $job)
                @php
                    $keyObject = $location->id_objek_kerja . '-' . $job->id_pekerjaan;
                @endphp
                <tr>
                    <td width="5">{{ $counter }}</td>
                    <td width="40" style="word-wrap: break-word;">{{ $job->nama_pekerjaan }}</td>
                    @if (isset($answers[$keyObject]))
                        <td width="30" style="word-wrap: break-word">
                            {{ $answers[$keyObject]['keterangan'] }}
                        </td>
                        <td style="text-align:center;">
                            @if ($answers[$keyObject]['jawaban'] == '1')
                                OK
                            @endif
                        </td>
                    @else
                        <td width="30"></td>
                        <td></td>
                    @endif
                    <td></td>
                    @if (isset($answers[$keyObject]) && $answers[$keyObject]['keterangan'] != '')
                        @foreach ($answers[$keyObject]['media'] as $ky => $me)
                            <td>
                                <img src="{{ $me }}" alt="" width="100"
                                    style="padding-left:100px;">
                            </td>
                        @endforeach
                    @endif
                    @php
                        $counter++;
                    @endphp
            @endforeach
            <tr>
                <td colspan="8"></td>
            </tr>
        @endforeach
    </table>
</body>

</html>
