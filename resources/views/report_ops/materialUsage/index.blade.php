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
            Laporan Pemakaian
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Pemakaian</li>
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
                        <label>Gudang</label>
                        <div class="form-group">
                            <select name="id_gudang" class="form-control select2 trigger-change">
                                <option value="all">Semua Gudang</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="text" name="date" class="form-control trigger-change">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Jenis Laporan</label>
                        <div class="form-group">
                            <select name="type" class="form-control select2 trigger-change">
                                @foreach ($typeReport as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="pull-right">
                    <a href="{{ route('report_material_usage-print') }}" target="_blank"
                        class="btn btn-danger btn-sm btn-flat btn-action">
                        <i class="glyphicon glyphicon-print"></i> Print
                    </a>
                    <a href="{{ route('report_material_usage-excel') }}" class="btn btn-success btn-sm btn-flat btn-action">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <a href="javascript:void(0)" class="btn btn-default btn-sm btn-flat btn-view-action">
                        <i class="glyphicon glyphicon-eye-open"></i> View
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive" id="target-table-rekap" style="display:none;">
                    <table class="table table-bordered data-table-rekap display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kode Transaksi</th>
                                <th>Cabang</th>
                                <th>Gudang</th>
                                <th>Jenis Pemakaian</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

                <div class="table-responsive" id="target-table-detail" style="display:none;">
                    <table class="table table-bordered data-table-detail display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kode Transaksi</th>
                                <th>Cabang</th>
                                <th>Gudang</th>
                                <th>Jenis Pemakaian</th>
                                <th>QR Code</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Jumlah Zak</th>
                                <th>Berat Zak</th>
                                <th>Catatan Header</th>
                                <th>Catatan Detail</th>
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
        let defaultUrlIndex = '{{ route('report_material_usage-index') }}'
        let branch = {!! json_encode(session()->get('access_cabang')) !!}

        function loadDatatable() {
            reportType = $('[name="type"]').val()
            switch (reportType) {
                case 'Rekap':
                    $('#target-table-detail').hide()
                    $('.data-table-rekap').DataTable().destroy();
                    $('.data-table-rekap').find('tbody').html('')

                    $('#target-table-rekap').show()
                    table = $('.data-table-rekap').DataTable({
                        scrollX: true,
                        lengthMenu: [
                            [10, 50, 100, -1],
                            [10, 50, 100, "All"]
                        ],
                        bDestroy: true,
                        processing: true,
                        serverSide: true,
                        ajax: defaultUrlIndex + param,
                        columns: [{
                            data: 'tanggal',
                            name: 'mu.tanggal'
                        }, {
                            data: 'kode_pemakaian',
                            name: 'mu.kode_pemakaian'
                        }, {
                            data: 'nama_cabang',
                            name: 'c.nama_cabang',
                        }, {
                            data: 'nama_gudang',
                            name: 'g.nama_gudang',
                        }, {
                            data: 'jenis_pemakaian',
                            name: 'mu.jenis_pemakaian',
                        }, {
                            data: 'catatan',
                            name: 'mu.catatan',
                        }]
                    });
                    break;
                case 'Detail':
                    $('#target-table-rekap').hide()
                    $('.data-table-detail').DataTable().destroy();
                    $('.data-table-detail').find('tbody').html('')

                    $('#target-table-detail').show()
                    table = $('.data-table-detail').DataTable({
                        scrollX: true,
                        lengthMenu: [
                            [10, 50, 100, -1],
                            [10, 50, 100, "All"]
                        ],
                        bDestroy: true,
                        processing: true,
                        serverSide: true,
                        ajax: defaultUrlIndex + param,
                        columns: [{
                            data: 'tanggal',
                            name: 'ph.tanggal'
                        }, {
                            data: 'kode_pemakaian',
                            name: 'ph.kode_pemakaian'
                        }, {
                            data: 'nama_cabang',
                            name: 'c.nama_cabang',
                        }, {
                            data: 'nama_gudang',
                            name: 'g.nama_gudang',
                        }, {
                            data: 'jenis_pemakaian',
                            name: 'ph.jenis_pemakaian',
                        }, {
                            data: 'kode_batang',
                            name: 'pd.kode_batang',
                        }, {
                            data: 'nama_barang',
                            name: 'b.nama_barang',
                        }, {
                            data: 'jumlah',
                            name: 'pd.jumlah',
                            render: function(data) {
                                return data ? formatNumber(data, 4) : 0
                            },
                            className: 'text-right'
                        }, {
                            data: 'jumlah_zak',
                            name: 'pd.jumlah_zak',
                            render: function(data) {
                                return data ? formatNumber(data, 4) : 0
                            },
                            className: 'text-right'
                        }, {
                            data: 'weight_zak',
                            name: 'pd.weight_zak',
                            render: function(data) {
                                return data ? formatNumber(data, 4) : 0
                            },
                            className: 'text-right'
                        }, {
                            data: 'catatan_header',
                            name: 'ph.catatan',
                        }, {
                            data: 'catatan_detail',
                            name: 'pd.catatan',
                        }]
                    });
                    break;
                default:
                    break;
            }
        }
    </script>
    <script src="{{ asset('js/for-report.js') }}"></script>
@endsection
