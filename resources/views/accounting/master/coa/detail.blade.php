@extends('layouts.main')

@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Chart of Account
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-coa') }}">Master CoA</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Chart of Account <span class="text-muted"></span></h3>
                        <a href="{{ route('master-coa') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                                class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali</a>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Kode Akun</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">{{ $data_akun->kode_akun }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Nama Akun</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">{{ $data_akun->nama_akun }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Cabang</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">{{ $data_akun->kode_cabang }} - {{ $data_akun->nama_cabang }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Tipe Akun</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">
                                            @switch($data_akun->jenis_slip)
                                                @case(0)
                                                    Neraca
                                                @break

                                                @case(1)
                                                    Laba Rugi
                                                @break

                                                @default
                                                    -
                                            @endswitch
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Is Shown ?</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">
                                            @switch($data_akun->isshown)
                                                @case(0)
                                                    Tidak Tampil
                                                @break

                                                @case(1)
                                                    Tampil
                                                @break

                                                @default
                                                    -
                                            @endswitch
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Parent</label>
                                    </div>
                                    <div class="col-md-9">
                                        @if (isset($data_akun->kode_parent))
                                            <p class="nomarg">{{ $data_akun->kode_parent }} - {{ $data_akun->nama_parent }}
                                            </p>
                                        @else
                                            <p class="nomarg">-</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Header 1</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">{{ $data_akun->header1 }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Header 2</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">{{ $data_akun->header2 }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Header 3</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">{{ $data_akun->header3 }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Notes</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">{{ $data_akun->catatan }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $(function() {
            $('.select2').select2()
        })
    </script>
@endsection
