@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('css/fancybox.css') }}" />
    <style>
        th {
            text-align: center;
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
                        <td>{{ $data->tanggal_jawaban_checklist_pekerjaan }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px;font-weight:bold;">Grup</td>
                        <td>:</td>
                        <td>{{ $data->nama_grup_pengguna }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px;font-weight:bold;">Karyawan</td>
                        <td>:</td>
                        <td>{{ $data->nama_pengguna }}</td>
                    </tr>
                    <tr>
                        <td style="width:100px;font-weight:bold;">Checklist</td>
                        <td>:</td>
                        <td>{{ $data->nama_objek_kerja }}</td>
                    </tr>
                </table>
                <table class="table table-bordered" style="margin-top:20px;">
                    <tr>
                        <td colspan="4" style="font-weight:bold;">HASIL CHECKLIST PEKERJAAN</td>
                    </tr>
                    @for ($i = 1; $i <= 25; $i++)
                        @if ($data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'})
                            <tr>
                                <td style="width:30px;">{{ $i }}.</td>
                                <td>{{ $jobs[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}] }}</td>
                                <td style="width:10px;">:</td>
                                <td style="width:30px;">
                                    @if ($data->{'jawaban' . $i . '_jawaban_checklist_pekerjaan'} == '1')
                                        <i class="glyphicon glyphicon-check"></i>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <div class="item-media">
                                        @if (isset($medias[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}]))
                                            @foreach ($medias[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}] as $media)
                                                <a data-src="{{ $media['image'] }}" data-fancybox="gallery">
                                                    <img src="{{ $media['image'] }}">
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endfor
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
    </script>
@endsection
