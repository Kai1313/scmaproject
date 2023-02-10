@extends('layouts.main')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        label>span {
            color: red;
        }
    </style>
@endpush

@section('header')
    <p>{{ $data ? 'Edit' : 'Tambah' }} Master Biaya</p>
@endsection

@section('main-section')
    @if (session()->has('success'))
        <div class="alert alert-success">
            <ul>
                <li>{!! session()->get('success') !!}</li>
            </ul>
        </div>
    @endif
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('master-biaya-save-entry', $data ? $data->id_biaya : 0) }}" method="post">
        <div class="panel">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-9">
                        <div class="row">
                            <label class="col-md-3">Cabang <span>*</span></label>
                            <div class="col-md-5 form-group">
                                <select name="id_cabang" class="form-control select2">
                                    <option value="">Pilih Cabang</option>
                                    @foreach ($cabang as $branch)
                                        <option value="{{ $branch->id_cabang }}"
                                            {{ old('id_cabang', $data ? $data->id_cabang : '') == $branch->id_cabang ? 'selected' : '' }}>
                                            {{ $branch->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Nama Biaya <span>*</span></label>
                            <div class="col-md-6 form-group">
                                <input type="text" class="form-control" name="nama_biaya"
                                    value="{{ old('nama_biaya', $data ? $data->nama_biaya : '') }}">
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Akun Biaya <span>*</span></label>
                            <div class="col-md-5 form-group">
                                <select name="id_akun_biaya" class="form-control select2">
                                    <option value="">Pilih Akun Biaya</option>
                                    @foreach ($akunBiaya as $biaya)
                                        <option value="{{ $biaya->id_akun }}"
                                            {{ old('id_akun_biaya', $data ? $data->id_akun_biaya : '') == $biaya->id_akun ? 'selected' : '' }}>
                                            {{ $biaya->kode_akun }} - {{ $biaya->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">PPn</label>
                            <div class="col-md-9 form-group">
                                <input type="checkbox" name="isppn" value="1"
                                    {{ old('isppn', $data ? $data->isppn : '') ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">PPh</label>
                            <div class="col-md-9 form-group">
                                <input type="checkbox" name="ispph" value="1"
                                    {{ old('ispph', $data ? $data->ispph : '') ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Nilai PPh</label>
                            <div class="col-md-3 form-group">
                                <input type="number" name="value_pph" class="form-control show-pph"
                                    value="{{ old('value_pph', $data ? $data->value_pph : '') }}" step=".01">
                            </div>
                        </div>
                        <div class="row show-pph">
                            <label class="col-md-3">Akun PPh</label>
                            <div class="col-md-5 form-group">
                                <select name="id_akun_pph" class="form-control select2 show-pph">
                                    <option value="">Pilih Akun PPh</option>
                                    @foreach ($akunBiaya as $pph)
                                        <option value="{{ $pph->id_akun }}"
                                            {{ old('id_akun_biaya', $data ? $data->id_akun_pph : '') == $pph->id_akun ? 'selected' : '' }}>
                                            {{ $pph->kode_akun }} - {{ $pph->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Aktif <span>*</span></label>
                            <div class="col-md-9 form-group">
                                <input type="checkbox" name="aktif" value="1"
                                    {{ old('aktif', $data ? $data->aktif : 1) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button class="btn btn-primary btn-block" type="submit">Simpan</button>
                        <a href="{{ route('master-biaya-page') }}" class="btn btn-default btn-block">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script>
        $('[name="ispph"]').change(function() {
            if ($(this).is(':checked')) {
                $('.show-pph').prop('disabled', false)
            } else {
                $('.show-pph').prop('disabled', true).val('').trigger('change')
            }
        })

        $('.select2').select2()
        if ($('[name="ispph"]').is(':checked')) {
            $('.show-pph').prop('disabled', false)
        } else {
            $('.show-pph').prop('disabled', true)
        }
    </script>
@endpush
