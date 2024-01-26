@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}" />
    <style>
        th {
            text-align: center;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Laporan QC Penerimaan Barang
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan QC Penerimaan Barang</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-3">
                        <label>Cabang</label>
                        <div class="form-group">
                            <select name="id_cabang" class="form-control select2 trigger-change">
                                @foreach (getCabangForReport() as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Tanggal QC</label>
                        <div class="form-group">
                            <input type="text" name="date" class="form-control trigger-change">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <div class="form-group">
                            <select name="status_qc" class="form-control select2 trigger-change">
                                <option value="all">Semua Status</option>
                                @foreach ($arrayStatus as $key => $val)
                                    <option value="{{ $key }}">{{ $val }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="pull-right">
                    <a href="{{ route('report_qc-print') }}" target="_blank"
                        class="btn btn-danger btn-sm btn-flat btn-action">
                        <i class="glyphicon glyphicon-print"></i> Print
                    </a>
                    <a href="{{ route('report_qc-excel') }}" class="btn btn-success btn-sm btn-flat btn-action">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <a href="javascript:void(0)" class="btn btn-default btn-sm btn-flat btn-view-action">
                        <i class="glyphicon glyphicon-eye-open"></i> View
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive" id="target-table" style="display:none;">
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Tanggal Pembelian</th>
                                <th>Kode Pembelian</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah</th>
                                <th>SG</th>
                                <th>BE</th>
                                <th>PH</th>
                                <th>Warna</th>
                                <th>Bentuk</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Tanggal QC</th>
                                <th>Alasan</th>
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
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/bower_components/moment/moment.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let defaultUrlIndex = '{{ route('report_qc-index') }}'
        let arrayStatus = {
            1: {
                'text': 'Passed',
                'class': 'label label-success'
            },
            2: {
                'text': 'Reject',
                'class': 'label label-danger'
            },
            3: {
                'text': 'Hold',
                'class': 'label label-warning'
            }
        }

        function loadDatatable() {
            $('#target-table').show()
            table = $('.data-table').DataTable({
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: defaultUrlIndex + param,
                columns: [{
                    data: 'tanggal_pembelian',
                    name: 'p.tanggal_pembelian',
                }, {
                    data: 'nama_pembelian',
                    name: 'p.nama_pembelian'
                }, {
                    data: 'nama_barang',
                    name: 'b.nama_barang'
                }, {
                    data: 'nama_satuan_barang',
                    name: 'sb.nama_satuan_barang',
                }, {
                    data: 'total_jumlah_purchase',
                    name: 'pd.jumlah_purchase',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'sg_pembelian_detail',
                    name: 'qc.sg_pembelian_detail',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'be_pembelian_detail',
                    name: 'qc.be_pembelian_detail',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'ph_pembelian_detail',
                    name: 'qc.ph_pembelian_detail',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'warna_pembelian_detail',
                    name: 'qc.warna_pembelian_detail',
                }, {
                    data: 'bentuk_pembelian_detail',
                    name: 'qc.bentuk_pembelian_detail',
                }, {
                    data: 'keterangan_pembelian_detail',
                    name: 'qc.keterangan_pembelian_detail',
                }, {
                    data: 'status_qc',
                    name: 'qc.status_qc',
                    render: function(data) {
                        if (arrayStatus.hasOwnProperty(data)) {
                            return '<label class="' + arrayStatus[data].class + '">' + arrayStatus[data]
                                .text + '</label>'
                        } else {
                            return '<label class="label label-default">Pending</label>'
                        }
                    }
                }, {
                    data: 'tanggal_qc',
                    name: 'qc.tanggal_qc',
                }, {
                    data: 'reason',
                    name: 'qc.reason',
                }, ]
            });
        }
    </script>
    <script src="{{ asset('js/for-report.js') }}"></script>
@endsection
