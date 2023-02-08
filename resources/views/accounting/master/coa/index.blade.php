@extends('layouts.main')
@section('addedStyles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <!-- Treetable -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/jquery-treetable/css/jquery.treetable.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/jquery-treetable/css/jquery.treetable.theme.default.css') }}">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #table_master_akun th{
            text-align: center !important;
            font-size: 1.5rem !important;
            border-color: white !important;
            padding: 0.6rem 0.4rem;
        }

        #table_master_akun td{
            font-size: 1.3rem !important;
            padding: 1rem !important;
        }

        #table_master_akun td.btn-column{
            text-align: center !important;
        }

        #table_master_akun td.btn-column .btn-sm{
            margin: 5px;
        }

        #table_master_akun td.btn-column span{
            padding: 2px !important;
        }
    </style>
@endsection
@section('header')
<section class="content-header">
    <h1>
        Master Chart of Account
        <small></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Master CoA</li>
    </ol>
</section>
@endsection

@section('main-section')
<section class="content container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cabang</label>
                                <select name="cabang_table" id="cabang_table" class="form-control select2" style="width: 100%;">
                                    @foreach ($data_cabang as $cabang)
                                        <option value="{{ $cabang->id_cabang }}" {{ isset($data_slip->id_cabang)?(($data_slip->id_cabang == $cabang->id_cabang)?'selected':''):'' }}>{{ $cabang->kode_cabang.' - '.$cabang->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <a href="{{ route('master-coa-create') }}" class="btn btn-sm btn-success btn-flat pull-right"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Tambah CoA</a>
                            <button id="btn-copy" type="button" class="btn btn-sm btn-info btn-flat pull-right mr-1"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span> Copy Data</button>
                            <a href="{{ route('master-coa-export-excel') }}" target="__blank" class="btn btn-sm btn-info btn-flat pull-right mr-1"><span class="glyphicon glyphicon-export" aria-hidden="true"></span> Export Excel</a>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <table id="table_master_akun" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="bg-primary" width="35%">Nama Akun</th>
                                <th class="bg-primary" width="15%">Kode Akun</th>
                                <th class="bg-primary" width="5%">Level</th>
                                <th class="bg-primary" width="10%">Header1</th>
                                <th class="bg-primary" width="10%">Header2</th>
                                <th class="bg-primary" width="10%">Header3</th>
                                <th class="bg-primary" width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data_akun_level1 as $akun1)
                                <tr data-tt-id="{{$akun1->id_akun}}">
                                    <td>{{ $akun1->nama_akun }}</td>
                                    <td>{{ $akun1->kode_akun }}</td>
                                    <td>1</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="btn-column">
                                        <a href="{{ route('master-coa-show', $akun1->id_akun) }}" class="btn-sm btn-default">
                                            <span class="glyphicon glyphicon-search" aria-hidden="true">
                                        </a>
                                        <a href="{{ route('master-coa-edit', $akun1->id_akun) }}" class="btn-sm btn-warning">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true">
                                        </a>
                                        <a href="{{ route('master-coa-destroy', $akun1->id_akun) }}" class="btn-sm btn-danger">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true">
                                        </a>
                                    </td>
                                </tr>
                                @foreach ($data_akun_level2 as $akun2)
                                    @if ($akun2->id_parent == $akun1->id_akun)
                                        <tr data-tt-id="{{$akun2->id_akun}}" data-tt-parent-id="{{$akun1->id_akun}}">
                                            <td>{{ $akun2->nama_akun }}</td>
                                            <td>{{ $akun2->kode_akun }}</td>
                                            <td>2</td>
                                            <td>{{ $akun2->header1 }}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td class="btn-column">
                                                <a href="{{ route('master-coa-show', $akun2->id_akun) }}" class="btn-sm btn-default">
                                                    <span class="glyphicon glyphicon-search" aria-hidden="true">
                                                </a>
                                                <a href="{{ route('master-coa-edit', $akun2->id_akun) }}" class="btn-sm btn-warning">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true">
                                                </a>
                                                <a href="{{ route('master-coa-destroy', $akun2->id_akun) }}" class="btn-sm btn-danger">
                                                    <span class="glyphicon glyphicon-trash" aria-hidden="true">
                                                </a>
                                            </td>
                                        </tr>

                                        @foreach ($data_akun_level3 as $akun3)
                                            @if ($akun3->id_parent == $akun2->id_akun)
                                                <tr data-tt-id="{{$akun3->id_akun}}" data-tt-parent-id="{{$akun2->id_akun}}">
                                                    <td>{{ $akun3->nama_akun }}</td>
                                                    <td>{{ $akun3->kode_akun }}</td>
                                                    <td>3</td>
                                                    <td>{{ $akun3->header1 }}</td>
                                                    <td>{{ $akun3->header2 }}</td>
                                                    <td>-</td>
                                                    <td class="btn-column">
                                                        <a href="{{ route('master-coa-show', $akun3->id_akun) }}" class="btn-sm btn-default">
                                                            <span class="glyphicon glyphicon-search" aria-hidden="true">
                                                        </a>
                                                        <a href="{{ route('master-coa-edit', $akun3->id_akun) }}" class="btn-sm btn-warning">
                                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true">
                                                        </a>
                                                        <a href="{{ route('master-coa-destroy', $akun3->id_akun) }}" class="btn-sm btn-danger">
                                                            <span class="glyphicon glyphicon-trash" aria-hidden="true">
                                                        </a>
                                                    </td>
                                                </tr>

                                                @foreach ($data_akun_level4 as $akun4)
                                                    @if ($akun4->id_parent == $akun3->id_akun)
                                                        <tr data-tt-id="{{$akun4->id_akun}}" data-tt-parent-id="{{$akun3->id_akun}}">
                                                            <td>{{ $akun4->nama_akun }}</td>
                                                            <td>{{ $akun4->kode_akun }}</td>
                                                            <td>4</td>
                                                            <td>{{ $akun4->header1 }}</td>
                                                            <td>{{ $akun4->header2 }}</td>
                                                            <td>{{ $akun4->header3 }}</td>
                                                            <td class="btn-column">
                                                                <a href="{{ route('master-coa-show', $akun4->id_akun) }}" class="btn-sm btn-default">
                                                                    <span class="glyphicon glyphicon-search" aria-hidden="true">
                                                                </a>
                                                                <a href="{{ route('master-coa-edit', $akun4->id_akun) }}" class="btn-sm btn-warning">
                                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true">
                                                                </a>
                                                                <a href="{{ route('master-coa-destroy', $akun4->id_akun) }}" class="btn-sm btn-danger">
                                                                    <span class="glyphicon glyphicon-trash" aria-hidden="true">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
              </div>
        </div>
    </div>
</section>
@endsection

@section('modal-section')
<div class="modal fade" id="modal-copy">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Copy Data Akun</h4>
            </div>
            <div class="modal-body">
                <form id="form-copy" action="" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group">
                                <label>Dari Cabang</label>
                                {{ csrf_field() }}
                                <input type="hidden" id="id_cabang" name="id_cabang" value="{{ $cabang_user->id_cabang }}">
                                <input type="text" class="form-control" id="nama_cabang" value="{{ $cabang_user->kode_cabang.' - '.$cabang_user->nama_cabang }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Ke Cabang</label>
                                <select name="cabang" class="form-control select2" style="width: 100%;">
                                    @foreach ($data_cabang as $cabang)
                                        <option value="{{ $cabang->id_cabang }}" {{ isset($akun->id_cabang)?(($akun->id_cabang == $cabang->id_cabang)?'selected':''):'' }}>{{ $cabang->kode_cabang.' - '.$cabang->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="button" id="btn-copy-data" class="btn btn-primary">Copy Data Akun</button>
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
    <!-- TreeTable -->
    <script src="{{ asset('assets/bower_components/jquery-treetable/jquery.treetable.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $(function () {
            // $('#example1').DataTable()
            $('#table_master_akun').treetable({expandable: true});

            $("#btn-copy").on("click", function() {
                $("#modal-copy").modal("show")
            })

            $("#btn-copy-data").on("click", function() {
                $(this).html('<i class="fa fa-spinner fa-spin"></i>')
                $.ajax({
                    url: "{{ route('master-coa-copy-data') }}",
                    type: "POST",
                    data: $("#form-copy").serialize(),
                    dataType: "JSON",
                    success: function(data) {
                        console.log(data)
                        if (data.result) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'Close'
                            })
                        }
                        else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'Close'
                            })
                        }
                    }
                })
                $(this).html('Copy Data Slip')
            })
        })
    </script>
@endsection
