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

        .rounded-0 {
            border-radius: 0;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            QC Penerimaan Pembelian
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">QC Penerimaan Pembelian</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-4">
                        <label>Cabang</label>
                        <div class="form-group">
                            <select name="id_cabang" class="form-control select2">
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Awal</label>
                        <div class="form-group">
                            <input type="text" name="start_date" class="form-control datepicker"
                                value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Akhir</label>
                        <div class="form-group">
                            <input type="text" name="end_date" class="form-control datepicker"
                                value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('qc_receipt-entry') }}" class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah QC Penerimaan Pembelian
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Pembelian</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Status</th>
                                <th>Alasan</th>
                                <th>Tanggal QC</th>
                                <th>SG</th>
                                <th>BE</th>
                                <th>PH</th>
                                <th>Warna</th>
                                <th>Bentuk</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $('.select2').select2()
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('qc_receipt') }}?c=" + $('[name="id_cabang"]').val() + '&start_date=' + $(
                '[name="start_date"]').val() + '&end_date=' + $('[name="end_date"]').val(),
            columns: [{
                data: 'nama_pembelian',
                name: 'pembelian.nama_pembelian'
            }, {
                data: 'nama_barang',
                name: 'barang.nama_barang',
            }, {
                data: 'jumlah_pembelian_detail',
                name: 'jumlah_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'nama_satuan_barang',
                name: 'satuan_barang.nama_satuan_barang',
            }, {
                data: 'status_qc',
                name: 'qc.status_qc',
                className: 'text-center'
            }, {
                data: 'reason',
                name: 'qc.reason',
            }, {
                data: 'tanggal_qc',
                name: 'qc.tanggal_qc'
            }, {
                data: 'sg_pembelian_detail',
                name: 'pembelian_detail.sg_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'be_pembelian_detail',
                name: 'pembelian_detail.be_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'ph_pembelian_detail',
                name: 'pembelian_detail.ph_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'warna_pembelian_detail',
                name: 'pembelian_detail.warna_pembelian_detail',
            }, {
                data: 'bentuk_pembelian_detail',
                name: 'pembelian_detail.bentuk_pembelian_detail',
            }, {
                data: 'keterangan_pembelian_detail',
                name: 'pembelian_detail.keterangan_pembelian_detail',
            }]
        });

        $('[name="id_cabang"],[name="start_date"],[name="end_date"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&start_date=' + $('[name="start_date"]').val() +
                '&end_date=' + $('[name="end_date"]').val()).load()
        })
    </script>
@endsection
