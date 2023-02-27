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
            Permintaan Pembelian
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Permintaan Pembelian</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Permintaan Pembelian <span class="text-muted"></span></h3>
                <a href="{{ route('purchase-request') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
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
                            <label class="col-md-4">Kode Permintaan</label>
                            <div class="col-md-8">
                                : {{ $data->purchase_request_code }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">
                                : {{ $data->purchase_request_date }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Estimasi</label>
                            <div class="col-md-8">
                                : {{ $data->purchase_request_estimation_date }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Status</label>
                            <div class="col-md-8">
                                : <label class="{{ $status[$data->approval_status]['class'] }}">
                                    {{ $status[$data->approval_status]['text'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Gudang</label>
                            <div class="col-md-8">
                                : {{ $data->gudang->nama_gudang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Pemohon</label>
                            <div class="col-md-8">
                                : {{ $data->pengguna->nama_pengguna }}
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
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="200">Kode Barang</th>
                                <th width="200">Nama Barang</th>
                                <th width="100">Satuan</th>
                                <th width="150">Jumlah</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data->formatdetail as $detail)
                                <tr>
                                    <td>{{ $detail->kode_barang }}</td>
                                    <td>{{ $detail->nama_barang }}</td>
                                    <td class="text-center">{{ $detail->nama_satuan_barang }}</td>
                                    <td class="text-right">{{ $detail->qty }}</td>
                                    <td>{{ $detail->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
