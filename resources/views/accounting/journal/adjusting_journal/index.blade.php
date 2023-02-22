@extends('layouts.main')
@section('addedStyles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    table{
        width: 100% !important;
    }

    .dataTables_scrollHeadInner{
        width: 100% !important;
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

    .mb-1 { margin-bottom:.25rem!important; }
</style>
@endsection
@section('header')
<section class="content-header">
    <h1>
        Transaksi Jurnal Penyesuaian
        <small></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Transaksi Jurnal Penyesuaian</li>
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
                            <a href="{{ route('transaction-general-ledger-create') }}" class="btn btn-sm btn-success btn-flat pull-right"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Tambah Jurnal Umum</a>
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
                    <table id="table_general_ledger" class="table table-bordered table-striped" style="width:100%">
                        <thead width="100%">
                            <tr>
                                <th class="text-center" width="25%">Kode Jurnal</th>
                                <th class="text-center" width="20%">Tanggal Jurnal</th>
                                <th class="text-center" width="20%">Jenis Jurnal</th>
                                <th class="text-center" width="20%">Kode Slip</th>
                                <th class="text-center" width="25%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
    $(function() {
        populate_table()

        $("#cabang_table").on("change", function() {
            populate_table()
        })
    })

    function populate_table() {
        let get_data_url = "{{ route('transaction-general-ledger-populate') }}"
        get_data_url += '?cabang=' + $("#cabang_table").val()
        $('#table_general_ledger').DataTable({
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
            columns: [
                {
                    data: 'kode_jurnal',
                    name: 'kode_jurnal',
                    width: '25%'
                },
                {
                    data: 'tanggal_jurnal',
                    name: 'tanggal_jurnal',
                    width: '20%'
                },
                {
                    data: 'jenis_name',
                    name: 'jenis_name',
                    width: '20%'
                },
                {
                    data: 'kode_slip',
                    name: 'kode_slip',
                    width: '20%'
                },
                {
                    data: 'id_jurnal',
                    width: '25%',
                    'sClass': 'text-center',
                    render: function(data, row) {
                        return getActions(data, row);
                    },
                    orderable: false
                }
            ],
            responsive: {
                details: {
                    type: 'column'
                }
            },
            columnDefs: [ {
                className: 'control',
                orderable: false,
                targets:   0
            } ],
        })
    }

    window.getActions = function(data, row) {
        let base_url = "{{ url('') }}"
        var action_btn = '<ul id="horizontal-list"><li><a href="' + base_url + '/transaction/general_ledger/show/' + data + '" class="btn btn-xs mr-1 mb-1 btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Detail</a></li><li><a href="' + base_url + '/transaction/general_ledger/form/edit/' + data + '" class="btn btn-xs mr-1 mb-1 btn-warning"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Ubah</a></li><li><button type="button" id="delete-btn" data-ids="' + data + '" onclick="delete_slip(' + data + ')" class="btn btn-xs mr-1 mb-1 btn-danger delete-btn"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Hapus</button</li></ul>';
        return action_btn;
    }

</script>
@endsection
