@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
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
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Biaya
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Master Biaya</li>
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
                                    <option value="{{ $branch->id_cabang }}">{{ $branch->kode_cabang }} -
                                        {{ $branch->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <a href="{{ route('master-biaya-entry') }}" class="btn btn-success pull-right btn-flat btn-sm">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Data Biaya
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Nama Biaya</th>
                                <th>Akun Biaya</th>
                                <th>PPn</th>
                                <th>PPh</th>
                                <th>Nilai PPh</th>
                                <th>Akun PPh</th>
                                <th>Aktif</th>
                                <th>Action</th>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $('.select2').select2()
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master-biaya') }}?c=" + $('[name="id_cabang"]').val(),
            columns: [{
                data: 'nama_biaya',
                name: 'nama_biaya'
            }, {
                data: 'akun_biaya',
                name: 'ma.nama_akun'
            }, {
                data: 'isppn',
                name: 'isppn',
                className: 'text-center'
            }, {
                data: 'ispph',
                name: 'ispph',
                className: 'text-center'
            }, {
                data: 'value_pph',
                name: 'value_pph',
                render: $.fn.dataTable.render.number('.', ',', 2),
                className: 'text-right'
            }, {
                data: 'akun_pph',
                name: 'man.nama_akun'
            }, {
                data: 'aktif',
                name: 'aktif',
                className: 'text-center'
            }, {
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false
            }, ]
        });

        $('[name="id_cabang"]').change(function() {
            table.ajax.url("?c=" + $(this).val()).load()
        })
    </script>
@endsection
