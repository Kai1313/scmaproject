@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fancybox.css') }}" />
    <style>
        ul.horizontal-list {
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        ul.horizontal-list li {
            display: inline;
        }

        .mb-1 {
            margin-bottom: .25rem !important;
        }

        th {
            text-align: center;
        }

        .head-checkbox {
            padding-top: 30px;
        }

        .head-checkbox label {
            margin-right: 10px;
        }

        label>span {
            color: red;
        }

        .select2 {
            width: 100% !important;
        }

        .table-detail th {
            background-color: #f39c12;
            color: white;
            text-align: center;
        }

        .handle-number-4 {
            text-align: right;
        }

        tfoot>tr>td {
            font-weight: bold;
        }

        select[readonly].select2-hidden-accessible+.select2-container {
            pointer-events: none;
            touch-action: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container .select2-selection {
            background: #eee;
            box-shadow: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container .select2-selection__arrow,
        select[readonly].select2-hidden-accessible+.select2-container .select2-selection__clear {
            display: none;
        }

        label.has-error {
            background: #fb434a;
            padding: 5px 8px;
            -webkit-border-radius: 3px;
            border-radius: 3px;
            position: absolute;
            right: 0;
            bottom: 37px;
            margin-bottom: 8px;
            max-width: 230px;
            font-size: 80%;
            z-index: 1;
            color: white;
            font-weight: normal;
        }

        label.has-error:after {
            width: 0px;
            height: 0px;
            content: '';
            display: block;
            border-style: solid;
            border-width: 5px 5px 0;
            border-color: #fb434a transparent transparent;
            position: absolute;
            right: 20px;
            bottom: -4px;
            margin-left: -5px;
        }

        .form-group.error {
            color: #fb434a;
        }

        .error input,
        .error textarea {
            border-color: #fb434a;
        }

        .form-group {
            margin-bottom: 5px;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Surat Jalan Umum
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('surat_jalan_umum') }}">Surat Jalan Umum</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Detail Surat Jalan Umum</h3>
                <a href="{{ route('surat_jalan_umum') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right mr-1">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
                <a href="javascript:void(0)" class="btn btn-default btn-flat btn-sm pull-right mr-1 show-media">
                    <i class="fa fa-image mr-1"></i> Dokumentasi
                </a>
                <a href="{{ route('surat_jalan_umum-print-data', $data->id) }}" target="_blank"
                    class="btn btn-default btn-flat btn-sm pull-right mr-1">
                    <i class="fa fa-print mr-1"></i> Cetak
                </a>

            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">: {{ $data->tanggal }}</div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Nomer Surat Jalan</label>
                            <div class="col-md-8">: {{ $data->no_surat_jalan }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <label class="col-md-4">Penerima</label>
                            <div class="col-md-8">: {{ $data->penerima }}</div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Alamat Penerima</label>
                            <div class="col-md-8">: {{ $data->alamat_penerima }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <label class="col-md-4">Nomer Dokumen Lain</label>
                            <div class="col-md-8">: {{ $data->no_dokumen_lain }}</div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Keterangan</label>
                            <div class="col-md-8">: {{ $data->keterangan }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Detail Barang</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="table-detail" class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEntryCamera" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Dokumentasi</h4>
                </div>
                <div class="modal-body">
                    <div class="show-res-camera" style="overflow-x: scroll;overflow-y: hidden;white-space: nowrap;">
                        @if ($data)
                            @foreach ($data->medias as $media)
                                <div style="display:inline-block;margin:5px;">
                                    <div style="margin-bottom:10px;">
                                        <a data-fancybox="lightbox" href="{{ asset($media->lokasi_media) }}">
                                            <img src="{{ asset($media->lokasi_media) }}" alt=""
                                                style="width:100px;height:100px;object-fit:cover;border-radius:5px;"
                                                loading="lazy">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}?t={{ time() }}"></script>
    <script src="{{ asset('js/fancybox.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let details = {!! $data ? $data->details : '[]' !!};

        var table = $('#table-detail').DataTable({
            data: details,
            paging: false,
            ordering: false,
            columns: [{
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'jumlah',
                name: 'jumlah',
                width: 100
            }, {
                data: 'satuan',
                name: 'satuan',
                width: 100
            }, {
                data: 'keterangan',
                name: 'keterangan',
            }]
        });

        $('.show-media').click(function() {
            $('#modalEntryCamera').modal('show')
        })

        Fancybox.bind('[data-fancybox="lightbox"]');
    </script>
@endsection
