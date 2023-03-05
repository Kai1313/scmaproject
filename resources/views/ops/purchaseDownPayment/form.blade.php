@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <style>
        label>span {
            color: red;
        }

        .select2 {
            width: 100% !important;
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
            Uang Muka Pembelian
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Uang Muka Pembelian</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Uang Muka Pembelian</h3>
                <a href="{{ route('purchase-down-payment') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <form action="{{ route('purchase-down-payment-save-entry', $data ? $data->id_uang_muka_pembelian : 0) }}"
                    method="post" class="post-action">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-3">Cabang <span>*</span></label>
                                <div class="col-md-5 form-group">
                                    <select name="id_cabang" class="form-control select2" data-validation="[NOTEMPTY]"
                                        data-validation-message="Cabang tidak boleh kosong">
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
                                    <input type="text" name="tanggal"
                                        value="{{ old('tanggal', $data ? $data->tanggal : date('Y-m-d')) }}"
                                        class="form-control datepicker" data-validation="[NOTEMPTY]"
                                        data-validation-message="Tanggal tidak boleh kosong">
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">ID Permintaan Pembelian (PO) <span>*</span></label>
                                <div class="col-md-9 form-group">
                                    <select name="id_permintaan_pembelian" class="form-control selectAjax"
                                        data-route="{{ route('purchase-down-payment-auto-po') }}"
                                        data-validation="[NOTEMPTY]"
                                        data-validation-message="ID permintaan pembelian tidak boleh kosong">
                                        <option value="">Pilih Permintaan Pembelian (PO)</option>
                                        @if ($data && $data->id_permintaan_pembelian)
                                            <option value="{{ $data->id_permintaan_pembelian }}" selected>
                                                {{ $data->purchaseOrder->nama_permintaan_pembelian }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="id_mata_uang"
                                value="{{ old('id_mata_uang', $data ? $data->id_mata_uang : '') }}">
                            <div class="row">
                                <label class="col-md-3">Slip <span>*</span></label>
                                <div class="col-md-5 form-group">
                                    <select name="id_slip" class="form-control selectAjax"
                                        data-route="{{ route('purchase-down-payment-auto-slip') }}"
                                        data-validation="[NOTEMPTY]" data-validation-message="Slip tidak boleh kosong">
                                        <option value="">Pilih Slip</option>
                                        @if ($data && $data->id_slip)
                                            <option value="{{ $data->id_slip }}" selected>
                                                {{ $data->slip->kode_slip }} - {{ $data->slip->nama_slip }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-3">Rate <span>*</span></label>
                                <div class="col-md-4 form-group">
                                    <input type="text" name="rate" class="form-control handle-number-2"
                                        value="{{ old('rate', $data ? $data->rate : '') }}" data-validation="[NOTEMPTY]"
                                        data-validation-message="Rate tidak boleh kosong">
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Nominal <span>*</span></label>
                                <div class="col-md-4 form-group">
                                    <input type="text" name="nominal" class="form-control handle-number-2"
                                        value="{{ old('nominal', $data ? $data->nominal : '') }}"
                                        data-max="{{ $maxPayment }}" data-validation="[NOTEMPTY]"
                                        data-validation-message="Nominal tidak boleh kosong">
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Total <span>*</span></label>
                                <div class="col-md-4 form-group">
                                    <input type="text" name="total" class="form-control handle-number-2" readonly
                                        value="{{ old('total', $data ? $data->total : '') }}" data-validation="[NOTEMPTY]"
                                        data-validation-message="Total tidak boleh kosong">
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
                    <button class="btn btn-primary btn-flat pull-right" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $('.select2').select2()
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        if ($('[name="id_cabang"]').val() == '') {
            $('[name="id_permintaan_pembelian"]').prop('disabled', true)
        }

        $('[name="id_cabang"]').change(function() {
            let self = $('[name="id_permintaan_pembelian"]')
            if ($('[name="id_cabang"]').val() == '') {
                self.val('').prop('disabled', true).trigger('change')
            } else {
                self.val('').prop('disabled', false).trigger('change')
            }

            $('[name="nominal"]').val('').attr('data-max', 0)
            $('[name="total"]').val('')
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
                            $('[name="nominal"]').val(formatNumber(res.nominal)).attr(
                                'data-max', res
                                .nominal)
                            $('[name="total"]').val(formatNumber(res.total))
                            $('[name="id_mata_uang"]').val(res.id_mata_uang)
                            $('[name="rate"]').val(formatNumber(res.nilai_mata_uang))
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
