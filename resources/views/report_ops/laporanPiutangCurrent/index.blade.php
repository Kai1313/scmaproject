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
            Laporan Piutang
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Piutang </li>
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
                    <div class="col-md-4">
                        <label>Pelanggan</label>
                        <div class="form-group">
                            <select name="id_pelanggan" class="form-control select2 trigger-change">
                                @foreach (getPelangganForReport() as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label style="width:100%;"> &nbsp</label>
                        <div class="form-group pull-right">
                            <a href="{{ route('report_receiveable-print') }}" target="_blank"
                                class="btn btn-danger btn-sm btn-flat btn-action">
                                <i class="glyphicon glyphicon-print"></i> Print
                            </a>
                            <a href="{{ route('report_receiveable-excel') }}"
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
                    <table class="table table-bordered data-table display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Pelanggan</th>
                                <th>Nama Pelanggan</th>
                                <th>No. Faktur</th>
                                <th>Tgl Faktur</th>
                                <th>Jatuh Tempo</th>
                                <th>Nilai Faktur</th>
                                <th>Total Pembayaran</th>
                                <th>Sisa Piutang</th>
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
    <script src="https://cdn.datatables.net/rowgroup/1.4.0/js/dataTables.rowGroup.min.js"></script>
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
                fnDrawCallback: function(oSettings) {
                    setTimeout(function() {
                        var xxxx = $('.dtrg-end th');
                        $.each(xxxx, function(index, value) {
                            var ccccc = $(value).text().split(" | ");
                            console.log(value)
                            $(value).parent().html(
                                "<td colspan='3' style='text-align: left;background-color: #B9B9B9'><b>" +
                                ccccc[0] +
                                "</b></td><td style='text-align: right;background-color: #B9B9B9'><b>" +
                                ccccc[1] +
                                "</b></td><td style='text-align: right;background-color: #B9B9B9'><b>" +
                                ccccc[2] +
                                `</b></td><td style='text-align: right;background-color: #B9B9B9'>${ccccc[3]}</td>` +
                                "</b></td><td style='text-align: right;background-color: #B9B9B9'></td>"
                            );
                        });
                    }, 100);
                },
                rowGroup: {
                    startRender: function(rows, group) {
                        return '(' + group + ') ' + rows.data()[0].nama_pelanggan;
                    },
                    endRender: function(rows, group) {
                        var nilaiFaktur = rows
                            .data()
                            .pluck('mtotal_penjualan')
                            .reduce(function(a, b) {
                                return a + b * 1;
                            }, 0);

                        var bayar = rows
                            .data()
                            .pluck('bayar')
                            .reduce(function(a, b) {
                                return a + b * 1;
                            }, 0);

                        var hutang = rows
                            .data()
                            .pluck('sisa')
                            .reduce(function(a, b) {
                                return a + b * 1;
                            }, 0);
                        return '' + ' | ' + $.fn.dataTable.render.number('.',
                            ',', 0, '').display(nilaiFaktur) + ' | ' + $.fn.dataTable.render.number('.',
                            ',', 0, '').display(bayar) + ' | ' + $.fn.dataTable.render.number('.',
                            ',', 0, '').display(hutang);
                    },
                    dataSrc: 'kode_pelanggan'
                },
                columns: [{
                    data: 'kode_pelanggan',
                    name: 'kode_pelanggan',
                    visible: false
                }, {
                    data: 'nama_pelanggan',
                    name: 'nama_pelanggan',
                    visible: false
                }, {
                    data: 'id_transaksi',
                    name: 'id_transaksi',
                }, {
                    data: 'tanggal_penjualan',
                    name: 'tanggal_penjualan',
                    render: function(data) {
                        return data ? formatDate(data) : ''
                    },
                }, {
                    data: 'top',
                    name: 'top',
                    render: function(data) {
                        return data ? formatDate(data) : ''
                    },
                }, {
                    data: 'mtotal_penjualan',
                    name: 'mtotal_penjualan',
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
