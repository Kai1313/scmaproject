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
                            <select name="id_cabang" class="form-control">
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch->id_cabang }}">{{ $branch->kode_cabang }} -
                                        {{ $branch->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <span style="margin-right:10px;">Tampilkan Gambar</span> <input type="checkbox" name="show_image">
                    </div>
                    <div class="col-md-8">
                        <a href="{{ route('master-wrapper-entry') }}" class="btn btn-success pull-right btn-flat btn-sm">
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
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>Nama Pembungkus</th>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master-wrapper') }}" + "?c=" + $('[name="id_cabang"]').val() + '&show_img=' + $(
                '[name="show_image"]').is(':checked'),
            columns: [{
                data: 'nama_wrapper',
                name: 'nama_wrapper'
            }, {
                data: 'weight',
                name: 'weight',
                render: $.fn.dataTable.render.number('.', ',', 4),
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
        })

        $('[name="show_image"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_img=' + $('[name="show_image"]').is(
                ':checked')).load()
        })
    </script>
@endsection
