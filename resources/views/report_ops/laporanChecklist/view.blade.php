@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
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
            Laporan Checklist Pekerjaan
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('report_checklist') }}">Laporan Checklist Pekerjaan</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
               Lokasi
            </div>
            <div class="box-body" style="padding:0px;">
               <table class="table table-bordered">
                <tr>
                    <td style="width:100px;font-weight:bold;">Tanggal</td>
                    <td style="width:10px;">:</td>
                    <td>{{$data->tanggal_jawaban_checklist_pekerjaan}}</td>
                </tr>
                <tr>
                    <td style="width:100px;font-weight:bold;">Grup</td>
                    <td>:</td>
                    <td>{{$data->nama_grup_pengguna}}</td>
                </tr>
                <tr>
                    <td style="width:100px;font-weight:bold;">Karyawan</td>
                    <td>:</td>
                    <td>{{$data->nama_pengguna}}</td>
                </tr>
               </table>
               <table class="table table-bordered" style="margin-top:20px;">
                <tr>
                    <td colspan="4" style="font-weight:bold;">HASIL CHECKLIST PEKERJAAN</td>
                </tr>
                @for($i = 1;$i <= 25;$i++)
                <tr>
                    <td style="width:30px;">{{$i}}.</td>
                    <td></td>
                    <td style="width:10px;">:</td>
                    <td style="width:20px;"></td>
                </tr>
                @endfor
               </table>
            </div>
        </div>
    </div>
@endsection

