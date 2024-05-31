@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        ul.horizontal-list {
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        ul.horizontal-list li {
            display: inline;
        }

        .mb-1 {
            margin-bottom: .25rem !important;
        }

        th {
            text-align: center;
        }

        .head-checkbox {
            padding-top: 30px;
        }

        .head-checkbox label {
            margin-right: 10px;
        }

        label>span {
            color: red;
        }

        .select2 {
            width: 100% !important;
        }

        .table-detail th {
            background-color: #f39c12;
            color: white;
            text-align: center;
        }

        .handle-number-4 {
            text-align: right;
        }

        tfoot>tr>td {
            font-weight: bold;
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

        label.has-error {
            background: #fb434a;
            padding: 5px 8px;
            -webkit-border-radius: 3px;
            border-radius: 3px;
            position: absolute;
            right: 0;
            bottom: 37px;
            margin-bottom: 8px;
            max-width: 230px;
            font-size: 80%;
            z-index: 1;
            color: white;
            font-weight: normal;
        }

        label.has-error:after {
            width: 0px;
            height: 0px;
            content: '';
            display: block;
            border-style: solid;
            border-width: 5px 5px 0;
            border-color: #fb434a transparent transparent;
            position: absolute;
            right: 20px;
            bottom: -4px;
            margin-left: -5px;
        }

        .form-group.error {
            color: #fb434a;
        }

        .error input,
        .error textarea {
            border-color: #fb434a;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Pemakaian
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('material_usage') }}">Pemakaian</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Pemakaian</h3>
                <a href="{{ route('material_usage') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <form action="{{ route('material_usage-save-entry', $data ? $data->id_pemakaian : 0) }}" method="post"
                    class="post-acton-custom">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cabang Pemakaian <span>*</span></label>
                                <select name="id_cabang" class="form-control select2 lock-form"
                                    {{ $data ? 'readonly' : '' }}>
                                    <option value="">Pilih Cabang</option>
                                    @if ($data && $data->id_cabang)
                                        <option value="{{ $data->id_cabang }}" selected>
                                            {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Gudang Pemakaian <span>*</span></label>
                            <div class="form-group">
                                <select name="id_gudang" class="form-control select2 lock-form"
                                    {{ $data ? 'readonly' : '' }}>
                                    <option value="">Pilih Gudang</option>
                                    @if ($data && $data->id_gudang)
                                        <option value="{{ $data->id_gudang }}" selected>
                                            {{ $data->gudang->kode_gudang }} - {{ $data->gudang->nama_gudang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Kode Pemakaian</label>
                            <div class="form-group">
                                <input type="text" name="kode_pemakaian"
                                    value="{{ old('kode_pemakaian', $data ? $data->kode_pemakaian : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Tanggal <span>*</span></label>
                            <div class="form-group">
                                <input type="date" name="tanggal"
                                    value="{{ old('tanggal', $data ? $data->tanggal : date('Y-m-d')) }}"
                                    class="form-control">
                            </div>
                            <label>Jenis Pemakaian <span>*</span></label>
                            <div class="form-group">
                                <select name="jenis_pemakaian" class="form-control select2 lock-form">
                                    <option value="">Pilih Jenis Pemakaian</option>
                                    @foreach ($types as $dType)
                                        @php
                                            $val = str_replace('HPP Pemakaian ', '', $dType->code);
                                        @endphp
                                        @if (in_array(session('user')->id_grup_pengguna, explode(',', $dType->description)))
                                            <option value="{{ $val }}"
                                                {{ $data && $data->jenis_pemakaian == $val ? 'selected' : '' }}>
                                                {{ $val }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Catatan</label>
                            <div class="form-group">
                                <textarea name="catatan" class="form-control" rows="2.5">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
                            </div>
                            @if ($accessQc == '1')
                                <label>QC</label>
                                @if (!$data)
                                    <input type="checkbox" name="is_qc" value="1">
                                @else
                                    : <input type="checkbox" name="is_qc" value="1"
                                        {{ $data->is_qc == '1' ? 'checked' : '' }} disabled>
                                @endif
                            @endif
                        </div>
                    </div>
                    <button class="btn btn-primary btn-flat pull-right btn-sm" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved mr-1"></i> Simpan Data
                    </button>
                </form>
            </div>
        </div>
        <div class="box" id="targetDetail" style="{{ $data ? 'display:block' : 'display:none' }}">
            <div class="box-header">
                <h3 class="box-title">Detail Barang</h3>
                <button class="btn btn-info add-entry btn-flat pull-right btn-sm" type="button">
                    <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                </button>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <input type="hidden" name="details" value="{{ $data ? json_encode($data->formatdetail) : '[]' }}">
                    <input type="hidden" name="detele_details" value="[]">
                    <table id="table-detail" class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Gross</th>
                                <th>Jumlah Zak</th>
                                <th>Tare</th>
                                <th>Nett</th>
                                <th>Catatan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                {{-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> --}}

            </div>
        </div>

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display:none;" id="alertModal">
                        </div>
                        <input type="hidden" name="index" value="0">
                        <div id="reader" style="margin-bottom:10px;"></div>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" name="search-qrcode" class="form-control"
                                    placeholder="Scan QRCode" autocomplete="off">
                                <div class="input-group-btn">
                                    <button class="btn btn-info btn-search btn-flat" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="result-form" style="display:none;">
                            <form action="{{ route('material_usage-save-detail', $data ? $data->id_pemakaian : 0) }}"
                                method="post" class="post-action-modal">
                                <label>QR Code <span>*</span></label>
                                <div class="form-group">
                                    <input type="text" name="kode_batang" class="validate form-control" readonly>
                                </div>
                                <label>Nama Barang <span>*</span></label>
                                <div class="form-group">
                                    <input type="text" name="nama_barang" class="validate form-control" readonly>
                                    <input type="hidden" name="id_barang" class="validate">
                                </div>
                                <label>Satuan <span>*</span></label>
                                <div class="form-group">
                                    <input type="text" name="nama_satuan_barang" class="validate form-control"
                                        readonly>
                                    <input type="hidden" name="id_satuan_barang" class="validate">
                                </div>
                                <div class="form-group">
                                    <label>Semua</label>
                                    <input type="checkbox" name="checked_all">
                                </div>
                                <label id="label-jumlah-zak">Jumlah Zak</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" name="jumlah_zak"
                                            class="form-control validate handle-number-4" autocomplete="off">
                                        <span class="input-group-addon" id="max-jumlah-zak"></span>
                                    </div>
                                    <label id="alertZak" style="display:none;color:red;"></label>
                                    <input type="hidden" name="weight_zak" class="validate">
                                    <input type="hidden" name="wrapper_weight" class="validate">
                                </div>
                                <label id="label-timbangan">Timbangan</label>
                                <div class="form-group">
                                    <select name="id_timbangan" class="form-control select2">
                                        <option value="">Pilih Timbangan</option>
                                    </select>
                                </div>
                                <label id="label-berat">Jumlah</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" name="jumlah" class="form-control handle-number-4"
                                            autocomplete="off">
                                        <span class="input-group-addon" id="max-jumlah"></span>
                                        <div class="input-group-btn">
                                            <a href="javascript:void(0)" class="btn btn-warning reload-timbangan">
                                                <i class="glyphicon glyphicon-refresh"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <label id="alertWeight" style="display:none;color:red;"></label>
                                </div>
                                <label>Catatan</label>
                                <div class="form-group">
                                    <textarea name="catatan" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="text-right">
                                    <button type="button" class="btn btn-secondary cancel-entry btn-flat">Batal</button>

                                    <button type="submit" class="btn btn-primary save-entry btn-flat">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}?t={{ time() }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branch = {!! json_encode($cabang) !!}
        let timbangan = {!! $timbangan !!}
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let urlMaterialUsageReloadWeight = '{{ route('material_usage-reload-weight') }}'
        let urlMaterialUsageQrcode = '{{ route('material_usage-qrcode') }}'
        let urlDeleteDetail = '{{ route('material_usage-delete-detail', [$data ? $data->id_pemakaian : 0, 0]) }}'
    </script>
    <script src="{{ asset('js/material-usage.js') }}?t={{ time() }}"></script>
@endsection
