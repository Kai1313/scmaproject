@extends('layouts.main')
@section('addedStyles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endsection
@section('header')
    <section class="content-header">
        <h1>
            Transaksi Jurnal Umum
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('transaction-general-ledger') }}">Transaksi Jurnal Umum</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Jurnal Umum <span class="text-muted"></span></h3>
                        <a href="{{ route('transaction-general-ledger') }}"
                            class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                                class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali</a>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Kode Jurnal</label>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="nomarg">{{ $data_jurnal_header->kode_jurnal }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Tanggal Jurnal</label>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="nomarg">
                                            {{ date('d-m-Y', strtotime($data_jurnal_header->tanggal_jurnal)) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Jenis Jurnal</label>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="nomarg">{{ $data_jurnal_header->jenis_name }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Cabang</label>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="nomarg">{{ $data_jurnal_header->kode_cabang }} -
                                            {{ $data_jurnal_header->nama_cabang }}
                                        </p>
                                    </div>
                                </div>
                                @if (
                                    ($data_jurnal_header->no_giro != null && $data_jurnal_header->jenis_jurnal == 'PG') ||
                                        ($data_jurnal_header->no_giro != null && $data_jurnal_header->jenis_jurnal == 'HG'))
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Slip</label>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="nomarg">{{ $data_jurnal_header->kode_slip }} -
                                                {{ $data_jurnal_header->nama_slip }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Notes</label>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="nomarg">{{ $data_jurnal_header->catatan }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if (
                                    ($data_jurnal_header->no_giro == null && $data_jurnal_header->jenis_jurnal != 'PG') ||
                                        ($data_jurnal_header->no_giro == null && $data_jurnal_header->jenis_jurnal != 'HG'))
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Slip</label>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="nomarg">{{ $data_jurnal_header->kode_slip }} -
                                                {{ $data_jurnal_header->nama_slip }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Notes</label>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="nomarg">{{ $data_jurnal_header->catatan }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if (
                                    ($data_jurnal_header->no_giro != null && $data_jurnal_header->jenis_jurnal == 'PG') ||
                                        ($data_jurnal_header->no_giro != null && $data_jurnal_header->jenis_jurnal == 'HG'))
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Nomor Giro</label>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="nomarg">{{ $data_jurnal_header->no_giro }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Status Giro</label>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="nomarg">
                                                @switch($data_jurnal_header->status_giro)
                                                    @case(0)
                                                        Belum Cair
                                                    @break

                                                    @case(1)
                                                        Cair
                                                    @break

                                                    @case(2)
                                                        Tolak
                                                    @break

                                                    @default
                                                        -
                                                @endswitch
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Tanggal Giro</label>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="nomarg">
                                                {{ date('d-m-Y', strtotime($data_jurnal_header->tanggal_giro)) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Tanggal Jatuh Tempo Giro</label>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="nomarg">
                                                {{ date('d-m-Y', strtotime($data_jurnal_header->tanggal_giro_jt)) }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Detail</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table id="detail_table" class="table table-bordered table-striped" width="100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center" width="10%">No. Akun</th>
                                            <th class="text-center" width="20%">Nama Akun</th>
                                            <th class="text-center" width="30%">Catatan</th>
                                            <th class="text-center" width="20%">Debet</th>
                                            <th class="text-center" width="20%">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data_jurnal_detail as $detail)
                                            <tr>
                                                <td>{{ $detail->kode_akun }}</td>
                                                <td>{{ $detail->nama_akun }}</td>
                                                <td>{{ $detail->keterangan }}</td>
                                                <td style="text-align: right">{{ number_format($detail->debet, 2) }}</td>
                                                <td style="text-align: right">{{ number_format($detail->credit, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <!-- DataTables -->
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('assets/bower_components/fastclick/lib/fastclick.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $(function() {
            $('#detail_table').dataTable({
                "scrollX": true
            })
        })
    </script>
@endsection