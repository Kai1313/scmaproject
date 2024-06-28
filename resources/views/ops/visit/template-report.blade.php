@if ($type == 'main-data')
    @foreach ($datas as $data)
        <tr>
            <td class="no-wrap">{{ $data->nama_salesman }}</td>
            <td class="no-wrap">
                {{ $data->visit_date }} <br>
                <b>Tanggal Buat :</b> <br>
                {{ $data->created_at }} <br>
                <b>Tanggal Update :</b> <br>
                {{ $data->updated_at }}
            </td>
            <td style="min-width:150px;max-width:200px;">
                @if (in_array(session('user')->id_grup_pengguna, [15, 1, 13]) || session('user')->id_pengguna == $data->user_created)
                    <a href="javascript:void(0)" class="show-customer" data-id="{{ $data->id_pelanggan }}">
                        {{ $data->nama_pelanggan }}
                    </a>
                @else
                    {{ $data->nama_pelanggan }}
                @endif
            </td>
            <td class="no-wrap">{{ $data->status_pelanggan }}</td>
            @php
                $progress = explode(', ', $data->progress_ind);
            @endphp
            @foreach ($activities as $activity)
                <td class="text-center no-wrap">
                    @if (in_array($activity, $progress))
                        <i class="fa fa-check"></i>
                    @endif
                </td>
            @endforeach
            <td style="min-width:400px;max-width:800px;">
                @foreach ($data->medias as $media)
                    <div style="display: inline">
                        <a data-src="{{ asset($media->image) }}" data-fancybox="gallery">
                            <img src="{{ asset($media->image) }}" alt=""
                                style="width:100px;height:100px;object-fit:cover;border-radius:10px;">
                        </a>
                    </div>
                @endforeach
                @if ($data->alasan_ubah_tanggal != '')
                    <br><br>
                    <b><u>Hasil kunjungan</u></b> : {{ $data->alasan_ubah_tanggal }}
                @endif
                @if (strip_tags($data->visit_title))
                    <br><br>
                    <b><u>Hasil kunjungan</u></b> : {!! nl2br($data->visit_title) !!}
                @endif
                @if (strip_tags($data->visit_desc))
                    <br><br>
                    <b><u>Masalah</u></b> : {!! nl2br($data->visit_desc) !!}
                @endif
                @if (strip_tags($data->solusi))
                    <br><br>
                    <b><u>Solusi</u></b> : {!! nl2br($data->solusi) !!}
                @endif
                @if ($data->alasan_pembatalan)
                    <br><br>
                    <b><u>Alasan Batal</u></b> : {{ $data->alasan_pembatalan }}
                @endif
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
