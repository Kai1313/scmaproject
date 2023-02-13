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
            padding: 0.5rem !important;
        }

        #table_master_akun td.btn-column{
            text-align: center !important;
            font-size: 12px;
            padding: 8px;
        }

        #table_master_akun td.btn-column span{
            padding: 2px !important;
        }

        .dropdown-menu>li>a.text-danger{
            color: #843534 !important;
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
    <p>Daftar Master Pembungkus</p>
@endsection

@section('main-section')
    <div class="panel">
        <div class="panel-body">
            <div style="margin-bottom:10px;">
                <a href="{{ route('master-wrapper-entry') }}" class="btn btn-primary">Tambah Data Pembungkus</a>
                <br><br>
                <select name="id_cabang" class="form-control" style="width:200px;">
                    @foreach ($cabang as $branch)
                        <option value="{{ $branch->id_cabang }}">{{$branch->kode_cabang}} - {{ $branch->nama_cabang }}</option>
                    @endforeach
                </select>
                Tampilkan Gambar <input type="checkbox" name="show_image">
            </div>
            @if (session()->has('success'))
                <div class="alert alert-success">
                    <ul>
                        <li>{!! session()->get('success') !!}</li>
                    </ul>
                </div>
            @endif
            <table class="table table-bordered data-table">
                <thead>
                    <tr>
                        <th>Nama Pembungkus</th>
                        <th class="text-right">Berat</th>
                        <th>Catatan</th>
                        <th>Gambar</th>
                        <th width="150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="approvalDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <h4>Anda akan menghapus data ini!</h4>
                </div>
                <div class="modal-footer">
                    <form action="" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    </form>
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
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script>
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master-wrapper') }}" + "?c=" + $('[name="id_cabang"]').val() + '&show_img=' + $(
                '[name="show_image"]').is(':checked'),
            columns: [{
                data: 'nama_wrapper',
                name: 'nama_wrapper'
            }, {
                data: 'weight',
                name: 'weight',
                className: "text-right"
            }, {
                data: 'catatan',
                name: 'catatan'
            }, {
                data: 'path2',
                name: 'path2'
            }, {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }]
        });

        $(document).on('click', '.btn-destroy', function(e) {
            e.preventDefault()
            let route = $(this).prop('href')
            $('#approvalDelete').modal('show').find('form').attr('action', route)
        })

        $('[name="id_cabang"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_img=' + $('[name="show_image"]').is(
                ':checked')).load()
        })

        $('[name="show_image"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_img=' + $('[name="show_image"]').is(
                ':checked')).load()
        })
    </script>
@endsection
