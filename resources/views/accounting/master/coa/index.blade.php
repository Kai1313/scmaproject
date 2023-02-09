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

        .dropdown-menu>li>a.text-danger{
            color: #843534 !important;
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
                    <div class="table-responsive">
                        <table id="table_master_akun" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="bg-primary" width="20%">Kode Akun</th>
                                    <th class="bg-primary" width="25%">Nama Akun</th>
                                    <th class="bg-primary" width="5%">Level</th>
                                    <th class="bg-primary" width="10%">Tipe</th>
                                    <th class="bg-primary" width="10%">Header1</th>
                                    <th class="bg-primary" width="10%">Header2</th>
                                    <th class="bg-primary" width="10%">Header3</th>
                                    <th class="bg-primary" width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="coa_table">
                            </tbody>
                        </table>
                    </div>
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
        var body_coa = '';
        var base_url = "{{ url('/') }}"
        $(function () {
            // $('#example1').DataTable()

            $("#btn-copy").on("click", function() {
                $("#modal-copy").modal("show")
            })

            getDataCoa($('#cabang_table').val());

            $('#cabang_table').change(function(){
                getDataCoa($(this).val())
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

        function getDataCoa(id_cabang){
            $('#table_master_akun').treetable('destroy');
            $.ajax({
                url: "{{ route('master-coa-populate') }}/" + id_cabang,
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    if (data.result) {
                        let data_coa = data.data;
                        body_coa = '';
                        if(jQuery.isEmptyObject(data_coa) == false){
                            getTreetable(data_coa, 1);
                            $('#coa_table').html(body_coa);
                            $('#table_master_akun').treetable({expandable: true});
                        }
                        else{
                            body_coa += '<tr><td colspan="8" class="text-center">Empty Data</td></tr>';
                            $('#coa_table').html(body_coa);
                        }
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
        }

        function getTreetable(data, level){
            let parent_level = level;
            data.forEach(element => {
                if(element.id_parent == null){
                    body_coa += '<tr data-tt-id="' + element.id_akun + '">';
                }else{
                    body_coa += '<tr data-tt-id="' + element.id_akun + '" data-tt-parent-id="' + element.id_parent + '">';
                }
                body_coa += '<td>' + element.kode_akun + '</td>';
                body_coa += '<td>' + element.nama_akun + '</td>';
                body_coa += '<td>' + parent_level + '</td>';
                if(element.tipe_akun == 0){
                    body_coa += '<td>Neraca</td>';
                }else if(element.tipe_akun == 1){
                    body_coa += '<td>Laba Rugi</td>';
                }else{
                    body_coa += '<td>-</td>';
                }
                body_coa += '<td>' + ((element.header1 == null) ? '-' : element.header1) + '</td>';
                body_coa += '<td>' + ((element.header2 == null) ? '-' : element.header2) + '</td>';
                body_coa += '<td>' + ((element.header3 == null) ? '-' : element.header3) + '</td>';
                body_coa += '<td class="btn-column">';
                // body_coa += '<div class="btn-group" role="group"><button id="btnActionGroup" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>';
                // body_coa += '<ul class="dropdown-menu" aria-labelledby="btnActionGroup">';
                // body_coa += '<li class="dropdown-item"><a href="' + base_url + '/master/coa/form/show/' + element.id_akun + '">Detail</a></li>';
                // body_coa += '<li class="dropdown-item"><a href="' + base_url + '/master/coa/form/edit/' + element.id_akun + '">Edit</a></li>';
                // body_coa += '<li class="dropdown-item text-danger"><a href="' + base_url + '/master/coa/destory/' + element.id_akun + '">Delete</a></li>';
                body_coa += '<a href="' + base_url + '/master/coa/form/show/' + element.id_akun + '" class="btn-sm btn-default"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>';
                body_coa += '<a href="' + base_url + '/master/coa/form/edit/' + element.id_akun + '" class="btn-sm btn-warning"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>';
                body_coa += '<a href="' + base_url + '/master/coa/form/destory/' + element.id_akun + '" class="btn-sm btn-danger"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>';
                // body_coa += '</ul></div>';
                body_coa += '</td></tr>';
                if(typeof(element.children) != "undefined"){
                    getTreetable(element.children, (parent_level + 1));
                }
            });

            // return html;
        }

        Object.filter = (obj, predicate) =>
            Object.keys(obj)
                .filter( key => predicate(obj[key]) )
                .reduce( (res, key) => (res[key] = obj[key], res), {} );

    </script>
@endsection
