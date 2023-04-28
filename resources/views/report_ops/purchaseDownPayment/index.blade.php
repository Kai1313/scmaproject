@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}" />
    <style>
        th {
            text-align: center;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Laporan Uang Muka Pembelian
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Uang Muka Pembelian</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Cabang</label>
                                <div class="form-group">
                                    <select name="id_cabang" class="form-control select2 trigger-change">
                                        @foreach (getCabangForReport() as $branch)
                                            <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>Tanggal</label>
                                <div class="form-group">
                                    <input type="text" name="date" class="form-control trigger-change">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('report_purchase_down_payment-print') }}"
                            class="btn btn-primary btn-sm btn-flat pull-right btn-action" style="margin-top:26px;"
                            target="_blank">
                            <i class="glyphicon glyphicon-print"></i> Print
                        </a>
                    </div>
                </div>

            </div>
        </div>
        <div class="box">
            <div class="box-body" id="target-html">

            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    {{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script> --}}
    <script type="text/javascript" src="{{ asset('assets/bower_components/moment/moment.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let defaultUrlIndex = '{{ route('report_purchase_down_payment-index') }}'
        let defaultUrlPrint = $('.btn-action').prop('href')
        let param = ''
        let gArray = [];

        $('.select2').select2()
        $('[name="date"]').daterangepicker({
            timePicker: false,
            startDate: moment().subtract(60, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        $('.btn-action').prop('href', defaultUrlPrint + param)
        $(document).ready(function() {
            getData()
        })

        function getParam() {
            param = ''
            $('.trigger-change').each(function(i, v) {
                param += (i == 0) ? '?' : '&'
                param += $(v).prop('name') + '=' + $(v).val()
            })

            $('.btn-action').prop('href', defaultUrlPrint + param)
        }

        function getData() {
            $('#cover-spin').show()
            setTimeout(() => {
                getParam()
                $.ajax({
                    url: defaultUrlIndex + param,
                    success: function(res) {
                        $('#target-html').html(res.html)
                        $('#cover-spin').hide()
                    },
                    error: function(error) {
                        let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON
                            .message : error
                            .statusText
                        Swal.fire("Gagal Mengambil Data. ", textError, 'error')
                        $('#cover-spin').hide()
                    }
                })
            }, 100);
        }

        $('.trigger-change').change(function() {
            getData()
        })
    </script>
@endsection
