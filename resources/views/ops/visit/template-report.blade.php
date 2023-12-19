@if ($type == 'main-data')
    @foreach ($datas as $data)
        <tr>
            <td>{{ $data->nama_salesman }}</td>
            <td>{{ $data->visit_date }}</td>
            <td>{{ $data->nama_pelanggan }}</td>
            @php
                $progress = explode(', ', $data->progress_ind);
            @endphp
            @foreach ($activities as $activity)
                <td class="text-center">
                    @if (in_array($activity, $progress))
                        <i class="fa fa-check"></i>
                    @endif
                </td>
            @endforeach
            <td>
                <b>Hasil kunjungan</b> : {{ $data->visit_title }} <br><br>
                <b>Masalah</b> : {{ $data->visit_desc }} <br><br>
                <b>Solusi</b> : {{ $data->solusi }}
            </td>
        </tr>
    @endforeach
@endif

@if ($type == 'recap-data')
    @foreach ($recap as $kre => $re)
        <tr>
            <td width="200px">{{ $kre }}</td>
            <td width="20px"> : </td>
            <td>{{ $re }}</td>
        </tr>
    @endforeach
@endif
