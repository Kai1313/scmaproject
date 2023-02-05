@extends('layouts.main')
@section('addedStyles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <style>
        .m-2{
            margin: 0.5rem;
        }
    </style>
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
                        class="glyphicon glyphicon-plus" aria-hidden="true"></span> Tambah Slip</a>
                <a href="#" class="btn btn-sm btn-info btn-flat pull-right mr-1"><span
                        class="glyphicon glyphicon-copy" aria-hidden="true"></span> Copy Data</a>
                <a href="#" class="btn btn-sm btn-info btn-flat pull-right mr-1"><span
                        class="glyphicon glyphicon-export" aria-hidden="true"></span> Export Excel</a>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Slip List</h3>
                    </div>
                    @if (session('failed'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('failed') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif
                    <div class="box-body">
                        <table width="100%" id="table_slip" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Kode Slip</th>
                                    <th class="text-center">Nama Slip</th>
                                    <th class="text-center">Jenis Slip</th>
                                    <th class="text-center">Akun COA</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
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
        let base_url = "{{ url('') }}"
        let get_data_url = "{{ route('master-slip-populate') }}"
        $(function() {
            $('#table_slip').DataTable({
                processing: true,
                serverSide: true,
                "scrollX": true,
                ajax:{
                    'url' :get_data_url,
                    'type' : 'GET',
                    'dataType' : 'JSON',
                    'error' : function(xhr, textStatus, ThrownException){
                        alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                    }
                },
                columns:[
                    {
                        data: 'kode_slip',
                        name: 'kode_slip',
                        width: '15%'
                    },
                    {
                        data: 'nama_slip',
                        name: 'nama_slip',
                        width: '20%'
                    },
                    {
                        data: 'jenis_name',
                        name: 'jenis_name',
                        width: '20%'
                    },
                    {
                        data: 'nama_akun',
                        name: 'nama_akun',
                        width: '30%'
                    },
                    {
                        data:'id_slip',
                        width: '15%',
                        'sClass': 'text-center',
                        render: function (data, row){
                            return getActions(data, row);
                        },
                        orderable: false
                    }
                ]
            });
        })

        window.getActions = function(data, row){
            var action_btn = '<a href="' + base_url + '/master/slip/show/' + data + '" class="btn-sm m-2 btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></a>' +
                            '<a href="' + base_url + '/master/slip/form/edit/' + data + '" class="btn-sm m-2 btn-warning"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></a>' +
                            '<a href="' + base_url + '/master/slip/destroy/' + data + '"  class="btn-sm m-2 btn-danger"><span class="glyphicon glyphicon-trash" aria-hidden="true"></a>';
            return action_btn;
        }
    </script>
@endsection
