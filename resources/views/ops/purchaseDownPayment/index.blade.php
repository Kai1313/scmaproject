@extends('layouts.main')
@section('addedStyles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <!-- Treetable -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/jquery-treetable/css/jquery.treetable.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/jquery-treetable/css/jquery.treetable.theme.default.css') }}">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Uang Muka Pembelian
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Uang Muka Pembelian</li>
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
                            <select name="id_cabang" class="form-control">
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch->id_cabang }}">{{ $branch->kode_cabang }} -
                                        {{ $branch->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 head-checkbox">
                        <label for="">Tampilkan Void</label> <input type="checkbox" name="show_void">
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('purchase-down-payment-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Uang Muka Pembelian
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                @if (session()->has('success'))
                    <div class="alert alert-success">
                        <ul>
                            <li>{!! session()->get('success') !!}</li>
                        </ul>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>ID Uang Muka Pembelian</th>
                                <th>Tanggal</th>
                                <th>ID Permintaan Pembelian (PO)</th>
                                <th>Supplier</th>
                                <th>Mata Uang</th>
                                <th>Rate</th>
                                <th>Nominal</th>
                                <th>Total</th>
                                <th>Catatan</th>
                                <th width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="approvalDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4>Anda akan menghapus data ini!</h4>
                    </div>
                    <div class="modal-footer">
                        <form action="" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <!-- DataTables -->
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('assets/bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- TreeTable -->
    <script src="{{ asset('assets/bower_components/jquery-treetable/jquery.treetable.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('purchase-down-payment') }}?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $(
                '[name="show_void"]').is(':checked'),
            columns: [{
                data: 'kode_uang_muka_pembelian',
                name: 'kode_uang_muka_pembelian'
            }, {
                data: 'tanggal',
                name: 'tanggal'
            }, {
                data: 'nama_permintaan_pembelian',
                name: 'nama_permintaan_pembelian',
            }, {
                data: 'nama_pemasok',
                name: 'nama_pemasok',
            }, {
                data: 'nama_mata_uang',
                name: 'nama_mata_uang',
            }, {
                data: 'rate',
                name: 'rate',
                render: $.fn.dataTable.render.number('.', ',', 2),
                className: 'text-right'
            }, {
                data: 'nominal',
                name: 'nominal',
                render: $.fn.dataTable.render.number('.', ',', 2),
                className: 'text-right'
            }, {
                data: 'total',
                name: 'total',
                render: $.fn.dataTable.render.number('.', ',', 2),
                className: 'text-right'
            }, {
                data: 'catatan',
                name: 'catatan',
            }, {
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false
            }, ]
        });

        $(document).on('click', '.btn-destroy', function(e) {
            e.preventDefault()
            let route = $(this).prop('href')
            $('#approvalDelete').modal('show').find('form').attr('action', route)
        })

        $('[name="id_cabang"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $('[name="show_void"]').is(
                ':checked')).load()
        })

        $('[name="show_void"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $('[name="show_void"]').is(
                ':checked')).load()
        })
    </script>
@endsection