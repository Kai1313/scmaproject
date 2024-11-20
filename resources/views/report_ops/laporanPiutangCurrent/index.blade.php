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
                            <select name="id_cabang" class="form-control select2 trigger-change" style="width:100%;">
                                @foreach (getCabangForReport() as $branch)
                                    <option value="{{ $branch['id'] }}"
                                        {{ request()->id_cabang == $branch['id'] ? 'selected' : '' }}>{{ $branch['text'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="date" name="dateReport" class="form-control trigger-change"
                                value="{{ request()->date ?? date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Pelanggan</label>
                        <div class="form-group">
                            <select name="id_pelanggan" class="form-control select2 trigger-change" style="width:100%;">
                                @foreach (getPelangganForReport() as $customer)
                                    <option value="{{ $customer['id'] }}"
                                        {{ request()->id_pelanggan == $customer['id'] ? 'selected' : '' }}>
                                        {{ $customer['text'] }}</option>
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
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Tgl Faktur</th>
                                <th>No. Faktur</th>
                                <th>Nama Pelanggan</th>
                                <th>Jatuh Tempo</th>
                                <th>Nilai Faktur</th>
                                <th>Uang Muka</th>
                                <th>Pembayaran</th>
                                <th>Total Terbayar</th>
                                <th>Sisa</th>
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
    <div id="modal-payment" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Daftar Transaksi Pembayaran</h4>
                </div>
                <table class="table" style="margin-bottom:20px;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Transaksi</th>
                            <th>Tanggal Bayar</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="target-transaction">

                    </tbody>
                </table>
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
        let defaultUrlIndex = '{{ route('report_receiveable-index') }}'

        function loadDatatable() {
            $('#target-table').show()
            table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 50,
                ajax: defaultUrlIndex + param,
                columns: [{
                    data: 'tanggal_penjualan',
                    name: 'p2.tanggal_penjualan',
                }, {
                    data: 'id_transaksi',
                    name: 'a.id_transaksi',
                }, {
                    data: 'nama_pelanggan',
                    name: 'pe.nama_pelanggan',
                    visible: true
                }, {
                    data: 'top',
                    name: 'top',
                }, {
                    data: 'mtotal_penjualan',
                    name: 'a.total',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'uang_muka',
                    name: 'a.uang_muka',
                    render: function(data) {
                        return data ? formatNumber(data, 2) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'bayar',
                    name: 'a.bayar',
                    className: 'text-right'
                }, {
                    data: 'terbayar',
                    name: 'terbayar',
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
    <script>
        @if (request()->action == '1')
            $('.btn-view-action').click()
        @endif

        $('#target-table').on('click', '.show-payment', function() {
            $('#cover-spin').show()
            let idTransaksi = $(this).data('id');
            $.ajax({
                url: "{{ route('report_receiveable-get_journal') }}",
                type: 'get',
                data: {
                    id_transaksi: idTransaksi
                },
                success: function(res) {
                    $('#target-transaction').html(res.html)
                    $('#modal-payment').modal()
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    $('#cover-spin').hide()
                    Swal.fire("Gagal proses data. ", error.responseJSON.message, 'error')
                }
            })
        })
    </script>
@endsection
