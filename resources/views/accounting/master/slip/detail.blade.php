@extends('layouts.main')

@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Slip
            <small>Slip | Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-slip') }}">Master Slip</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route('master-slip') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                        class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Back</a>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Slip <span class="text-muted">KAS-SBY xxx-xxx-xxx</span></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Kode Slip</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">KAS-SBY xxx-xxx-xxx</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Nama Slip</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">KAS-SBY (AC: xxx-xxx-xxx)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Jenis Slip</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">Kas</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Akun</label>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="nomarg">KAS-SBY (AC: xxx-xxx-xxx)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $(function() {
            $('.select2').select2()
        })
    </script>
@endsection
