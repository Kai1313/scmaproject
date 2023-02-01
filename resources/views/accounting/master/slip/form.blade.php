@extends('layouts.main')

@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Slip
            <small>Slip | Create</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-slip') }}">Master Slip</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route('master-slip') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                        class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Back</a>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Add Slip</h3>
                    </div>
                    <div class="box-body">
                        <form id="submit" class="form-horizontal" data-toggle="validator" enctype="multipart/form-data">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="kode_slip">Kode Slip*</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control input-sm" id="kode_slip" name="kode_slip"
                                        data-minlength="1" data-error="Wajib isi" placeholder="Masukkan Kode Slip" required>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Kode
                                        Slip</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="nama_slip">Nama Slip*</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control input-sm" id="nama_slip" name="nama_slip"
                                        data-minlength="1" maxlength="150" data-error="Wajib isi"
                                        placeholder="Masukkan Nama Slip" required>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Nama
                                        Slip</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="jenis_slip">Jenis Slip*</label>
                                <div class="col-sm-10">
                                    <select name="jenis_slip" class="form-control input-sm select2" id="jenis_slip"
                                        data-error="Wajib isi" required>
                                        <option value="">Pilih Slip</option>
                                        <option value="0">Kas</option>
                                        <option value="1">Bank</option>
                                        <option value="2">Piutang Giro</option>
                                        <option value="3">Hutang Giro</option>
                                    </select>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Slip</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>
                            <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="akun_id">Akun*</label>
                                <div class="col-sm-10">
                                    <select name="akun_id" class="form-control input-sm select2" id="akun_id"
                                        data-error="Wajib isi" required placeholder="aaaaa">
                                        <option value="">Pilih Akun</option>
                                        @foreach ($data_akun as $akun)
                                            <option value="{{ $akun->id_akun }}">{{ $akun->nama_akun }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Akun</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <button type="button" id="tombol_refresh" onclick="refresh()"
                                class="btn btn-default pull-left sr-only"><span class="glyphicon glyphicon-repeat"
                                    aria-hidden="true"></span> Ulangi</button>
                            <button type="button" id="tombol_buat" onclick="tambah_data()"
                                class="btn btn-primary btn-flat pull-right"><span class="glyphicon glyphicon-plus"
                                    aria-hidden="true"></span> Simpan Data</button>
                            <button type="button" id="tombol_ubah" onclick="ubah_data()"
                                class="btn btn-warning pull-right sr-only"><span class="glyphicon glyphicon-pencil"
                                    aria-hidden="true"></span> Ubah Data</button>
                        </form>
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
