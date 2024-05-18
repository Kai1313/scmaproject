@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <style>
        th {
            text-align: center;
        }

        tfoot>tr>td {
            font-weight: bold;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Pemakaian
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('material_usage') }}">Pemakaian</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Pemakaian <span class="text-muted"></span></h3>
                {{-- <a href="{{ route('send_to_branch-print-data', $data->id_pindah_barang) }}" target="_blank"
                    class="btn btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-print mr-1"></span> Cetak
                </a> --}}
                <a href="{{ route('material_usage') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"
                    style="margin-right:10px;">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Cabang Pemakaian</label>
                            <div class="col-md-8">
                                : {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Gudang Pemakaian</label>
                            <div class="col-md-8">
                                : {{ $data->gudang->nama_gudang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">
                                : {{ $data->tanggal }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Kode Pemakaian</label>
                            <div class="col-md-8">
                                : {{ $data->kode_pemakaian }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Catatan</label>
                            <div class="col-md-8">
                                : {{ $data->catatan }}
                            </div>
                        </div>
                        @if ($accessQc)
                            <div class="row">
                                @if ($data && $data->is_qc == 1)
                                    <label class="col-md-4">QC</label>
                                    <div class="col-md-8">
                                        : <label class="label label-info">Ya</label>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-body">
                <h4>Detil Barang</h4>
                <div class="table-responsive">
                    <table id="table-detail" class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Gross</th>
                                <th>Jumlah Zak</th>
                                <th>Tare</th>
                                <th>Nett</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
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
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        var resDataTable = $('#table-detail').DataTable({
            scrollX: true,
            paging: false,
            data: details,
            ordering: false,
            columns: [{
                data: 'kode_batang',
                name: 'kode_batang'
            }, {
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang'
            }, {
                data: 'jumlah',
                name: 'jumlah',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'jumlah_zak',
                name: 'jumlah_zak',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'tare',
                name: 'tare',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'nett',
                name: 'nett',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'catatan',
                name: 'catatan'
            }],
            initComplete: function(settings, json) {
                sumDetail()
            }
        });

        function sumDetail() {
            let totalJumlah = 0;
            let totalJumlahZak = 0;
            let totalTare = 0;
            let totalNett = 0;
            for (let i = 0; i < details.length; i++) {
                totalJumlah += parseFloat(details[i].jumlah)
                totalJumlahZak += parseFloat(details[i].jumlah_zak)
                totalTare += parseFloat(details[i].tare)
                totalNett += parseFloat(details[i].nett)
            }

            $('#table-detail tbody').after(
                '<tfoot><tr>' +
                '<td colspan="3" class="text-left"><b>Total</b></td>' +
                '<td class="text-right">' + formatNumberNew(totalJumlah, 4) + '</td>' +
                '<td class="text-right">' + formatNumberNew(totalJumlahZak, 4) + '</td>' +
                '<td class="text-right">' + formatNumberNew(totalTare, 4) + '</td>' +
                '<td class="text-right">' + formatNumberNew(totalNett, 4) + '</td>' +
                '<td></td>' +
                '</tr></tfoot>'
            );
        }
    </script>
@endsection
