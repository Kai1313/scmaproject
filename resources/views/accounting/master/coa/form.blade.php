@extends('layouts.main')
@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
@endsection
@section('header')
<section class="content-header">
    <h1>
        Master CoA
        <small>Chart of Account | Create</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('master-coa') }}">Master CoA</a></li>
        <li class="active">Form</li>
    </ol>
</section>
@endsection

@section('main-section')
<section class="content container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <a href="{{ route('master-coa') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">Back</a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Add Chart of Account</h3>
                </div>
                <div class="box-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Cabang</label>
                                    <select class="form-control select2" style="width: 100%;">
                                        <option selected="selected">Alabama</option>
                                        <option>Alaska</option>
                                        <option>California</option>
                                        <option>Delaware</option>
                                        <option>Tennessee</option>
                                        <option>Texas</option>
                                        <option>Washington</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Kode Akun</label>
                                    <input type="text" class="form-control" id="kodeAkun" placeholder="Masukkan kode akun">
                                </div>
                                <div class="form-group">
                                    <label>Tipe Akun</label>
                                    <select class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Tipe</option>
                                        <option value="0">Neraca</option>
                                        <option value="1">Laba Rugi</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Parent</label>
                                    <select class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Parent</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Is Shown</label>
                                    <select class="form-control select2" style="width: 100%;">
                                        <option value="0">Tidak</option>
                                        <option value="1">Tampil</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Header 1</label>
                                    <select class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Header</option>
                                        <option value="0">Neraca</option>
                                        <option value="1">Laba Rugi</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Header 2</label>
                                    <select class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Header</option>
                                        <option value="0">Neraca</option>
                                        <option value="1">Laba Rugi</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Header 3</label>
                                    <select class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Header</option>
                                        <option value="0">Neraca</option>
                                        <option value="1">Laba Rugi</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('addedScripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $(function () {
            $('.select2').select2()
        })
    </script>
@endsection