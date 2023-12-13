<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th rowspan="2" width="110">Sales Name</th>
                <th rowspan="2" width="110">Date</th>
                <th rowspan="2" width="220">Customer</th>
                <th colspan="{{ count($activities) }}">Activity</th>
                <th rowspan="2">Description</th>
            </tr>
            <tr>
                @foreach ($activities as $activity)
                    <th width="50">{{ $activity }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
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
        </tbody>
    </table>
</div>
