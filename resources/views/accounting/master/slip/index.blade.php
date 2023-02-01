@extends('layouts.main')
@section('addedStyles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endsection
@section('header')
    <section class="content-header">
        <h1>
            Master Slip
            <small>Slip</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Master Slip</li>
        </ol>
    </section>
@endsection
@section('main-section')
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route('master-slip-create') }}" class="btn btn-sm btn-success btn-flat pull-right"><span
                        class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add Slip</a>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Slip List</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="table_slip" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Kode Slip</th>
                                    <th class="text-center">Nama Slip</th>
                                    <th class="text-center">Jenis Slip</th>
                                    <th class="text-center">Akun COA</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>KAS-SBY xxx-xxx-xxx</td>
                                    <td>KAS-SBY (AC: xxx-xxx-xxx)</td>
                                    <td class="text-center">Kas</td>
                                    <td>KAS-SBY (AC: xxx-xxx-xxx)</td>
                                    <td class="text-center">
                                        <a href="{{ route('master-slip-show') }}" class="btn btn-default">
                                            <span class="glyphicon glyphicon-search" aria-hidden="true">
                                        </a>
                                        <a href="{{ route('master-slip-edit') }}" class="btn btn-warning">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true">
                                        </a>
                                        <a href="{{ route('master-slip-destroy') }}" class="btn btn-danger">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true">
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>BCA-IDR xxx-xxx-xxx</td>
                                    <td>BCA-IDR (AC: xxx-xxx-xxx)</td>
                                    <td class="text-center">Bank</td>
                                    <td>BCA-IDR (AC: xxx-xxx-xxx)</td>
                                    <td class="text-center">
                                        <a href="{{ route('master-slip-show') }}" class="btn btn-default">
                                            <span class="glyphicon glyphicon-search" aria-hidden="true">
                                        </a>
                                        <a href="{{ route('master-slip-edit') }}" class="btn btn-warning">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true">
                                        </a>
                                        <a href="{{ route('master-slip-destroy') }}" class="btn btn-danger">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true">
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.box-body -->
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
            $('#table_slip').DataTable()
        })
    </script>
@endsection
