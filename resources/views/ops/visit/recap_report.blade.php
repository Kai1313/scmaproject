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
            Laporan Rekap Kunjungan
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Rekap Kunjungan</li>
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
                        <label class="d-block">&nbsp;</label>
                        <div class="form-group">
                            <button type="button" class="btn btn-info btn-search"><i class="fa fa-search mr-1"></i>
                                Cari</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body" id="target-body">

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

        $('.btn-search').click(function(e) {
            e.preventDefault()
            $('#cover-spin').show()
            $.ajax({
                url: '',
                data: filterDatatable(),
                success: function(res) {
                    if (res.result) {
                        $('#target-body').html(res.html)
                    }

                    $('#cover-spin').hide()
                },
                error: function(error) {
                    $('#cover-spin').hide()
                    Swal.fire("Gagal Ambil Data. ", data.responseJSON.message, 'error')
                }
            })
        })
    </script>
@endsection
