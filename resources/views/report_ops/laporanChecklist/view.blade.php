@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('css/fancybox.css') }}" />
    <style>
        th {
            text-align: center;
            vertical-align: middle !important;
        }

        .item-media>a>img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 5px;
        }

        .item-media {
            overflow: auto;
            white-space: nowrap;
        }

        :root {
            --form-control-color: rebeccapurple;
            --form-control-disabled: #959495;
        }

        *,
        *:before,
        *:after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
        }

        form {
            display: grid;
            place-content: center;
            min-height: 100vh;
        }

        .form-control2 {
            font-family: system-ui, sans-serif;
            font-size: 2rem;
            font-weight: bold;
            line-height: 1.1;
            /* display: grid; */
            grid-template-columns: 1em auto;
            gap: 0.5em;
        }

        .form-control2+.form-control2 {
            margin-top: 1em;
        }

        .form-control2--disabled {
            color: var(--form-control-disabled);
            cursor: not-allowed;
        }

        input[type="checkbox"] {
            /* Add if not using autoprefixer */
            -webkit-appearance: none;
            /* Remove most all native input styles */
            appearance: none;
            /* For iOS < 15 */
            background-color: var(--form-background);
            /* Not removed via appearance */
            margin: 0;

            font: inherit;
            color: currentColor;
            width: 1.15em;
            height: 1.15em;
            border: 0.15em solid currentColor;
            border-radius: 0.15em;
            transform: translateY(-0.075em);

            display: grid;
            place-content: center;
        }

        input[type="checkbox"]::before {
            content: "";
            width: 0.65em;
            height: 0.65em;
            clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
            transform: scale(0);
            transform-origin: bottom left;
            transition: 120ms transform ease-in-out;
            box-shadow: inset 1em 1em var(--form-control-color);
            /* Windows High Contrast Mode */
            background-color: CanvasText;
        }

        input[type="checkbox"]:checked::before {
            transform: scale(1);
        }

        input[type="checkbox"]:focus {
            outline: max(2px, 0.15em) solid currentColor;
            outline-offset: max(2px, 0.15em);
        }

        input[type="checkbox"]:disabled {
            --form-control-color: var(--form-control-disabled);

            color: var(--form-control-disabled);
            cursor: not-allowed;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Laporan Checklist Pekerjaan
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('report_checklist') }}">Laporan Checklist Pekerjaan</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-body" style="padding:0px;">
                <table class="table table-bordered">
                    <tr>
                        <td style="width:100px;font-weight:bold;">Tanggal</td>
                        <td style="width:10px;">:</td>
                        <td>{{ $status == '1' ? $data->tanggal_jawaban_checklist_pekerjaan : date('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px;font-weight:bold;">Grup</td>
                        <td>:</td>
                        <td>{{ $data->nama_grup_pengguna }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px;font-weight:bold;">Karyawan</td>
                        <td>:</td>
                        <td>{{ $status == '1' ? $data->nama_pengguna : '' }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px;font-weight:bold;">Checklist</td>
                        <td>:</td>
                        <td>{{ $status == '1' ? $data->nama_objek_kerja : $obj->nama_objek_kerja }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px;font-weight:bold;">Pemeriksa</td>
                        <td>:</td>
                        <td>{{ $status == '1' ? $data->nama_pengguna_checker : '' }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px;font-weight:bold;">Tanggapan Pemeriksa</td>
                        <td style="vertical-align: middle;">:</td>
                        <td style="vertical-align: middle;">
                            @if ($status == '1')
                                <div class="input-group">
                                    <textarea name="keterangan_checker_jawaban_checklist_pekerjaan" class="form-control" rows="3"
                                        placeholder="Masukkan Tanggapan Anda">{!! $data->keterangan_checker_jawaban_checklist_pekerjaan !!}</textarea>
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary btn-note-save" style="height:74px;">Simpan</button>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
                <label for="" style="margin:10px;border-bottom:1px solid gray;font-size:18px;">HASIL CHECKLIST
                    PEKERJAAN</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pekerjaan</th>
                            <th>Checklist Pengguna</th>
                            <th>Checklist Pemeriksa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($status == '1')
                            @for ($i = 1; $i <= 25; $i++)
                                @if ($data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'})
                                    <tr>
                                        <td style="width:30px;">{{ $i }}.</td>
                                        <td>{{ $jobs[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}] }}</td>
                                        <td style="width:50px;text-align:center;">
                                            @if ($data->{'jawaban' . $i . '_jawaban_checklist_pekerjaan'} == '1')
                                                <i class="glyphicon glyphicon-check" style="font-size:20px;"></i>
                                            @endif
                                        </td>
                                        <td style="width:50px;text-align:center;">
                                            @if ($data->{'jawaban' . $i . '_jawaban_checklist_pekerjaan'} == '1')
                                                <label class="form-control2">
                                                    <input type="checkbox" name="checklist_checker"
                                                        data-sequence="{{ $i }}"
                                                        {{ $data->{'checker' . $i . '_jawaban_checklist_pekerjaan'} == '1' ? 'checked' : '' }} />
                                                </label>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5">
                                            @if ($data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'})
                                                <b>Keterangan</b> : {!! $data->{'keterangan' . $i . '_jawaban_checklist_pekerjaan'} !!}
                                            @endif
                                            <div class="item-media">
                                                @if (isset($medias[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}]))
                                                    @foreach ($medias[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}] as $media)
                                                        <a data-src="{{ $media['image'] }}" data-fancybox="gallery"
                                                            data-caption="Diinput oleh {{ $media['user_name'] }}">
                                                            <img src="{{ $media['image'] }}">
                                                        </a>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endfor
                        @else
                            @foreach ($datas as $kd => $d)
                                <tr>
                                    <td style="width:30px;">{{ $kd + 1 }}.</td>
                                    <td>{{ $d->nama_pekerjaan }}</td>
                                    <td style="width:50px;text-align:center;"></td>
                                    <td style="width:50px;text-align:center;"></td>
                                </tr>
                            @endforeach
                            @if (count($datas) == 0)
                                <tr>
                                    <td colspan="4">Tidak ada checklist pekerjaan</td>
                                </tr>
                            @endif
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('js/fancybox.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        Fancybox.bind('[data-fancybox="gallery"]');

        $('.item-media').width($('.box-body').width() - 18)

        $(window).on("resize", function() {
            $('.item-media').width($('.box-body').width() - 18)
        });
        @if ($status == '1')
            $('[name="checklist_checker"]').change(function() {
                let seq = $(this).data('sequence')
                let val = $(this).is(':checked') ? '1' : 0
                $.ajax({
                    url: '{{ route('checklist-checker') }}',
                    type: 'post',
                    data: {
                        seq: seq,
                        id: '{{ $data->id_jawaban_checklist_pekerjaan }}',
                        val: val
                    },
                    success: function(res) {
                        console.log(res)
                    },
                    error: function(data) {
                        Swal.fire("Bermasalah. ", data.responseJSON.message, 'error')
                    }
                })
            })

            $('.btn-note-save').click(function() {
                let val = $('[name="keterangan_checker_jawaban_checklist_pekerjaan"]').val()
                $.ajax({
                    url: '{{ route('checklist-comment-checker') }}',
                    type: 'post',
                    data: {
                        id: '{{ $data->id_jawaban_checklist_pekerjaan }}',
                        note: val
                    },
                    success: function(res) {
                        console.log(res)
                    },
                    error: function(data) {
                        Swal.fire("Bermasalah. ", data.responseJSON.message, 'error')
                    }
                })
            })
        @endif
    </script>
@endsection
