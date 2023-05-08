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
            <li><a href="{{ route('purchase-down-payment') }}">Uang Muka Pembelian</a></li>
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
                        <div class="col-md-4">
                            <label>Cabang <span>*</span></label>
                            <div class="form-group">
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
                            <label>Kode Uang Muka Pembelian</label>
                            <div class="form-group">
                                <input type="text" name="kode_uang_muka_pembelian"
                                    value="{{ old('kode_uang_muka_pembelian', $data ? $data->kode_uang_muka_pembelian : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                            <label>Tanggal <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="tanggal"
                                    value="{{ old('tanggal', $data ? $data->tanggal : date('Y-m-d')) }}"
                                    class="form-control datepicker" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal tidak boleh kosong">
                            </div>
                            <label>ID Permintaan Pembelian (PO) <span>*</span></label>
                            <div class="form-group">
                                <select name="id_permintaan_pembelian" class="form-control select2"
                                    data-validation="[NOTEMPTY]"
                                    data-validation-message="ID permintaan pembelian tidak boleh kosong">
                                    <option value="">Pilih Permintaan Pembelian (PO)</option>
                                    @if ($data && $data->id_permintaan_pembelian)
                                        <option value="{{ $data->id_permintaan_pembelian }}" selected>
                                            {{ $data->purchaseOrder->nama_permintaan_pembelian }} (
                                            {{ $data->purchaseOrder->supplier->nama_pemasok }} )
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Mata Uang</label>
                            <div class="form-group">
                                <input type="text" name="nama_mata_uang" class="form-control" readonly
                                    value="{{ old('id_mata_uang', $data ? $data->mataUang->kode_mata_uang . ' - ' . $data->mataUang->nama_mata_uang : '') }}">
                                <input type="hidden" name="id_mata_uang"
                                    value="{{ old('id_mata_uang', $data ? $data->id_mata_uang : '') }}">
                            </div>
                            <label>Rate <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="rate" class="form-control handle-number-2"
                                    value="{{ old('rate', $data ? $data->rate : '') }}" data-validation="[NOTEMPTY]"
                                    data-validation-message="Rate tidak boleh kosong">
                            </div>
                            <label>Nominal <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="nominal" class="form-control handle-number-2"
                                    value="{{ old('nominal', $data ? $data->nominal : '') }}"
                                    data-max="{{ $maxPayment }}" data-validation="[NOTEMPTY]"
                                    data-validation-message="Nominal tidak boleh kosong">
                            </div>
                            <label>Konversi Nominal</label>
                            <div class="form-group">
                                <input type="text" name="konversi_nominal" class="form-control handle-number-2" readonly
                                    value="{{ old('konversi_nominal', $data ? $data->konversi_nominal : '') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Total</label>
                            <div class="form-group">
                                <input type="text" name="total" class="form-control handle-number-2" readonly
                                    value="{{ old('total', $data ? $data->total : '') }}" data-validation="[NOTEMPTY]"
                                    data-validation-message="Total tidak boleh kosong">
                            </div>
                            {{-- <label>Slip</label>
                            <div class="form-group">
                                <select name="id_slip" class="form-control select2">
                                    <option value="">Pilih Slip</option>
                                    @foreach ($slip as $dataSlip)
                                        <option value="{{ $dataSlip->id }}"
                                            {{ old('id_slip', $data ? $data->id_slip : '') == $dataSlip->id ? 'selected' : '' }}>
                                            {{ $dataSlip->text }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <label>Catatan</label>
                            <div class="form-group">
                                <textarea name="catatan" class="form-control" rows="5">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
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
        let branch = {!! json_encode($cabang) !!}
        $('.select2').select2()
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        $('[name="id_cabang"]').select2({
            data: branch
        }).on('select2:select', function(e) {
            let dataselect = e.params.data
            $('[name="nominal"]').val('').attr('data-max', 0)
            $('[name="total"]').val('')
            getPurchaseOrder()
        })

        function getPurchaseOrder() {
            let tag = $('[name="id_permintaan_pembelian"]')
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('purchase-down-payment-auto-po') }}',
                data: {
                    id_cabang: $('[name="id_cabang"]').val()
                },
                success: function(res) {
                    tag.select2({
                        data: res.data
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        getTotalPrice(dataselect.id)
                    })
                    $('#cover-spin').hide()
                },
                error(error) {
                    console.log(error)
                    $('#cover-spin').hide()
                }
            })
        }

        function getTotalPrice(param) {
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('purchase-down-payment-count-po') }}',
                type: 'get',
                data: {
                    po_id: param,
                    id: '{{ $data ? $data->id_uang_muka_pembelian : 0 }}'
                },
                success: function(res) {
                    $('[name="nominal"]').val(formatNumber(res.nominal, 2)).attr(
                        'data-max', res
                        .nominal)
                    $('[name="total"]').val(formatNumber(res.total, 2))
                    $('[name="id_mata_uang"]').val(res.id_mata_uang)
                    $('[name="rate"]').val(formatNumber(res.nilai_mata_uang, 2))
                    $('[name="konversi_nominal"]').val(formatNumber(res.nilai_mata_uang * res.nominal, 2))
                    $('[name="nama_mata_uang"]').val(res.nama_mata_uang)
                    $('#cover-spin').hide()
                },
                error(error) {
                    console.log(error)
                    $('#cover-spin').hide()
                }
            })
        }

        $('body').on('input', '[name="rate"],[name="nominal"]', function() {
            let rate = normalizeNumber($('[name="rate"]').val())
            let nominal = normalizeNumber($('[name="nominal"]').val())
            $('[name="konversi_nominal"]').val(formatNumber(rate * nominal, 2))
        })
    </script>
@endsection
