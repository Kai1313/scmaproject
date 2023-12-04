@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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

        .select2 {
            width: 100% !important;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Kunjungan
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Kunjungan</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-2 filter-div">
                        <label>Cabang</label>
                        <div class="form-group">
                            <select id="id_cabang" class="form-control select2" name="id_cabang">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="text" id="daterangepicker" class="form-control" name="daterangepicker" />
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Sales</label>
                        <div class="form-group">
                            <select id="id_salesman" class="form-control select2" name="id_salesman">
                                <option value="">Semua Sales</option>
                                @foreach ($salesmans as $sales)
                                    <option value="{{ $sales->id }}">{{ $sales->text }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Status</label>
                        <div class="form-group">
                            <select id="status" class="form-control select2" name="status">
                                <option value="">Semua Status</option>
                                <option value="1">Belum Visit</option>
                                <option value="2">Sudah Visit</option>
                                <option value="0">Batal Visit</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Kategori Pelanggan</label>
                        <div class="form-group">
                            <select id="status_pelanggan" class="form-control select2" name="status_pelanggan">
                                <option value="">Semua Kategori</option>
                                @foreach ($customerCategory as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label class="d-block">&nbsp;</label>
                        <div class="form-group">
                            <button type="button" class="btn btn-info" onclick="table.ajax.reload()"><i
                                    class="fa fa-search"></i> Cari</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered data-table display responsive nowrap" width="100%">
                                <thead>
                                    <tr>
                                        <th style="width:30px;">Action</th>
                                        <th>Kode Jadwal</th>
                                        <th>Tanggal</th>
                                        <th>Salesman</th>
                                        <th>Pelanggan</th>
                                        <th>Kategori Pelanggan</th>
                                        <th>Status</th>
                                        <th>Status Report</th>
                                        <th style="width:300px;">Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endsection

@section('externalScripts')
    <script>
        var salesman = {!! json_encode($salesmans) !!}
        $('.select2').select2();

        $('#daterangepicker').daterangepicker({
            timePicker: false,
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        filterDatatable()

        function filterDatatable() {
            let param = {};
            $('.box-header').find('select,input').each(function(i, v) {
                param[$(v).attr('name')] = function() {
                    return $(v).val()
                }
            })

            return param;
        }

        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            "ordering": false,
            ajax: {
                url: "{{ route('visit') }}",
                data: filterDatatable(),
            },
            columns: [{
                data: 'action',
                name: 'action',
                className: 'text-center',
            }, {
                data: 'visit_code',
                name: 'visit_code'
            }, {
                data: 'visit_date',
                name: 'visit_date'
            }, {
                data: 'salesman.nama_salesman',
                name: 'salesman.nama_salesman',
            }, {
                data: 'pelanggan.nama_pelanggan',
                name: 'pelanggan.nama_pelanggan',
            }, {
                data: 'status_pelanggan',
                name: 'status_pelanggan',
                class: 'text-center',
            }, {
                data: 'status',
                name: 'status',
                class: 'text-center',
            }, {
                data: 'progress_ind',
                name: 'progress_ind',
                class: 'text-center',
            }, {
                data: 'detail',
                name: 'detail',
                className: 'limit-text'
            }],
        });
    </script>
@endsection
