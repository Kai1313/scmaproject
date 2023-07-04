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
            Laporan Piutang Saat Ini
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Piutang Saat Ini</li>
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
                    {{-- <div class="col-md-3">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="text" name="date" class="form-control trigger-change">
                        </div>
                    </div> --}}
                </div>
                <div class="pull-right">
                    <a href="{{ route('report_receiveable-print') }}" target="_blank"
                        class="btn btn-danger btn-sm btn-flat btn-action">
                        <i class="glyphicon glyphicon-print"></i> Print
                    </a>
                    <a href="{{ route('report_receiveable-excel') }}" class="btn btn-success btn-sm btn-flat btn-action">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <a href="javascript:void(0)" class="btn btn-default btn-sm btn-flat btn-view-action">
                        <i class="glyphicon glyphicon-eye-open"></i> View
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive" id="target-table" style="display:none;">
                    <table class="table table-bordered data-table display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Pelanggan</th>
                                <th>Nama Pelanggan</th>
                                <th>No. Faktur</th>
                                <th>Tgl Faktur</th>
                                <th>Jatuh Tempo</th>
                                <th>Nilai Faktur</th>
                                <th>Piutang Asing</th>
                                <th>Piutang Pajak</th>
                                <th>Umur</th>
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
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/bower_components/moment/moment.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let defaultUrlIndex = '{{ route('report_receiveable-index') }}'

        function loadDatatable() {
            $('#target-table').show()
            table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: defaultUrlIndex + param,
                columns: [{
                    data: 'kode_pelanggan',
                    name: 'kode_pelanggan'
                }, {
                    data: 'nama_pelanggan',
                    name: 'nama_pelanggan'
                }, {
                    data: 'id_transaksi',
                    name: 'id_transaksi',
                }, {
                    data: 'tanggal_penjualan',
                    name: 'tanggal_penjualan',
                }, {
                    data: 'top',
                    name: 'top',
                }, {
                    data: 'mtotal_penjualan',
                    name: 'mtotal_penjualan',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'sisa',
                    name: 'sisa',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'sisa_tax',
                    name: 'sisa_tax',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'aging',
                    name: 'aging',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, ]
            });
        }
    </script>
    <script src="{{ asset('js/for-report.js') }}"></script>
@endsection
