@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <style>
        th {
            text-align: center;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Kirim Ke Cabang
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('send_to_branch') }}">Kirim Ke Cabang</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Kirim Ke Cabang <span class="text-muted"></span></h3>
                <a href="{{ route('send_to_branch-print-data', $data->id_pindah_barang) }}" target="_blank"
                    class="btn btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-print mr-1"></span> Cetak
                </a>
                <a href="{{ route('send_to_branch') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"
                    style="margin-right:10px;">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Cabang</label>
                            <div class="col-md-8">
                                : {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Gudang</label>
                            <div class="col-md-8">
                                : {{ $data->gudang->nama_gudang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Kode Pindah Cabang</label>
                            <div class="col-md-8">
                                : {{ $data->kode_pindah_barang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">
                                : {{ $data->tanggal_pindah_barang }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Cabang Tujuan</label>
                            <div class="col-md-8">
                                : {{ $data->destinationBranch->nama_cabang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Jasa Pengiriman</label>
                            <div class="col-md-8">
                                : {{ $data->transporter }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">No Polisi Kendaraan</label>
                            <div class="col-md-8">
                                : {{ $data->nomor_polisi }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Keterangan</label>
                            <div class="col-md-8">
                                : {{ $data->keterangan_pindah_barang }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-body">
                <h4>Detil Barang</h4>
                <div class="table-responsive">
                    <table id="table-detail" class="table table-bordered data-table display responsive nowrap"
                        width="100%">
                        <thead>
                            <tr>
                                <th>QR Code</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah</th>
                                <th>Batch</th>
                                <th>Kadaluarsa</th>
                                <th>SG</th>
                                <th>BE</th>
                                <th>PH</th>
                                <th>Bentuk</th>
                                <th>Warna</th>
                                <th>Keterangan</th>
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
            data: details,
            ordering: false,
            columns: [{
                    data: 'qr_code',
                    name: 'qr_code'
                }, {
                    data: 'nama_barang',
                    name: 'nama_barang'
                }, {
                    data: 'nama_satuan_barang',
                    name: 'nama_satuan_barang'
                }, {
                    data: 'qty',
                    name: 'qty',
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
                }, {
                    data: 'batch',
                    name: 'batch',
                    className: 'text-right'
                }, {
                    data: 'tanggal_kadaluarsa',
                    name: 'tanggal_kadaluarsa',
                }, {
                    data: 'sg',
                    name: 'sg',
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
                }, {
                    data: 'be',
                    name: 'be',
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
                }, {
                    data: 'ph',
                    name: 'ph',
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
                }, {
                    data: 'bentuk',
                    name: 'bentuk',
                },
                {
                    data: 'warna',
                    name: 'warna',
                }, {
                    data: 'keterangan',
                    name: 'keterangan',
                }
            ]
        });
    </script>
@endsection
