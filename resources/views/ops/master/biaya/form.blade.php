@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        label>span {
            color: red;
        }

        .label-checkbox {
            margin-right: 10px;
        }

        .handle-number-4,
        .handle-number-2 {
            text-align: right;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Biaya
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-slip') }}">Master Biaya</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('master-biaya-save-entry', $data ? $data->id_biaya : 0) }}" method="post"
            class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Biaya</h3>
                    <a href="{{ route('master-biaya') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cabang <span>*</span></label>
                                <select name="id_cabang" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Cabang tidak boleh kosong">
                                    <option value="">Pilih Cabang</option>
                                    @if ($data && $data->id_cabang)
                                        <option value="{{ $data->id_cabang }}" selected>
                                            {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nama Biaya <span>*</span></label>
                                <input type="text" class="form-control" name="nama_biaya"
                                    value="{{ old('nama_biaya', $data ? $data->nama_biaya : '') }}"
                                    data-validation="[NOTEMPTY]" data-validation-message="Nama Biaya tidak boleh kosong">
                            </div>
                            <div class="form-group">
                                <label>Akun Biaya <span>*</span></label>
                                <select name="id_akun_biaya" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Akun Biaya tidak boleh kosong">
                                    <option value="">Pilih Akun Biaya</option>
                                    @foreach ($akunBiaya as $biaya)
                                        <option value="{{ $biaya->id_akun }}"
                                            {{ old('id_akun_biaya', $data ? $data->id_akun_biaya : '') == $biaya->id_akun ? 'selected' : '' }}>
                                            {{ $biaya->kode_akun }} - {{ $biaya->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="label-checkbox">PPn</label>
                                        <input type="checkbox" name="isppn" value="1"
                                            {{ old('isppn', $data ? $data->isppn : '') ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="label-checkbox">PPh</label>
                                        <input type="checkbox" name="ispph" value="1"
                                            {{ old('ispph', $data ? $data->ispph : '') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nilai PPh</label>
                                <input type="text" name="value_pph" class="form-control show-pph handle-number-2"
                                    value="{{ old('value_pph', $data ? $data->value_pph : '') }}">
                            </div>
                            <div class="form-group">
                                <label>Akun PPh</label>
                                <select name="id_akun_pph" class="form-control select2 show-pph">
                                    <option value="">Pilih Akun PPh</option>
                                    @foreach ($akunBiaya as $pph)
                                        <option value="{{ $pph->id_akun }}"
                                            {{ old('id_akun_biaya', $data ? $data->id_akun_pph : '') == $pph->id_akun ? 'selected' : '' }}>
                                            {{ $pph->kode_akun }} - {{ $pph->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="label-checkbox">Aktif <span>*</span></label>
                                <input type="checkbox" name="aktif" value="1"
                                    {{ old('aktif', $data ? $data->aktif : 1) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary pull-right btn-flat" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branch = {!! json_encode($cabang) !!}
        $('[name="id_cabang"]').select2({
            data: branch
        })

        $('[name="ispph"]').change(function() {
            if ($(this).is(':checked')) {
                $('.show-pph').prop('disabled', false)
                $('[name="value_pph"]').attr('data-validation', '[NOTEMPTY]').attr('data-validation-message',
                    'Nilai pph tidak boleh kosong')
                $('[name="id_akun_pph"]').attr('data-validation', '[NOTEMPTY]').attr('data-validation-message',
                    'Akun pph tidak boleh kosong')
            } else {
                $('.show-pph').prop('disabled', true).val('').trigger('change')
                $('[name="value_pph"]').removeAttr('data-validation').removeAttr('data-validation-message')
                $('[name="id_akun_pph"]').removeAttr('data-validation').removeAttr('data-validation-message')
            }
        })

        $('.select2').select2()
        if ($('[name="ispph"]').is(':checked')) {
            $('.show-pph').prop('disabled', false)
            $('[name="value_pph"]').attr('data-validation', '[NOTEMPTY]').attr('data-validation-message',
                'Nilai pph tidak boleh kosong')
            $('[name="id_akun_pph"]').attr('data-validation', '[NOTEMPTY]').attr('data-validation-message',
                'Akun pph tidak boleh kosong')
        } else {
            $('.show-pph').prop('disabled', true)
        }
    </script>
@endsection
