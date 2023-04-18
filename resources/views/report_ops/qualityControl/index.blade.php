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
            Laporan QC Penerimaan Barang
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan QC Penerimaan Barang</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Cabang</label>
                                <div class="form-group">
                                    <select name="id_cabang" class="form-control select2 trigger-change">
                                        @foreach (getCabangForReport() as $branch)
                                            <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Tanggal</label>
                                <div class="form-group">
                                    <input type="text" name="date" class="form-control trigger-change">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('report_qc-print') }}"
                            class="btn btn-primary btn-sm btn-flat pull-right btn-action" style="margin-top:26px;"
                            target="_blank">
                            <i class="glyphicon glyphicon-print"></i> Print
                        </a>
                    </div>
                </div>

            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Pembelian</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Status</th>
                                <th>Alasan</th>
                                <th>Tanggal QC</th>
                                <th>SG</th>
                                <th>BE</th>
                                <th>PH</th>
                                <th>Warna</th>
                                <th>Bentuk</th>
                                <th>Keterangan</th>
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
        let defaultUrlIndex = '{{ route('report_qc-index') }}'
        let defaultUrlPrint = $('.btn-action').prop('href')
        let countDown = '{{ $countDown }}'
        console.log(countDown)
        let param = ''

        $('.select2').select2()
        $('[name="date"]').daterangepicker({
            timePicker: true,
            startDate: moment().subtract(countDown, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY/M/DD'
            }
        });
        $('.btn-action').prop('href', defaultUrlPrint + param)
        $(document).ready(function() {
            getData()
        })

        function getParam() {
            param = '?id_cabang=' + $('[name="id_cabang"]').val() + '&date=' + $('[name="date"]').val()
        }

        function getData() {
            getParam()

            $.ajax({
                url: defaultUrlIndex + param,
                success: function(res) {
                    console.log(res)
                },
                error: function(error) {
                    console.log(error)
                }
            })
        }

        $('.trigger-change').change(function() {
            getData()
        })
    </script>
@endsection
