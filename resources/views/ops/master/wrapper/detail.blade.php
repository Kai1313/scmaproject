@extends('layouts.main')

@section('addedStyles')
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Pembungkus
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-wrapper') }}">Master Pembungkus</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Pembungkus <span class="text-muted"></span></h3>
                <a href="{{ route('master-wrapper') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <label class="col-md-3">Cabang</label>
                    <div class="col-md-9">
                        : {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3">Nama Pembungkus</label>
                    <div class="col-md-9">
                        : {{ $data->nama_wrapper }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3">Berat</label>
                    <div class="col-md-9">
                        : {{ number_format($data->weight, 4, ',', '.') }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3">Catatan</label>
                    <div class="col-md-9">
                        : {{ $data->catatan }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3">Gambar</label>
                    <div class="col-md-9">
                        @if ($data->path)
                            <img src="{{ env('FTP_GET_FILE') . $data->path2 }}" alt=""
                                style="width:100px;border-radius:15px;">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
