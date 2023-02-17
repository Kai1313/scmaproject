@extends('layouts.main')
@section('addedStyles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <!-- Treetable -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/jquery-treetable/css/jquery.treetable.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/jquery-treetable/css/jquery.treetable.theme.default.css') }}">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #table_master_akun th {
            text-align: center !important;
            font-size: 1.5rem !important;
            border-color: white !important;
            padding: 0.6rem 0.4rem;
        }

        #table_master_akun td {
            font-size: 1.3rem !important;
            padding: 0.5rem !important;
        }

        #table_master_akun td.btn-column {
            text-align: center !important;
            font-size: 12px;
            padding: 8px;
        }

        #table_master_akun td.btn-column span {
            padding: 2px !important;
        }

        .dropdown-menu>li>a.text-danger {
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

        .mb-1 {
            margin-bottom: .25rem !important;
        }
    </style>
@endsection

@section('header')
    <p>Daftar Uang Muka Pembelian</p>
@endsection

@section('main-section')
    <div class="panel">
        <div class="panel-body">
            <div style="margin-bottom:10px;">
                <a href="{{ route('purchase-down-payment-entry') }}" class="btn btn-primary">Tambah Uang Muka Pembelian</a>
                <br><br>
                <select name="id_cabang" class="form-control" style="width:200px;">
                    @foreach ($cabang as $branch)
                        <option value="{{ $branch->id_cabang }}">{{ $branch->kode_cabang }} - {{ $branch->nama_cabang }}
                        </option>
                    @endforeach
                </select>
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
                        <th>ID Uang Muka Pembelian</th>
                        <th>Tanggal</th>
                        <th>ID Permintaan Pembelian (PO)</th>
                        <th>Supplier</th>
                        <th>Mata Uang</th>
                        <th>Rate</th>
                        <th>Nominal</th>
                        <th>Total</th>
                        <th>Catatan</th>
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
    <script>
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('purchase-down-payment') }}?c=" + $('[name="id_cabang"]').val(),
            columns: [{
                data: 'kode_uang_muka_pembelian',
                name: 'kode_uang_muka_pembelian'
            }, {
                data: 'tanggal',
                name: 'tanggal'
            }, {
                data: 'id_permintaan_pembeliaan',
                name: 'id_permintaan_pembeliaan',
            }, {
                data: 'id_permintaan_pembeliaan',
                name: 'id_permintaan_pembeliaan',
            }, {
                data: 'id_mata_uang',
                name: 'id_mata_uang',
            }, {
                data: 'rate',
                name: 'rate'
            }, {
                data: 'nominal',
                name: 'nominal',
            }, {
                data: 'total',
                name: 'total',
            }, {
                data: 'catatan',
                name: 'catatan',
            }, {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }, ]
        });

        $(document).on('click', '.btn-destroy', function(e) {
            e.preventDefault()
            let route = $(this).prop('href')
            $('#approvalDelete').modal('show').find('form').attr('action', route)
        })

        $('[name="id_cabang"]').change(function() {
            table.ajax.url("?c=" + $(this).val()).load()
        })
    </script>
@endsection
