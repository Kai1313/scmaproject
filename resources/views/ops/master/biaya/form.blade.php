@extends('layouts.main')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
@endpush

@section('header')
    <p>{{ $data ? 'Edit' : 'Tambah' }} Master Biaya</p>
@endsection

@section('main-section')
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('master-biaya-save-entry', $data ? $data->id_biaya : 0) }}" method="post">
        <div class="row">
            <div class="col-md-9">
                {{-- <div class="row">
                    <label class="col-md-3">Cabang</label>
                    <div class="col-md-5 form-group">
                        <select name="id_cabang" class="form-control">

                        </select>
                    </div>
                </div> --}}
                <div class="row">
                    <label class="col-md-2">Nama Biaya</label>
                    <div class="col-md-6 form-group">
                        <input type="text" class="form-control" name="nama_biaya" value="">
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Akun Biaya</label>
                    <div class="col-md-5 form-group">
                        <select name="id_akun_biaya" class="form-control select2">
                            <option value="">Pilih Akun Biaya</option>
                            @foreach ($akunBiaya as $biaya)
                                <option value="{{ $biaya->id_akun }}">{{ $biaya->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">PPn</label>
                    <div class="col-md-10 form-group">
                        <input type="checkbox" name="isppn" value="1">
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">PPh</label>
                    <div class="col-md-10 form-group">
                        <input type="checkbox" name="ispph" value="1">
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Nilai PPh</label>
                    <div class="col-md-3 form-group">
                        <input type="number" name="value_pph" class="form-control show-pph">
                    </div>
                </div>
                <div class="row show-pph">
                    <label class="col-md-2">Akun PPh</label>
                    <div class="col-md-5 form-group">
                        <select name="id_akun_pph" class="form-control select2 show-pph">
                            <option value="">Pilih Akun PPh</option>
                            @foreach ($akunBiaya as $pph)
                                <option value="{{ $pph->id_akun }}">{{ $pph->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2">Aktif</label>
                    <div class="col-md-10 form-group">
                        <input type="checkbox" name="aktif" value="1">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button class="btn btn-primary btn-block" type="submit">Simpan</button>
                <a href="{{ route('master-biaya-page') }}" class="btn btn-default btn-block">Kembali</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script>
        $('[name="ispph"]').change(function() {
            if ($(this).is(':checked')) {
                $('.show-pph').prop('disabled', false)
            } else {
                $('.show-pph').prop('disabled', true).val('')
            }
        })

        $('.select2').select2()
        $('.show-pph').prop('disabled', true)
    </script>
@endpush
