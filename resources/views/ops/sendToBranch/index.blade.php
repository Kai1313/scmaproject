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
            Kirim Ke Cabang
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Kirim Ke Cabang</li>
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
                        <span class="badge badge-default rounded-0 pull-right">
                            <input class="form-check-input" type="checkbox" id="void" name="show_void">
                            <label class="form-check-label" for="void">
                                Void
                            </label>
                        </span>
                        <a href="{{ route('send_to_branch-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Kirim Ke Cabang
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kode Pindah Gudang</th>
                                <th>Gudang</th>
                                <th>Cabang Tujuan</th>
                                <th>Keterangan</th>
                                <th>Jasa Pengiriman</th>
                                <th>Status</th>
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
            ajax: "{{ route('send_to_branch') }}?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $(
                '[name="show_void"]').is(':checked'),
            columns: [{
                data: 'tanggal_pindah_barang',
                name: 'tanggal_pindah_barang'
            }, {
                data: 'kode_pindah_barang',
                name: 'kode_pindah_barang'
            }, {
                data: 'nama_gudang',
                name: 'nama_gudang'
            }, {
                data: 'nama_cabang',
                name: 'nama_cabang',
            }, {
                data: 'keterangan_pindah_barang',
                name: 'keterangan_pindah_barang',
            }, {
                data: 'transporter',
                name: 'transporter',
            }, {
                data: 'status_pindah_barang',
                name: 'status_pindah_barang',
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