<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    @if ($req->report_type == 'rekap')
        <table>
            <tr>
                <td style="font-weight:bold;">LAPORAN REKAP KUNJUNGAN TANGGAL
                    {{ date('d/m/Y', strtotime($req->start_date)) }} SAMPAI
                    {{ date('d/m/Y', strtotime($req->end_date)) }}</td>
            </tr>
        </table>
        <table>
            <thead>
                <tr>
                    <th rowspan="2" width="20px" style="font-weight: bold;text-align:center;vertical-align:center;">No
                    </th>
                    <th rowspan="2" style="font-weight: bold;text-align:center;vertical-align:center;"
                        width="100px">
                        Sales Name</th>
                    <th rowspan="2" style="font-weight: bold;text-align:center;vertical-align:center;"
                        width="100px">Date</th>
                    <th rowspan="2" style="font-weight: bold;text-align:center;vertical-align:center;"
                        width="200px">Customer</th>
                    <th rowspan="2" style="font-weight: bold;text-align:center;vertical-align:center;"
                        width="200px">Category</th>
                    <th colspan="{{ count($activities) }}" style="font-weight: bold;text-align:center;">Activity</th>
                    <th rowspan="2" style="font-weight: bold;text-align:center;vertical-align:center;"
                        width="300px">Description</th>
                </tr>
                <tr>
                    @foreach ($activities as $ac)
                        <th style="font-weight: bold;text-align:center;word-wrap: break-word;" width="90px">
                            {{ $ac }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($result['datas'] as $key => $data)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $data->nama_salesman }}</td>
                        <td>{{ $data->visit_date }}</td>
                        <td>{{ $data->nama_pelanggan }}</td>
                        <td>{{ $data->status_pelanggan }}</td>
                        @php
                            $progress = explode(', ', $data->progress_ind);
                        @endphp
                        @foreach ($activities as $activity)
                            <td class="text-center">
                                @if (in_array($activity, $progress))
                                    1
                                @endif
                            </td>
                        @endforeach
                        <td>
                            @if ($data->visit_title)
                                Hasil kunjungan : {!! $data->visit_title !!} <br><br>
                            @endif
                            @if ($data->visit_desc)
                                Masalah : {!! $data->visit_desc !!} <br><br>
                            @endif
                            @if ($data->solusi)
                                Solusi : {!! $data->solusi !!} <br><br>
                            @endif
                            @if ($data->alasan_pembatalan)
                                Alasan batal : {{ $data->alasan_pembatalan }} <br><br>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table>
            @foreach ($result['recap'] as $kre => $re)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-weight:bold;">{{ $kre }}</td>
                    <td>{{ $re }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <table>
            <tr>
                <td style="font-weight:bold;">LAPORAN KUNJUNGAN TANGGAL
                    {{ date('d/m/Y', strtotime($req->start_date)) }}
                    SAMPAI
                    {{ date('d/m/Y', strtotime($req->end_date)) }}</td>
            </tr>
        </table>
        <table>
            <thead>
                <tr>
                    <th width="20px" style="font-weight: bold;text-align:center;">No</th>
                    <th width="100px" style="font-weight: bold;text-align:center;">Tanggal</th>
                    <th width="100px" style="font-weight: bold;text-align:center;">Sales</th>
                    <th width="200px" style="font-weight: bold;text-align:center;">Pelanggan</th>
                    <th width="300px" style="font-weight: bold;text-align:center;">Hasil Kunjungan</th>
                    <th width="300px" style="font-weight: bold;text-align:center;">Masalah</th>
                    <th width="300px" style="font-weight: bold;text-align:center;">Solusi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result as $key => $res)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $res->visit_date }}</td>
                        <td>{{ $res->nama_salesman }}</td>
                        <td>{{ $res->nama_pelanggan }}</td>
                        <td>{!! $res->visit_title !!}</td>
                        <td>{!! $res->visit_desc !!}</td>
                        <td>{!! $res->solusi !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
