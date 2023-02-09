@extends('layouts.main')
@section('addedStyles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .m-2 {
        margin: 0.5rem;
    }

    ul#horizontal-list {
        min-width: 200px;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    ul#horizontal-list li {
        display: inline;
    }
</style>
@endsection
@section('header')
<section class="content-header">
    <h1>
        Master Slip
        <small></small>
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
                            <a href="{{ route('master-slip-create') }}" class="btn btn-sm btn-success btn-flat pull-right"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Tambah Slip</a>
                            {{-- <button id="btn-copy" type="button" class="btn btn-sm btn-info btn-flat pull-right mr-1"><span
                                        class="glyphicon glyphicon-copy" aria-hidden="true"></span> Copy Data</button> --}}
                            <a href="{{ route('master-slip-export-excel') }}" target="__blank" class="btn btn-sm btn-info btn-flat pull-right mr-1"><span class="glyphicon glyphicon-export" aria-hidden="true"></span> Export Excel</a>
                        </div>
                    </div>
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
                    <table id="table_slip" class="table table-bordered table-striped" style="width:100%">
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

@section('modal-section')
<div class="modal fade" id="modal-copy">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Copy Slip Data</h4>
            </div>
            <div class="modal-body">
                <form id="form-copy" action="" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group">
                                <label>Dari Cabang</label>
                                {{ csrf_field() }}
                                <input type="hidden" id="id_cabang" name="id_cabang" value="{{ $cabang->id_cabang }}">
                                <input type="text" class="form-control" id="nama_cabang" value="{{ $cabang->kode_cabang.' - '.$cabang->nama_cabang }}" readonly>
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
                <button type="button" id="btn-copy-data" class="btn btn-primary">Copy Data Slip</button>
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
        populate_table()

        $("#btn-copy").on("click", function() {
            $("#modal-copy").modal("show")
        })

        $("#btn-copy-data").on("click", function() {
            $(this).html('<i class="fa fa-spinner fa-spin"></i>')
            $.ajax({
                url: "{{ route('master-slip-copy-data') }}",
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
                    } else {
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

        $("#cabang_table").on("change", function() {
            populate_table()
        })

        $("#table-slip").on("click", ".delete-btn", function() {
            let ids = $(this).data("ids")
            console.log(ids)
            delete_slip(ids)
        })
    })

    function populate_table() {
        let get_data_url = "{{ route('master-slip-populate') }}"
        get_data_url += '?cabang=' + $("#cabang_table").val()
        $('#table_slip').DataTable({
            processing: true,
            serverSide: true,
            "scrollX": true,
            "bDestroy": true,
            ajax: {
                'url': get_data_url,
                'type': 'GET',
                'dataType': 'JSON',
                'error': function(xhr, textStatus, ThrownException) {
                    alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                }
            },
            columns: [{
                    data: 'kode_slip',
                    name: 'kode_slip',
                    width: '15%'
                },
                {
                    data: 'nama_slip',
                    name: 'nama_slip',
                    width: '30%'
                },
                {
                    data: 'jenis_name',
                    name: 'jenis_name',
                    width: '10%'
                },
                {
                    data: 'nama_akun',
                    name: 'nama_akun',
                    width: '30%'
                },
                {
                    data: 'id_slip',
                    width: '5%',
                    'sClass': 'text-center',
                    render: function(data, row) {
                        return getActions(data, row);
                    },
                    orderable: false
                }
            ]
        })
    }

    window.getActions = function(data, row) {
        let base_url = "{{ url('') }}"
        var action_btn = '<ul id="horizontal-list"><li><a href="' + base_url + '/master/slip/show/' + data + '" class="btn btn-flat btn-xs m-2 btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Detail</a></li><li><a href="' + base_url + '/master/slip/form/edit/' + data + '" class="btn btn-flat btn-xs m-2 btn-warning"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Ubah</a></li><li><button type="button" id="delete-btn" data-ids="' + data + '" onclick="delete_slip(' + data + ')" class="btn btn-flat btn-xs m-2 btn-danger delete-btn"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Hapus</button</li></ul>';
        return action_btn;
    }

    function delete_slip(id) {
        let url = "{{ route('master-slip-destroy', ": id ") }}"
        url = url.replace(':id', id)
        Swal.fire({
            title: 'Anda yakin ingin menghapus data ini?',
            icon: 'info',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No',
            reverseButtons: true,
            customClass: {
                actions: 'my-actions',
                confirmButton: 'order-1',
                denyButton: 'order-3',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function(data) {
                        if (data.result) {
                            Swal.fire('Saved!', data.message, 'success').then((result) => {
                                if (result.isConfirmed) {
                                    populate_table()
                                }
                            })
                        } else {
                            Swal.fire("Sorry, Can't delete data. ", data.message, 'error')
                        }

                    },
                    error: function(data) {
                        Swal.fire("Sorry, Can't delete data. ", data.message, 'error')
                    }
                });
            } else if (result.isDenied) {
                Swal.fire('Batal menghapus data', '', 'info')
            }
        })
    }
</script>
@endsection