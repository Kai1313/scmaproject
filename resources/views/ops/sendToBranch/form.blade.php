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

        #reader {
            width: 100%;
        }

        @media only screen and (max-width: 412px) {
            #reader {
                width: 100%;
            }
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
            Kirim ke Cabang
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('send_to_branch') }}">Kirim ke Cabang</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('send_to_branch-save-entry', $data ? $data->id_pindah_barang : 0) }}" method="post"
            class="post-action-custom">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Kirim Ke Cabang</h3>
                    <a href="{{ route('send_to_branch') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Cabang Asal <span>*</span></label>
                            <div class="form-group">
                                <select name="id_cabang" class="form-control select2" {{ $data ? 'readonly' : '' }}>
                                    <option value="">Pilih Cabang</option>
                                    @if ($data && $data->id_cabang)
                                        <option value="{{ $data->id_cabang }}" selected>
                                            {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Gudang Asal <span>*</span></label>
                            <div class="form-group">
                                <select name="id_gudang" class="form-control select2" {{ $data ? 'readonly' : '' }}>
                                    <option value="">Pilih Gudang</option>
                                    @if ($data && $data->id_gudang)
                                        <option value="{{ $data->id_gudang }}" selected>
                                            {{ $data->gudang->kode_gudang }} - {{ $data->gudang->nama_gudang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Tanggal <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="tanggal_pindah_barang"
                                    value="{{ old('tanggal_pindah_barang', $data ? $data->tanggal_pindah_barang : date('Y-m-d')) }}"
                                    class="form-control datepicker">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Kode Pindah Cabang</label>
                            <div class="form-group">
                                <input type="text" name="kode_pindah_barang"
                                    value="{{ old('kode_pindah_barang', $data ? $data->kode_pindah_barang : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                            <label>Nama Jasa Pengiriman</label>
                            <div class="form-group">
                                <input type="text" name="transporter"
                                    value="{{ old('transporter', $data ? $data->transporter : '') }}" class="form-control">
                            </div>
                            <label>No Polisi Kendaraan</label>
                            <div class="form-group">
                                <input type="text" name="nomor_polisi"
                                    value="{{ old('nomor_polisi', $data ? $data->nomor_polisi : '') }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Cabang Tujuan <span>*</span></label>
                            <div class="form-group">
                                <select name="id_cabang2" class="form-control select2" {{ $data ? 'readonly' : '' }}>
                                    <option value="">Pilih Cabang Tujuan</option>
                                    @if ($data && $data->id_cabang2)
                                        <option value="{{ $data->id_cabang2 }}" selected>
                                            {{ $data->cabang2->nama_cabang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Keterangan</label>
                            <div class="form-group">
                                <textarea name="keterangan_pindah_barang" class="form-control" rows="3">{{ old('keterangan_pindah_barang', $data ? $data->keterangan_pindah_barang : '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <input name="id_jenis_transaksi" type="hidden"
                        value="{{ old('id_jenis_transaksi', $data ? $data->id_jenis_transaksi : '21') }}">
                    <button class="btn btn-primary btn-flat pull-right btn-sm" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>
        @if ($data)
            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Detil Barang</h4>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-info add-entry btn-flat pull-right btn-sm" type="button">
                                <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="table-detail" class="table table-bordered data-table nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>QR Code</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Batch</th>
                                    <th>Kadaluarsa</th>
                                    <th>SG</th>
                                    <th>BE</th>
                                    <th>PH</th>
                                    <th>Bentuk</th>
                                    <th>Warna</th>
                                    <th>Keterangan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div id="reader" style="margin-bottom:10px;"></div>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" name="search-qrcode" class="form-control"
                                    placeholder="QRCode barang" autocomplete="off">
                                <div class="input-group-btn">
                                    <button class="btn btn-info btn-search btn-flat">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('send_to_branch-save-detail', $data ? $data->id_pindah_barang : 0) }}"
                            class="post-action-modal" method="post">
                            <div class="result-form" style="display:none;">
                                <table class="table table-bordered">
                                    <tr>
                                        <td width="100"><b>QR Code</b></td>
                                        <td>
                                            <input type="text" id="qr_code" name="qr_code"
                                                class="setData form-control" readonly>
                                            <input type="hidden" id="id_pindah_barang_detail"
                                                name="id_pindah_barang_detail" class="setData">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Nama Barang</b></td>
                                        <td>
                                            <input type="text" id="nama_barang" name="nama_barang"
                                                class="setData form-control" readonly>
                                            <input type="hidden" id="id_barang" class="setData" name="id_barang">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Jumlah</b></td>
                                        <td>
                                            <input type="text" id="qty"
                                                class="setData form-control handle-number-4" readonly name="qty">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Satuan</b></td>
                                        <td>
                                            <input type="text" id="nama_satuan_barang" class="setData form-control"
                                                readonly name="nama_satuan_barang">
                                            <input type="hidden" id="id_satuan_barang" class="setData" readonly
                                                name="id_satuan_barang">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Jumlah Zak</b></td>
                                        <td>
                                            <input type="text" id="zak" class="setData form-control" readonly
                                                name="zak">
                                            <input type="hidden" id="id_wrapper_zak" class="setData"
                                                name="id_wrapper_zak">
                                            <input type="hidden" id="weight_zak" class="setData" name="weight_zak">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Batch</b></td>
                                        <td>
                                            <input type="text" id="batch" class="setData form-control" readonly
                                                name="batch">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Kadaluarsa</b></td>
                                        <td>
                                            <input type="text" id="tanggal_kadaluarsa" class="setData form-control"
                                                readonly name="tanggal_kadaluarsa">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Keterangan</b></td>
                                        <td>
                                            <textarea name="keterangan" class="form-control setData" rows="1" id="keterangan"></textarea>
                                        </td>
                                    </tr>
                                </table>
                                <input type="hidden" id="sg" class="setData form-control" readonly
                                    name="sg">
                                <input type="hidden" id="be" class="setData form-control" readonly
                                    name="be">
                                <input type="hidden" id="ph" class="setData form-control" readonly
                                    name="ph">
                                <input type="hidden" id="bentuk" class="setData form-control" readonly
                                    name="bentuk">
                                <input type="hidden" id="warna" class="setData form-control" readonly
                                    name="warna">
                                <input type="hidden" name="status_diterima" value="0">
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn btn-secondary cancel-entry btn-flat">Batal</button>
                                <button type="submit"
                                    class="btn btn-primary save-entry btn-flat result-form">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEntryEdit" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Barang</h4>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('send_to_branch-save-entry-detail') }}" class="post-action-edit-modal"
                            method="post">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2"></textarea>
                            <input type="hidden" name="id_pindah_barang_detail">
                            <div class="text-right" style="margin-top:10px;">
                                <button type="button" class="btn btn-secondary btn-flat"
                                    data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-flat">Simpan</button>
                            </div>
                        </form>
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
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branches = {!! json_encode($cabang) !!};
        let allBranch = {!! $allCabang !!}
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let qrcodeReceived = {!! json_encode($qrcodeReceived) !!}
        let urlSearchQrcode = "{{ route('send_to_branch-qrcode') }}"
        let sendId = {{ $data ? $data->id_pindah_barang : 0 }}
        let urlDeleteDetail = '{{ route('send_to_branch-delete-detail', [$data ? $data->id_pindah_barang : 0, 0]) }}'
    </script>
    <script src="{{ asset('js/send-to-branch.js') }}"></script>
@endsection
