@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="{{ asset('css/fancybox.css') }}" />
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

        #recap-data>tr>td {
            border-bottom: 1px solid #777;
            padding: 5px;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Laporan Kunjungan
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Kunjungan</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-2 filter-div">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="text" class="form-control trigger-change" name="date" />
                        </div>
                    </div>
                    {{-- @if ($groupUser != 6) --}}
                    <div class="col-md-2 filter-div">
                        <label>Sales</label>
                        <div class="form-group">
                            <select class="form-control select2 trigger-change" name="id_salesman">
                                <option value="">Semua Sales</option>
                                @foreach ($salesmans as $sales)
                                    <option value="{{ $sales->id }}">{{ $sales->text }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Pelanggan</label>
                        <div class="form-group">
                            <select class="form-control select2 trigger-change" name="id_pelanggan">
                                <option value="">Semua Pelanggan</option>

                            </select>
                        </div>
                    </div>
                    {{-- @else
                        <input type="hidden" name="id_salesman" value="{{ $idUser }}" class="trigger-change">
                    @endif --}}
                    <div class="col-md-2 filter-div">
                        <label>Jenis Laporan</label>
                        <div class="form-group">
                            <select class="form-control select2 trigger-change" name="report_type">
                                <option value="rekap">Rekap</option>
                                <option value="detail">Detail</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div" style="padding-top:27px;">
                        <a href="{{ route('visit_report_excel') }}" class="btn btn-success btn-action btn-sm btn-flat"
                            style="margin-bottom:5px;">
                            <i class="fa fa-file-excel-o"></i> Excel
                        </a>
                        <a href="javascript:void(0)" class="btn btn-default btn-action btn-view-action btn-sm btn-flat"
                            style="margin-bottom:5px;">
                            <i class="glyphicon glyphicon-eye-open"></i> View
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body target-recap" style="display:none;">
                <div class="table-responsive">
                    <table class="table table-bordered" id="main-data">
                        <thead>
                            <tr>
                                <th rowspan="2" width="110">Sales Name</th>
                                <th rowspan="2" width="110">Date</th>
                                <th rowspan="2" width="220">Customer</th>
                                <th rowspan="2" width="220">Category</th>
                                <th colspan="{{ count($activities) }}">Activity</th>
                                <th rowspan="2">Description</th>
                            </tr>
                            <tr>
                                @foreach ($activities as $activity)
                                    <th width="50">{{ $activity }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="box-body target-detail" style="display:none;">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered data-table display" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:80px;">Tanggal</th>
                                    <th style="width:80px;">Sales</th>
                                    <th>Pelanggan</th>
                                    <th style="width:200px">Hasil Kunjungan</th>
                                    <th style="width:200px">Masalah</th>
                                    <th style="width:200px">Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row target-recap" style="display:none;">
            <div class="col-md-5">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Rekap</h3>
                    </div>
                    <div class="box-body">
                        <table id="recap-data"></table>
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
    <script src="{{ asset('js/fancybox.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var salesman = {!! json_encode($salesmans) !!}
        var activities = {!! json_encode($activities) !!}
        let defaultUrlIndex = '{{ route('visit_report') }}'
        $('#daterangepicker').daterangepicker({
            timePicker: false,
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        function loadDatatable() {
            if ($('[name="report_type"]').val() == 'rekap') {
                $('.target-recap').show()
                $('.target-detail').hide()
                $('#cover-spin').show()
                $('#main-data').find('tbody').html('')
                $('#recap-data').html('')
                $.ajax({
                    url: defaultUrlIndex + param,
                    success: function(res) {
                        if (res.result) {
                            $('#main-data').find('tbody').html(res.htmlMainData)
                            $('#recap-data').html(res.htmlRecapData)
                            Fancybox.bind('[data-fancybox="gallery"]');
                        }

                        $('#cover-spin').hide()
                    },
                    error: function(error) {
                        $('#cover-spin').hide()
                        Swal.fire("Gagal Ambil Data. ", error.responseJSON.message, 'error')
                    }
                })
            } else {
                $('.target-recap').hide()
                $('.target-detail').show()
                $('.data-table').DataTable().destroy();
                $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 50,
                    scrollX: true,
                    ajax: {
                        url: "{{ route('visit_report') }}" + param,
                    },
                    language: {
                        processing: '<img src="{{ asset('images/833.gif') }}" alt="">',
                        paginate: {
                            'first': 'First',
                            'last': 'Last',
                            'next': '→',
                            'previous': '←'
                        },
                        emptyTable: "Data Tidak Ditemukan",
                    },
                    columns: [{
                        data: 'visit_date',
                        name: 'visit_date'
                    }, {
                        data: 'salesman.nama_salesman',
                        name: 'salesman.nama_salesman',
                    }, {
                        data: 'pelanggan.nama_pelanggan',
                        name: 'pelanggan.nama_pelanggan',
                    }, {
                        data: 'visit_title',
                        name: 'visit_title',
                    }, {
                        data: 'visit_desc',
                        name: 'visit_desc',
                    }, {
                        data: 'solusi',
                        name: 'solusi',
                    }],
                });
            }
        }
    </script>
    <script src="{{ asset('js/for-report.js') }}"></script>
    <script>
        $('[name="id_pelanggan"]').select2({
            ajax: {
                url: '{{ route('visit_report_customer') }}',
                dataType: 'json',
                data: function(params) {
                    return {
                        search: params.term
                    }
                },
                processResults: function(data) {
                    return {
                        results: [{
                            'id': '',
                            'text': 'Semua Pelanggan'
                        }, ...data.datas]
                    };
                }
            }
        })
    </script>
@endsection
