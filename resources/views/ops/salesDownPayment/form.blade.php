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
            Uang Muka Penjualan
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('sales-down-payment') }}">Uang Muka Penjualan</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Uang Muka Penjualan</h3>
                <a href="{{ route('sales-down-payment') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <form action="{{ route('sales-down-payment-save-entry', $data ? $data->id_uang_muka_penjualan : 0) }}"
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
                            <label>Kode Uang Muka Penjualan</label>
                            <div class="form-group">
                                <input type="text" name="kode_uang_muka_penjualan"
                                    value="{{ old('kode_uang_muka_penjualan', $data ? $data->kode_uang_muka_penjualan : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                            <label>Tanggal <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="tanggal"
                                    value="{{ old('tanggal', $data ? $data->tanggal : date('Y-m-d')) }}"
                                    class="form-control datepicker" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal tidak boleh kosong">
                            </div>
                            <label>ID Permintaan Penjualan (SO) <span>*</span></label>
                            <div class="form-group">
                                <select name="id_permintaan_penjualan" class="form-control selectAjax"
                                    data-validation="[NOTEMPTY]"
                                    data-validation-message="ID permintaan penjualan tidak boleh kosong">
                                    <option value="">Pilih Permintaan Penjualan (SO)</option>
                                    @if ($data && $data->id_permintaan_penjualan)
                                        <option value="{{ $data->id_permintaan_penjualan }}" selected>
                                            {{ $data->salesOrder->nama_permintaan_penjualan }} (
                                            {{ $data->salesOrder->customer->nama_pelanggan }} )
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
                            <label>Jenis PPN</label>
                            <div class="form-group">
                                <select name="ppn_uang_muka_penjualan" class="form-control select2">
                                    <option value="0"
                                        {{ $data && $data->ppn_uang_muka_penjualan == '0' ? 'selected' : '' }}>Tanpa PPN
                                    </option>
                                    <option value="1"
                                        {{ $data && $data->ppn_uang_muka_penjualan == '1' ? 'selected' : '' }}>Include
                                    </option>
                                    <option value="2"
                                        {{ $data && $data->ppn_uang_muka_penjualan == '2' ? 'selected' : '' }}>Exclude
                                    </option>
                                </select>
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
                                <input type="hidden" name="konversi_nominal"
                                    value="{{ old('konversi_nominal', $data ? $data->konversi_nominal : '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>DPP</label>
                            <div class="form-group">
                                <input type="text" name="dpp" class="form-control handle-number-2" readonly
                                    value="{{ old('dpp', $data ? $data->dpp : '') }}" readonly>
                            </div>
                            <label>PPN</label>
                            <div class="form-group">
                                <input type="text" name="ppn" class="form-control handle-number-2" readonly
                                    value="{{ old('ppn', $data ? $data->ppn : '') }}" readonly>
                            </div>
                            <label>Total <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="total" class="form-control handle-number-2" readonly
                                    value="{{ old('total', $data ? $data->total : '') }}" data-validation="[NOTEMPTY]"
                                    data-validation-message="Total tidak boleh kosong">
                            </div>
                            {{-- <label>Slip <span>*</span></label>
                            <div class="form-group">
                                <select name="id_slip" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Slip tidak boleh kosong">
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
                                <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
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
            getSalesOrder()
        })

        function getSalesOrder() {
            let tag = $('[name="id_permintaan_penjualan"]')
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('sales-down-payment-auto-so') }}',
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
                url: '{{ route('sales-down-payment-count-so') }}',
                type: 'get',
                data: {
                    so_id: param,
                    id: '{{ $data ? $data->id_uang_muka_penjualan : 0 }}'
                },
                success: function(res) {
                    $('[name="nominal"]').val(formatNumber(res.nominal, 2)).attr(
                        'data-max', res
                        .nominal)
                    $('[name="total"]').val(formatNumber(res.total, 2))
                    $('[name="id_mata_uang"]').val(res.id_mata_uang)
                    $('[name="rate"]').val(formatNumber(res.nilai_mata_uang, 2))
                    // $('[name="konversi_nominal"]').val(formatNumber(res.nilai_mata_uang * res.nominal, 2))
                    $('[name="nama_mata_uang"]').val(res.nama_mata_uang)
                    calculate()
                    $('#cover-spin').hide()
                },
                error(error) {
                    console.log(error)
                    $('#cover-spin').hide()
                }
            })
        }

        $('body').on('input', '[name="rate"],[name="nominal"]', function() {
            setTimeout(() => {
                calculate()
            }, 100);

        })

        $('[name="ppn_uang_muka_penjualan"]')
            .select2()
            .on('select2:select', function(e) {
                let dataselect = e.params.data
                calculate()
            })

        calculate()

        function calculate() {
            let nominal = normalizeNumber($('[name="nominal"]').val())
            let rate = normalizeNumber($('[name="rate"]').val())
            let type = $('[name="ppn_uang_muka_penjualan"]').val()
            let dpp = 0
            let ppn = 0
            switch (type) {
                case '0':
                    dpp = nominal * rate
                    ppn = 0
                    break;
                case '1':
                    dpp = (nominal * rate) / 1.11
                    ppn = (nominal * rate) - dpp
                    break;
                case '2':
                    dpp = nominal * rate
                    ppn = (nominal * rate) * 0.11
                    break;
                default:
                    break;
            }

            $('[name="konversi_nominal"]').val(formatNumber((nominal * rate).toFixed(2), 2))
            $('[name="dpp"]').val(formatNumber(dpp.toFixed(2), 2))
            $('[name="ppn"]').val(formatNumber(ppn.toFixed(2), 2))
        }
    </script>
@endsection
