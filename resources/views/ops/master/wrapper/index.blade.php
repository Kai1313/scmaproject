@extends('layouts.main')

@push('styles')
    {{-- <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}"> --}}
@endpush

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
                        <option value="{{ $branch->id_cabang }}">{{ $branch->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
            <table class="table table-bordered data-table">
                <thead>
                    <tr>
                        <th>Nama Pembungkus</th>
                        <th>Berat</th>
                        <th>Gambar</th>
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

@push('scripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script>
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master-wrapper-page') }}" + "?c=" + $('[name="id_cabang"]').val(),
            columns: [{
                data: 'nama_wrapper',
                name: 'nama_wrapper'
            }, {
                data: 'weight',
                name: 'weight'
            }, {
                data: 'path2',
                name: 'path2'
            }, {
                data: 'catatan',
                name: 'catatan'
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
            table.ajax.url("?c=" + $(this).val()).load()
        })
    </script>
@endpush
