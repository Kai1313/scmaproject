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

        .rounded-0 {
            border-radius: 0;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Pembungkus
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Master Pembungkus</li>
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
                            <input class="form-check-input" type="checkbox" id="show_image" name="show_image">
                            <label class="form-check-label" for="show_image">
                                Gambar
                            </label>
                        </span>
                        <a href="{{ route('master-wrapper-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Data Pembungkus
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
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Nama Pembungkus</th>
                                <th>Kategori Pembungkus</th>
                                <th class="text-right">Berat</th>
                                <th>Catatan</th>
                                <th>Gambar</th>
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
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var defaultFilter = sessionStorage.getItem('master_wrapper_filter') ? JSON.parse(sessionStorage.getItem(
            'master_wrapper_filter')) : {};
        for (const key in defaultFilter) {
            $('[name="' + key + '"]').val(defaultFilter[key])
        }

        $('.select2').select2()
        var table = $('.data-table').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            order: [],
            pageLength: 50,
            ajax: "{{ route('master-wrapper') }}" + "?c=" + $('[name="id_cabang"]').val() + '&show_img=' + $(
                '[name="show_image"]').is(':checked'),
            columns: [{
                data: 'nama_wrapper',
                name: 'nama_wrapper'
            }, {
                data: 'kategori_wrapper',
                name: 'id_kategori_wrapper'
            }, {
                data: 'weight',
                name: 'weight',
                render: function(data) {
                    return formatNumber(data, 4)
                },
                className: "text-right"
            }, {
                data: 'catatan',
                name: 'catatan'
            }, {
                data: 'path2',
                name: 'path2',
                className: 'text-center',
            }, {
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false
            }]
        });

        $('[name="id_cabang"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_img=' + $('[name="show_image"]').is(
                ':checked')).load()
            changeFilter()
        })

        $('[name="show_image"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_img=' + $('[name="show_image"]').is(
                ':checked')).load()
        })

        function changeFilter() {
            $('.change-filter').each(function(i, v) {
                defaultFilter[$(v).prop('name')] = $(v).val()
            })

            sessionStorage.setItem('master_wrapper_filter', JSON.stringify(defaultFilter));
        }
    </script>
@endsection
