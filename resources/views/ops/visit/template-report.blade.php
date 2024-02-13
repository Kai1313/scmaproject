@if ($type == 'main-data')
    @foreach ($datas as $data)
        <tr>
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
                        <i class="fa fa-check"></i>
                    @endif
                </td>
            @endforeach
            <td>
                @foreach ($data->medias as $media)
                    <div style="display: inline">
                        <a data-src="{{ asset($media->image) }}" data-fancybox="gallery">
                            <img src="{{ asset($media->image) }}" alt=""
                                style="width:100px;height:100px;object-fit:cover;border-radius:10px;">
                        </a>
                    </div>
                @endforeach
                <br>
                {{ $data->alasan_ubah_tanggal }}
                <b>Hasil kunjungan</b> : {{ $data->visit_title }} <br><br>
                <b>Masalah</b> : {{ $data->visit_desc }} <br><br>
                <b>Solusi</b> : {{ $data->solusi }}
            </td>
        </tr>
    @endforeach
    @if (count($datas) == 0)
        <tr>
            <td colspan="11">Data tidak ditemukan</td>
        </tr>
    @endif
@endif

@if ($type == 'recap-data')
    @foreach ($recap as $kre => $re)
        <tr>
            <td width="200px">{{ $kre }}</td>
            <td width="20px"> : </td>
            <td width="20px">{{ $re }}</td>
        </tr>
    @endforeach
    @if (count($recap) == 0)
        <tr>
            <td colspan="2">Data tidak ditemukan</td>
        </tr>
    @endif
@endif
