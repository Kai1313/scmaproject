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
            Uang Muka Penjualan
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Uang Muka Penjualan</li>
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
                        <a href="{{ route('sales-down-payment-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Uang Muka Penjualan
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>ID Uang Muka Penjualan</th>
                                <th>Tanggal</th>
                                <th>ID Permintaan Penjualan (SO)</th>
                                <th>Pelanggan</th>
                                <th>Mata Uang</th>
                                <th>Rate</th>
                                <th>Jenis PPN</th>
                                <th>Nominal</th>
                                <th>DPP</th>
                                <th>PPN</th>
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
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var defaultFilter = sessionStorage.getItem('sales_down_payment_filter') ? JSON.parse(sessionStorage.getItem(
            'sales_down_payment_filter')) : {};
        for (const key in defaultFilter) {
            $('[name="' + key + '"]').val(defaultFilter[key])
        }

        $('.select2').select2()
        var table = $('.data-table').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: "{{ route('sales-down-payment') }}?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $(
                '[name="show_void"]').is(':checked'),
            columns: [{
                data: 'kode_uang_muka_penjualan',
                name: 'ump.kode_uang_muka_penjualan'
            }, {
                data: 'tanggal',
                name: 'ump.tanggal'
            }, {
                data: 'nama_permintaan_penjualan',
                name: 'pp.nama_permintaan_penjualan',
            }, {
                data: 'nama_pelanggan',
                name: 'p.nama_pelanggan',
            }, {
                data: 'nama_mata_uang',
                name: 'mu.nama_mata_uang',
            }, {
                data: 'rate',
                name: 'ump.rate',
                render: function(data) {
                    return data ? formatNumber(data, 2) : 0
                },
                className: 'text-right'
            }, {
                data: 'ppn_uang_muka_penjualan',
                name: 'ump.ppn_uang_muka_penjualan',
                render: function(data) {
                    switch (data) {
                        case '0':
                            label = '<label class="label label-danger">Tanpa PPN</label>'
                            break;
                        case '1':
                            label = '<label class="label label-warning">Include</label>'
                            break;
                        case '2':
                            label = '<label class="label label-success">Exclude</label>'
                            break;
                        default:
                            label = ''
                            break;
                    }

                    return label
                }
            }, {
                data: 'nominal',
                name: 'ump.nominal',
                render: function(data) {
                    return data ? formatNumber(data, 2) : 0
                },
                className: 'text-right'
            }, {
                data: 'dpp',
                name: 'ump.dpp',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'ppn',
                name: 'ump.ppn',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'total',
                name: 'ump.total',
                render: function(data, d, r) {
                    return data ? formatNumber(data * r.rate, 2) : 0
                },
                className: 'text-right'
            }, {
                data: 'catatan',
                name: 'ump.catatan',
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

            sessionStorage.setItem('sales_down_payment_filter', JSON.stringify(defaultFilter));
        }
    </script>
@endsection
