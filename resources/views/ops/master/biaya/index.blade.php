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
                        <a href="{{ route('master-biaya-entry') }}" class="btn btn-success pull-right btn-flat btn-sm">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Data Biaya
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
                                <th>Nama Biaya</th>
                                <th>Akun Biaya</th>
                                <th>PPn</th>
                                <th>PPh</th>
                                <th class="text-right">Nilai PPh</th>
                                <th>Akun PPh</th>
                                <th>Aktif</th>
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
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
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
