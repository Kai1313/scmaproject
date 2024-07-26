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
            Pemakaian
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Pemakaian</li>
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
                        <a href="{{ route('material_usage-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Pemakaian
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Pemakaian</th>
                                <th>Tanggal</th>
                                <th>Cabang Pemakaian</th>
                                <th>Gudang Pemakaian</th>
                                <th>Jenis Pemakaian</th>
                                <th>QC</th>
                                <th>Catatan</th>
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
        var defaultFilter = sessionStorage.getItem('material_usage_filter') ? JSON.parse(sessionStorage.getItem(
            'material_usage_filter')) : {};
        for (const key in defaultFilter) {
            $('[name="' + key + '"]').val(defaultFilter[key])
        }

        $('.select2').select2()
        var table = $('.data-table').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            oSearch: {
                sSearch: defaultFilter.search
            },
            pageLength: defaultFilter.page_length ? parseInt(defaultFilter.page_length) : 50,
            displayStart: defaultFilter.page_length * defaultFilter.page,
            ajax: "{{ route('material_usage') }}?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $(
                '[name="show_void"]').is(':checked'),
            columns: [{
                data: 'kode_pemakaian',
                name: 'pemakaian_header.kode_pemakaian'
            }, {
                data: 'tanggal',
                name: 'pemakaian_header.tanggal'
            }, {
                data: 'nama_cabang',
                name: 'c.nama_cabang',
            }, {
                data: 'nama_gudang',
                name: 'g.nama_gudang',
            }, {
                data: 'jenis_pemakaian',
                name: 'jenis_pemakaian',
            }, {
                data: 'is_qc',
                name: 'is_qc',
                render: function(data) {
                    return data == 1 ? '<i class="fa fa-check" aria-hidden="true"></i>' : ''
                }
            }, {
                data: 'catatan',
                name: 'pemakaian_header.catatan'
            }, {
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false
            }, ],
            drawCallback: function(settings) {
                var api = this.api();
                defaultFilter.page = api.page()
                changeFilter()
            }
        });

        var keyupTimer;
        table.on('search.dt', function() {
            clearTimeout(keyupTimer);
            keyupTimer = setTimeout(function() {
                if ($('[type="search"]').val() != defaultFilter.search) {
                    defaultFilter.search = $('[type="search"]').val()
                    changeFilter()
                }
            }, 1000);
        });

        table.on('length.dt', function(e, settings, len) {
            defaultFilter.page_length = len
            changeFilter()
        });

        table.on('page.dt', function(e, settings) {
            defaultFilter.page = table.page.info().page
            changeFilter()
        })

        $('.change-filter').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $('[name="show_void"]').is(
                ':checked')).load()
            changeFilter()
        })

        $('[name="show_void"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $('[name="show_void"]').is(
                ':checked')).load()
        })

        function changeFilter() {
            $('.change-filter').each(function(i, v) {
                defaultFilter[$(v).prop('name')] = $(v).val()
            })

            sessionStorage.setItem('material_usage_filter', JSON.stringify(defaultFilter));
        }
    </script>
@endsection
