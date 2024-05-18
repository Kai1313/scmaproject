@extends('layouts.main')

@section('addedStyles')
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Biaya
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-biaya') }}">Master Biaya</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Biaya <span class="text-muted"></span></h3>
                <a href="{{ route('master-biaya') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <label class="col-md-2">Cabang</label>
                    <div class="col-md-9">
                        : {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Nama Biaya</label>
                    <div class="col-md-9">
                        : {{ $data->nama_biaya }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Akun Biaya</label>
                    <div class="col-md-9">
                        : {{ $data->akunBiaya->kode_akun }} - {{ $data->akunBiaya->nama_akun }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">PPn</label>
                    <div class="col-md-9">
                        : {{ $data->isppn == '1' ? 'Ya' : 'Tidak' }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">PPh</label>
                    <div class="col-md-9">
                        : {{ $data->ispph == '1' ? 'Ya' : 'Tidak' }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Akun PPh</label>
                    <div class="col-md-9">:
                        {{ $data->akunPph ? $data->akunPph->kode_akun . ' - ' . $data->akunPph->nama_akun : '' }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Nilai PPh</label>
                    <div class="col-md-9">
                        : {{ $data->value_pph }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Aktif</label>
                    <div class="col-md-9">
                        : {{ $data->aktif == '1' ? 'Ya' : 'Tidak' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
