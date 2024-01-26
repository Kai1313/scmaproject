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

        select[readonly].select2-hidden-accessible+.select2-container {
            pointer-events: none;
            touch-action: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container .select2-selection {
            background: #eee;
            box-shadow: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container .select2-selection__arrow,
        select[readonly].select2-hidden-accessible+.select2-container .select2-selection__clear {
            display: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container {
            pointer-events: none;
            touch-action: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container .select2-selection {
            background: #eee;
            box-shadow: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container .select2-selection__arrow,
        select[readonly].select2-hidden-accessible+.select2-container .select2-selection__clear {
            display: none;
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
                            <label>Jenis PPN</label>
                            <div class="form-group">
                                <select name="ppn_uang_muka_pembelian" class="form-control select2">
                                    <option value=""></option>
                                    <option value="0"
                                        {{ $data && $data->ppn_uang_muka_pembelian == '0' ? 'selected' : '' }}>Tanpa PPN
                                    </option>
                                    <option value="1"
                                        {{ $data && $data->ppn_uang_muka_pembelian == '1' ? 'selected' : '' }}>Include
                                    </option>
                                    <option value="2"
                                        {{ $data && $data->ppn_uang_muka_pembelian == '2' ? 'selected' : '' }}>Exclude
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
                                    value="{{ old('konversi_nominal', $data ? $data->konversi_nominal : '') }}"
                                    class="handle-number-2">
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
                            <label>Total Tagihan</label>
                            <div class="form-group">
                                <input type="text" name="konversi_total" class="form-control" readonly
                                    value="{{ old('konversi_total', $data ? formatNumber($data->total * $data->rate, 2) : 0) }}"
                                    style="text-align:right;">
                                <input type="hidden" name="total" class="form-control"
                                    value="{{ old('total', $data ? formatNumber($data->total, 2) : '') }}">
                            </div>
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
            // $('[name="nominal"]').val('').attr('data-max', 0)
            // $('[name="total"]').val('')
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
                    $('[name="konversi_total"]').val(formatNumber(res.total * res.nilai_mata_uang, 2))
                    $('[name="id_mata_uang"]').val(res.id_mata_uang)
                    $('[name="rate"]').val(formatNumber(res.nilai_mata_uang, 2))
                    $('[name="ppn_uang_muka_pembelian"]').val(res.ppn).change()
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

        $('body').on('change', '[name="rate"],[name="nominal"]', function() {
            setTimeout(() => {
                calculate()
            }, 100);
        })

        $('[name="ppn_uang_muka_pembelian"]')
            .select2()
            .on('select2:select', function(e) {
                let dataselect = e.params.data
                calculate()
            })

        calculate()

        function calculate() {
            let nominal = normalizeNumber($('[name="nominal"]').val())
            let rate = normalizeNumber($('[name="rate"]').val())
            let total = normalizeNumber($('[name="total"]').val())
            let type = $('[name="ppn_uang_muka_pembelian"]').val()
            let dpp = 0
            let ppn = 0
            switch (type) {
                case '0':
                    dpp = nominal * rate
                    ppn = 0
                    break;
                case '1':
                case '2':
                    dpp = (nominal * rate) / 1.11
                    ppn = (nominal * rate) - dpp
                    break;
                default:
                    break;
            }
            $('[name="konversi_nominal"]').val(formatNumber((nominal * rate).toFixed(2), 2))
            $('[name="dpp"]').val(formatNumber(dpp.toFixed(2), 2))
            $('[name="ppn"]').val(formatNumber(ppn.toFixed(2), 2))
            $('[name="konversi_total"]').val(formatNumber(total * rate, 2))
        }
    </script>
@endsection
