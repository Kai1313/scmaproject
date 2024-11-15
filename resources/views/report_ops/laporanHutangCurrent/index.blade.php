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

        table.dataTable tr.dtrg-group th {
            background-color: #e0e0e0;
            text-align: left;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Laporan Hutang
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Hutang </li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-2">
                        <label>Cabang</label>
                        <div class="form-group">
                            <select name="id_cabang" class="form-control select2 trigger-change">
                                @foreach (getCabangForReport() as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="date" name="dateReport" class="form-control trigger-change"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Pemasok</label>
                        <div class="form-group">
                            <select name="id_pemasok" class="form-control select2 trigger-change">
                                @foreach (getPemasokForReport() as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <div class="form-group">
                            <select name="transaction_status" class="form-control select2 trigger-change">
                                <option value="all">Tampilkan Semua</option>
                                <option value="1">Lunas</option>
                                <option value="2">Belum Lunas</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label style="width:100%;"> &nbsp</label>
                        <div class="form-group pull-right">
                            <a href="{{ route('report_payable-print') }}" target="_blank"
                                class="btn btn-danger btn-sm btn-flat btn-action">
                                <i class="glyphicon glyphicon-print"></i> Print
                            </a>
                            <a href="{{ route('report_payable-excel') }}"
                                class="btn btn-success btn-sm btn-flat btn-action">
                                <i class="fa fa-file-excel-o"></i> Excel
                            </a>
                            <a href="javascript:void(0)" class="btn btn-default btn-sm btn-flat btn-view-action">
                                <i class="glyphicon glyphicon-eye-open"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive" id="target-table" style="display:none;">
                    <table class="table table-bordered data-table display responsive" width="100%">
                        <thead>
                            <tr>
                                <th>Tgl Faktur</th>
                                <th>No. Faktur</th>
                                <th>Nama Pemasok</th>
                                <th>Jatuh Tempo</th>
                                <th>Uang Muka</th>
                                <th>Nilai Faktur</th>
                                <th>Total Pembayaran</th>
                                <th>Hutang</th>
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
    {{-- <script src="https://cdn.datatables.net/rowgroup/1.4.0/js/dataTables.rowGroup.min.js"></script> --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let defaultUrlIndex = '{{ route('report_payable-index') }}'

        function loadDatatable() {
            $('#target-table').show()
            table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: defaultUrlIndex + param,
                pageLength: 50,
                // fnDrawCallback: function(oSettings) {
                //     setTimeout(function() {
                //         var xxxx = $('.dtrg-end th');
                //         $.each(xxxx, function(index, value) {
                //             var ccccc = $(value).text().split(" | ");
                //             $(value).parent().html(
                //                 "<td colspan='3' style='text-align: left;background-color: #B9B9B9'><b>" +
                //                 ccccc[0] +
                //                 "</b></td><td style='text-align: right;background-color: #B9B9B9'><b>" +
                //                 ccccc[1] +
                //                 "</b></td><td style='text-align: right;background-color: #B9B9B9'><b>" +
                //                 ccccc[2] +
                //                 "</b></td><td style='text-align: right;background-color: #B9B9B9'><b>" +
                //                 ccccc[3] +
                //                 "</b></td><td style='text-align: right;background-color: #B9B9B9'>" +
                //                 ccccc[4] + '</td>' +
                //                 "</b></td><td style='text-align: right;background-color: #B9B9B9'></td>"
                //             );
                //         });
                //     }, 100);
                // },
                // rowGroup: {
                //     startRender: function(rows, group) {
                //         return '(' + group + ') ' + rows.data()[0].nama_pemasok;
                //     },
                //     endRender: function(rows, group) {
                //         var nilaiFaktur = rows
                //             .data()
                //             .pluck('mtotal_pembelian')
                //             .reduce(function(a, b) {
                //                 return a + b * 1;
                //             }, 0);

                //         var bayar = rows
                //             .data()
                //             .pluck('bayar')
                //             .reduce(function(a, b) {
                //                 return a + b * 1;
                //             }, 0);

                //         var hutang = rows
                //             .data()
                //             .pluck('sisa')
                //             .reduce(function(a, b) {
                //                 return a + b * 1;
                //             }, 0);

                //         var uangMuka = rows
                //             .data()
                //             .pluck('uang_muka')
                //             .reduce(function(a, b) {
                //                 return a + b * 1;
                //             }, 0);
                //         return '' + ' | ' + formatNumber(uangMuka, 2) +
                //             ' | ' + formatNumber(nilaiFaktur, 2) +
                //             ' | ' + formatNumber(bayar, 2) +
                //             ' | ' + formatNumber(hutang, 2);
                //     },
                //     dataSrc: 'kode_pemasok'
                // },
                columns: [{
                    data: 'tanggal_pembelian',
                    name: 'p2.tanggal_pembelian',
                }, {
                    data: 'id_transaksi',
                    name: 'a.id_transaksi',
                }, {
                    data: 'nama_pemasok',
                    name: 'pe.nama_pemasok',
                    visible: true
                }, {
                    data: 'top',
                    name: 'top',
                }, {
                    data: 'uang_muka',
                    name: 'a.uang_muka',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'mtotal_pembelian',
                    name: 'a.total',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'bayar',
                    name: 'bayar',
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
                    data: 'aging',
                    name: 'aging',
                    className: 'text-right'
                }, ]
            });
        }
    </script>
    <script src="{{ asset('js/for-report.js') }}"></script>
@endsection
