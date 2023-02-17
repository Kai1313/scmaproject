@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        label>span {
            color: red;
        }

        .select2 {
            width: 100% !important;
        }
    </style>
@endsection

@section('header')
    <p>{{ $data ? 'Edit' : 'Tambah' }} Uang Muka Pembelian</p>
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
    <form action="{{ route('purchase-down-payment-save-entry', $data ? $data->id_uang_muka_pembelian : 0) }}" method="post">
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
                            <label class="col-md-3">Kode Uang Muka Pembelian</label>
                            <div class="col-md-9 form-group">
                                <input type="text" name="kode_uang_muka_pembelian"
                                    value="{{ old('kode_uang_muka_pembelian', $data ? $data->kode_uang_muka_pembelian : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Tanggal <span>*</span></label>
                            <div class="col-md-5 form-group">
                                <input type="date" name="tanggal"
                                    value="{{ old('tanggal', $data ? $data->tanggal : date('Y-m-d')) }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">ID Permintaan Pembelian (PO) <span>*</span></label>
                            <div class="col-md-5 form-group">
                                <select name="id_permintaan_pembelian" class="form-control selectAjax"
                                    data-route="{{ route('purchase-down-payment-auto-po') }}">
                                    {{-- @if ($data && $data->id_permintaan_pembeliaan)
                                        <option value="{{ $data->id_permintaan_pembeliaan }}" selected>
                                        </option>
                                    @endif --}}
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Mata Uang <span>*</span></label>
                            <div class="col-md-5 form-group">
                                <select name="id_mata_uang" class="form-control selectAjax"
                                    data-route="{{ route('purchase-down-payment-auto-currency') }}">
                                    {{-- @if ($data && $data->id_permintaan_pembeliaan)
                                        <option value="{{ $data->id_permintaan_pembeliaan }}" selected>
                                        </option>
                                    @endif --}}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-3">Rate</label>
                            <div class="col-md-4 form-group">
                                <input type="text" name="rate" class="form-control"
                                    value="{{ old('rate', $data ? $data->rate : '') }}">
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Nominal</label>
                            <div class="col-md-4 form-group">
                                <input type="text" name="nominal" class="form-control"
                                    value=""{{ old('nominal', $data ? $data->nominal : '') }}>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Total</label>
                            <div class="col-md-4 form-group">
                                <input type="text" name="total" class="form-control" readonly
                                    value="{{ old('total', $data ? $data->total : '') }}">
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Catatan</label>
                            <div class="col-md-9 form-group">
                                <textarea name="catatan" class="form-control" rows="5">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
                            </div>
                        </div>
                    </div>
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
        $('.select2').select2()

        if ($('[name="id_cabang"]').val() == '') {
            $('[name="id_permintaan_pembeliaan"]').prop('disabled', true)
        }

        $('[name="id_cabang"]').change(function() {
            let self = $('[name="id_permintaan_pembeliaan"]')
            if ($('[name="id_cabang"]').val() == '') {
                self.val('').prop('disabled', true).trigger('change')
            } else {
                self.val('').prop('disabled', false).trigger('change')
            }
        })

        $('.selectAjax').each(function(i, v) {
            let route = $(v).data('route')
            let name = $(v).prop('name')
            $(v).select2({
                ajax: {
                    url: route,
                    dataType: 'json',
                    data: function(params) {
                        if (name == 'id_permintaan_pembelian') {
                            return {
                                search: params.term,
                                id_cabang: $('[name="id_cabang"]').val()
                            }
                        } else {
                            return {
                                search: params.term
                            }
                        }
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    }
                }
            }).on('select2:select', function(e) {
                let dataselect = e.params.data
                console.log(dataselect)
                if (name == 'id_mata_uang') {
                    $('[name="rate"]').val(dataselect.nilai_mata_uang)
                }

                if (name == 'id_permintaan_pembelian') {
                    $.ajax({
                        url: '{{ route('purchase-down-payment-count-po') }}',
                        type: 'get',
                        data: {
                            po_id: dataselect.id,
                            id: '{{ $data ? $data->id_uang_muka_pembelian : 0 }}'
                        },
                        success: function(res) {
                            $('[name="nominal"]').val(res.nominal)
                            $('[name="total"]').val(res.total)
                        },
                        error(error) {
                            console.log(error)
                        }
                    })
                }
            });
        })
    </script>
@endsection
