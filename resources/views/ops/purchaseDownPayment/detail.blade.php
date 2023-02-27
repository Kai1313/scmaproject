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
            Uang Muka Pembelian
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-down-payment') }}">Uang Muka Pembelian</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Uang Muka Pembelian <span class="text-muted"></span></h3>
                <a href="{{ route('purchase-down-payment') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
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
                            <label class="col-md-4">Kode Uang Muka Pembelian</label>
                            <div class="col-md-8">
                                : {{ $data->kode_uang_muka_pembelian }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">
                                : {{ $data->tanggal }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">ID Permintaan Pembelian (PO)</label>
                            <div class="col-md-8">
                                : {{ $data->purchaseOrder->nama_permintaan_pembelian }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Slip</label>
                            <div class="col-md-8">
                                : {{ $data->slip->kode_slip }} - {{ $data->slip->nama_slip }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Rate</label>
                            <div class="col-md-8">
                                : {{ $data->rate }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Nominal</label>
                            <div class="col-md-8">
                                : {{ $data->nominal }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Total</label>
                            <div class="col-md-8">
                                : {{ $data->total }}
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
