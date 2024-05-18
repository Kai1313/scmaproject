@extends('layouts.main')

@section('addedStyles')
    <style>
        th {
            text-align: center;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Uang Muka Penjualan
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('sales-down-payment') }}">Uang Muka Penjualan</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Uang Muka Penjualan <span class="text-muted"></span></h3>
                <a href="{{ route('sales-down-payment') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
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
                            <label class="col-md-4">Kode Uang Muka Penjualan</label>
                            <div class="col-md-8">
                                : {{ $data->kode_uang_muka_penjualan }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">
                                : {{ $data->tanggal }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">ID Permintaan Penjualan (SO)</label>
                            <div class="col-md-8">
                                : {{ $data->salesOrder->nama_permintaan_penjualan }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Pelanggan</label>
                            <div class="col-md-8">
                                : {{ $data->salesOrder->customer->nama_pelanggan }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Rate</label>
                            <div class="col-md-8">
                                : {{ number_format($data->rate, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Jenis PPN</label>
                            <div class="col-md-8">
                                : @if ($data->ppn_uang_muka_penjualan == '0')
                                    <label class="label label-danger">Tanpa PPN</label>
                                @elseif($data->ppn_uang_muka_penjualan == '1')
                                    <label class="label label-warning">Include</label>
                                @elseif($data->ppn_uang_muka_penjualan == '2')
                                    <label class="label label-success">Exclude</label>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Nominal</label>
                            <div class="col-md-8">
                                : {{ number_format($data->nominal, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">DPP</label>
                            <div class="col-md-8">
                                : {{ number_format($data->dpp, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">PPN</label>
                            <div class="col-md-8">
                                : {{ number_format($data->ppn, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Total</label>
                            <div class="col-md-8">
                                : {{ number_format($data->total * $data->rate, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Catatan</label>
                            <div class="col-md-8">
                                : {{ $data->catatan }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
