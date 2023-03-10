@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
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
                    <div class="col-md-8">
                        <span class="badge badge-default rounded-0 pull-right">
                            <input class="form-check-input" type="checkbox" id="void" name="show_void">
                            <label class="form-check-label" for="void">
                                Void
                            </label>
                        </span>
                        <a href="{{ route('purchase-down-payment-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Uang Muka Pembelian
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
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
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
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
