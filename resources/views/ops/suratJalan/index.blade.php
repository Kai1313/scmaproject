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
            Surat Jalan Umum
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Surat Jalan Umum</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-8">

                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('surat_jalan_umum-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Surat Jalan
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>No Surat Jalan</th>
                                <th>Tanggal</th>
                                <th>No Dokumen Lain</th>
                                <th>Penerima</th>
                                <th>Pembuat</th>
                                <th>Keterangan</th>
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
        var defaultFilter = sessionStorage.getItem('send_to_branch_filter') ? JSON.parse(sessionStorage.getItem(
            'send_to_branch_filter')) : {};
        for (const key in defaultFilter) {
            $('[name="' + key + '"]').val(defaultFilter[key])
        }

        $('.select2').select2()
        var table = $('.data-table').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: "{{ route('surat_jalan_umum') }}",
            columns: [{
                data: 'no_surat_jalan',
                name: 'no_surat_jalan',
                width: 130
            }, {
                data: 'tanggal',
                name: 'tanggal',
                width: 100
            }, {
                data: 'no_dokumen_lain',
                name: 'no_dokumen_lain',
                width: 150
            }, {
                data: 'penerima',
                name: 'penerima',
                width: 150
            }, {
                data: 'nama_pengguna',
                name: 'nama_pengguna',
                width: 100
            }, {
                data: 'keterangan',
                name: 'keterangan',
            }, {
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false,
                width: 150
            }, ]
        });

        function changeFilter() {
            $('.change-filter').each(function(i, v) {
                defaultFilter[$(v).prop('name')] = $(v).val()
            })

            sessionStorage.setItem('send_to_branch_filter', JSON.stringify(defaultFilter));
        }
    </script>
@endsection
