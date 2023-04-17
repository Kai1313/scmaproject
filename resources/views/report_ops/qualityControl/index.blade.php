@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
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
                                <label>Tanggal Awal</label>
                                <div class="form-group">
                                    <input type="date" name="start_date" class="form-control trigger-change"
                                        value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Tanggal Akhir</label>
                                <div class="form-group">
                                    <input type="date" name="end_date" class="form-control trigger-change"
                                        value="{{ date('Y-m-d') }}">
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
                                <th>Tanggal</th>
                                <th>Kode Pindah Gudang</th>
                                <th>Gudang</th>
                                <th>Cabang Tujuan</th>
                                <th>Keterangan</th>
                                <th>Jasa Pengiriman</th>
                                <th>Status</th>
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
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let filterBranch = $('[name="id_cabang"]').val()
        let filterStartDate = $('[name="start_date"]').val()
        let filterEndDate = $('[name="end_date"]').val()
        let defaultUrlIndex = '{{ route('report_qc-index') }}'
        let defaultUrlPrint = $('.btn-action').prop('href')

        let param = '?id_cabang=' + filterBranch + '&start_date=' + filterStartDate + '&end_date=' + filterEndDate

        $('.select2').select2()
        $('.btn-action').prop('href', defaultUrlPrint + param)
        $(document).ready(function() {
            getData()
        })

        function getData() {
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
