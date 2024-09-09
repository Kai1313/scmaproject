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
            Laporan Purchase Request
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Purchase Request</li>
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
                        <label>Status PO</label>
                        <div class="form-group">
                            <select name="po_status" class="form-control select2 trigger-change">
                                <option value="all">Semua Status</option>
                                <option value="0">Belum PO</option>
                                <option value="1">Sudah PO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="pull-right">
                    {{-- <a href="{{ route('report_material_usage-print') }}" target="_blank"
                        class="btn btn-danger btn-sm btn-flat btn-action">
                        <i class="glyphicon glyphicon-print"></i> Print
                    </a> --}}
                    <a href="{{ route('report_pr-excel') }}" class="btn btn-success btn-sm btn-flat btn-action">
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
                                <th>Gudang</th>
                                <th>Tanggal PR</th>
                                <th>Kode PR</th>
                                <th>Pemohon</th>
                                <th>Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah PR</th>
                                <th>Tanggal PO</th>
                                <th>Kode PO</th>
                                <th>Jumlah PO</th>
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
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/bower_components/moment/moment.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let defaultUrlIndex = '{{ route('report_pr-index') }}'
        let branch = {!! json_encode(session()->get('access_cabang')) !!}

        function loadDatatable() {
            $('#target-table').show()
            $('.data-table').DataTable().destroy();
            table = $('.data-table').DataTable({
                scrollX: true,
                lengthMenu: [
                    [10, 50, 100, -1],
                    [10, 50, 100, "All"]
                ],
                bDestroy: true,
                processing: true,
                serverSide: true,
                pageLength: 100,
                ajax: defaultUrlIndex + param,
                columns: [{
                    data: 'nama_gudang',
                    name: 'g.nama_gudang',
                }, {
                    data: 'purchase_request_date',
                    name: 'ph.purchase_request_date'
                }, {
                    data: 'purchase_request_code',
                    name: 'ph.purchase_request_code'
                }, {
                    data: 'nama_pengguna',
                    name: 'pengguna.nama_pengguna'
                }, {
                    data: 'nama_barang',
                    name: 'b.nama_barang',
                }, {
                    data: 'nama_satuan_barang',
                    name: 'sb.nama_satuan_barang',
                }, {
                    data: 'qty',
                    name: 'pd.qty',
                }, {
                    data: 'tanggal_permintaan_pembelian',
                    name: 'pph.tanggal_permintaan_pembelian'
                }, {
                    data: 'nama_permintaan_pembelian',
                    name: 'pph.nama_permintaan_pembelian',
                }, {
                    data: 'jumlah_permintaan_pembelian_detail',
                    name: 'ppd.jumlah_permintaan_pembelian_detail',
                }, {
                    data: 'notes',
                    name: 'pd.notes',
                }]
            });
        }
    </script>
    <script src="{{ asset('js/for-report.js') }}"></script>
@endsection
