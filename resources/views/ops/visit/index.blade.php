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
                    <div class="col-md-12">
                        <div class="form-group">
                            <button class="btn" onclick="trigger_filter()"><i class="fa fa-filter"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 filter-div">
                        <label>Cabang</label>
                        <div class="form-group">
                            <select id="id_cabang" class="form-control select2">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Sales</label>
                        <div class="form-group">
                            <select id="id_salesman" class="form-control select2">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Status</label>
                        <div class="form-group">
                            <select id="status" class="form-control select2">
                                <option value="">Semua Status</option>
                                <option value="1">Belum Visit</option>
                                <option value="2">Sudah Visit</option>
                                <option value="0">Batal Visit</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Progress Indicator</label>
                        <div class="form-group">
                            <select id="progress_ind" class="form-control select2">
                                <option value="">Semua Status</option>
                                <option value="0">Belum Report</option>
                                @foreach (App\Visit::$progressIndicator as $i => $item)
                                    <option value="{{ $i }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Range Tanggal</label>
                        <div class="form-group">
                            <input type="text" id="daterangepicker" class="form-control"
                                value="{{ startOfMonth('d/m/Y') }} - {{ endOfMonth('d/m/Y') }}" />
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
                                        <th>Action</th>
                                        <th>Kode Jadwal</th>
                                        <th>Tanggal</th>
                                        <th>Salesman</th>
                                        <th>Pelanggan</th>
                                        <th>Status</th>
                                        <th>Status Report</th>
                                        <th>Detail</th>
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

    <div class="modal fade" id="modal-gambar" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="">Kode Visit <h5 class="visit_code"></h5></label>
                        </div>
                        <div class="col-md-6">
                            <img id="gambar1" style="width: 100%"
                                onerror="this.src='https://perpus.umri.ac.id/ckfinder/userfiles/images/no-image.png'"
                                alt="">
                        </div>
                        <div class="col-md-6">
                            <img id="gambar2" style="width: 100%"
                                onerror="this.src='https://perpus.umri.ac.id/ckfinder/userfiles/images/no-image.png'"
                                alt="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel-entry btn-flat"
                        onclick="$('#modal-gambar').modal('toggle')">Batal</button>
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
    <script src="{{ asset('js/filter-button.js') }}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endsection

@section('externalScripts')
    <script>
        $('.select2').select2({
            width: '100%'
        });

        $('#daterangepicker').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY'
            }
        });

        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('visit') }}",
                data: {
                    id_cabang: function() {
                        return $('#id_cabang').val();
                    },
                    id_salesman: function() {
                        return $('#id_salesman').val();
                    },
                    status: function() {
                        return $('#status').val();
                    },
                    progress_ind: function() {
                        return $('#progress_ind').val();
                    },
                    daterangepicker: function() {
                        return $('#daterangepicker').val();
                    },

                },
            },
            columns: [{
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false
            }, {
                data: 'visit_code',
                name: 'visit_code'
            }, {
                data: 'visit_date',
                name: 'visit_date'
            }, {
                data: 'nama_salesman',
                name: 'nama_salesman',
            }, {
                data: 'nama_pelanggan',
                name: 'nama_pelanggan',
            }, {
                data: 'status',
                name: 'status',
                class: 'text-center',
                orderable: false,
                searchable: false
            }, {
                data: 'status_report',
                name: 'status_report',
                class: 'text-center',
                orderable: false,
                searchable: false
            }, {
                data: 'detail',
                name: 'detail',
            }, ]
        });

        $("#id_salesman").select2({
            width: '100%',
            allowClear: true,
            ajax: {
                url: "{{ route('visit.reporting.select') }}?param=id_salesman",
                dataType: 'json',
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                },
                cache: true,
                type: 'GET',
            },
            placeholder: 'Semua Salesman',
            minimumInputLength: 0,
            templateResult: formatRepoNormal,
            templateSelection: formatRepoNormalSelection
        });

        function openModalBukti(kode, gambar1, gambar2) {
            $('#gambar1').prop('src', gambar1);
            $('#gambar2').prop('src', gambar2);
            $('.visit_code').html(kode);

            $('#modal-gambar').modal('toggle');
        }
    </script>
@endsection
