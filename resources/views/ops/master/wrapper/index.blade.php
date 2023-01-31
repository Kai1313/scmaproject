@extends('layouts.main')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endpush

@section('header')
    <p>Daftar Master Wrapper</p>
@endsection

@section('main-section')
    <div class="panel">
        <div class="panel-body">
            <div style="margin-bottom:10px;">
                <a href="{{ route('master-wrapper-entry') }}" class="btn btn-primary">Tambah</a>
            </div>

            <table class="table table-bordered data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pembungkus</th>
                        <th>Berat</th>
                        <th width="100px">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script>
        $(function() {

            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('master-wrapper-page') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'nama_wrapper',
                        name: 'nama_wrapper'
                    },
                    {
                        data: 'weight',
                        name: 'weight'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

        });
    </script>
@endpush
