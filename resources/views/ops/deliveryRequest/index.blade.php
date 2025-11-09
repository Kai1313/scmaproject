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

        video {
            width: 60%;
        }

        @media screen and (max-width: 1024px) {
            video {
                width: 100%;
            }
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Permintaan Pengiriman
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Permintaan Pengiriman</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            {{-- <div class="box-header">
                <div class="row">
                    <div class="col-md-4">
                        <label>Cabang Asal</label>
                        <div class="form-group">
                            <select name="id_cabang" class="form-control select2 change-filter">
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
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
            </div> --}}
            <div class="box-header">
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('delivery_request-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Permintaan Pengiriman
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Tanggal</th>
                                <th>Cabang Peminta</th>
                                <th>Cabang Tujuan</th>
                                <th>Keterangan</th>
                                <th>Persetujuan</th>
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
            scrollX: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: "{{ route('delivery_request') }}",
            columns: [{
                data: 'delivery_request_code',
                name: 'delivery_request_code'
            }, {
                data: 'date',
                name: 'date'
            }, {
                data: 'branch_nama_cabang',
                name: 'branch.nama_gudang'
            }, {
                data: 'dest_nama_cabang',
                name: 'dest_cabang.nama_cabang',
            }, {
                data: 'desc',
                name: 'delivery_request.desc',
            }, {
                data: 'approval_status',
                name: 'delivery_request.approval_status',
            }, {
                data: 'status',
                name: 'delivery_request.status',
            }, {
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false
            }, ]
        });
    </script>
@endsection
