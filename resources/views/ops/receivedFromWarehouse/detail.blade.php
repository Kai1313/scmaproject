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
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Terima Dari Gudang
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Terima Dari Gudang</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Detail Terima Dari Gudang</h3>
                <a href="{{ route('received_from_warehouse') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Cabang</label>
                            <div class="col-md-8">
                                : {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Gudang</label>
                            <div class="col-md-8">
                                : {{ $data->gudang->nama_gudang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">
                                : {{ $data->tanggal_pindah_barang }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Kode Pindah Gudang</label>
                            <div class="col-md-8">
                                : {{ $data->kode_pindah_barang }}
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-md-4">Gudang Asal</label>
                            <div class="col-md-8">
                                : {{ $data->gudang2->nama_gudang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Kode Referensi</label>
                            <div class="col-md-8">
                                : {{ $data->parent->kode_pindah_barang }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Detil Barang</h4>
                    </div>
                </div>
                <div class="table-responsive">
                    <input type="hidden" name="details" value="[]">
                    <table id="table-detail" class="table table-bordered data-table display responsive nowrap"
                        width="100%">
                        <thead>
                            <tr>
                                <th>QR Code</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah</th>
                                <th>Batch</th>
                                <th>Kadaluarsa</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
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
        let oldDetails = {!! $data && $data->parent ? $data->parent->formatdetail : '[]' !!};
        let arrayQRCode = {!! $data ? $data->getDetailQRCode->pluck('qr_code') : '[]' !!};
        let details = []
        for (let i = 0; i < oldDetails.length; i++) {
            details.push(oldDetails[i])
            if (arrayQRCode.includes(oldDetails[i]['qr_code'])) {
                details[i]['status_diterima'] = 1
            } else {
                details[i]['id_pindah_barang_detail'] = 0
                details[i]['status_diterima'] = 0
            }
        }

        $('[name="details"]').val(JSON.stringify(details))

        var resDataTable = $('#table-detail').DataTable({
            data: details,
            ordering: false,
            columns: [{
                data: 'qr_code',
                name: 'qr_code'
            }, {
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang'
            }, {
                data: 'qty',
                name: 'qty',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'batch',
                name: 'batch',
                className: 'text-right'
            }, {
                data: 'tanggal_kadaluarsa',
                name: 'tanggal_kadaluarsa',
            }, {
                data: 'id_pindah_barang_detail',
                className: 'text-center',
                name: 'id_pindah_barang_detail',
                searchable: false,
                render: function(data, type, row, meta) {
                    let btn = '';
                    if (row.status_diterima == 1 && arrayQRCode.includes(row.qr_code)) {
                        btn = '<label class="label label-success">Diterima</label>';
                    } else {
                        btn = '<label class="label label-default">Belum diterima</label>';
                    }

                    return btn;
                }
            }, ]
        });
    </script>
@endsection