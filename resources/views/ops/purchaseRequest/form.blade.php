@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        label>span {
            color: red;
        }
    </style>
@endsection

@section('header')
    <p>{{ $data ? 'Edit' : 'Tambah' }} Permintaan Pembelian</p>
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
    <form action="{{ route('purchase-request-save-entry', $data ? $data->id_biaya : 0) }}" method="post">
        <div class="panel">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
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
                            <label class="col-md-3">Tanggal</label>
                            <div class="col-md-3 form-group">
                                <input type="date" name="purchase_request_date"
                                    value="{{ old('purchase_request_date', date('Y-m-d')) }}" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Estimasi</label>
                            <div class="col-md-5 form-group">
                                <input type="date" name="purchase_request_estimation_date"
                                    value="{{ old('purchase_request_estimation_date') }}" class=" form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-3">Gudang</label>
                            <div class="col-md-5 form-group">
                                <select name="id_gudang" class="form-control">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Pemohon</label>
                            <div class="col-md-5 form-group">
                                <select name="purchase_request_user_id" class="form-control">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Catatan</label>
                            <div class="col-md-5 form-group">
                                <textarea name="catatan" class="form-control" rows="5">{{ old('catatan') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Satuan</th>
                            <th>Jumlah</th>
                            <th>Action</th>
                        </tr>
                        @if ($data)
                            @foreach ($data->details as $detail)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                </div>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <a href="{{ route('purchase-request') }}" class="btn btn-default">Kembali</a>
            </div>
        </div>
    </form>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
@endsection

@section('externalScripts')
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
@endsection