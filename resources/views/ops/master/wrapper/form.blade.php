@extends('layouts.main')

@section('header')
    <p>{{ $data ? 'Edit' : 'Tambah' }} Master Wrapper</p>
@endsection

@section('main-section')
    <div class="panel">
        <div class="panel-body">
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
            <form action="{{ route('master-wrapper-save-entry', $data ? $data->id_wrapper : 0) }}" method="post"
                enctype="multipart/form-data">
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="row">
                                    <label class="col-md-3">Cabang</label>
                                    <div class="col-md-5 form-group">
                                        <select name="id_cabang" class="form-control select2">
                                            <option value="">Pilih Cabang</option>
                                            @foreach ($cabang as $branch)
                                                <option value="{{ $branch->id_cabang }}"
                                                    {{ old('id_cabang', $data ? $data->id_cabang : '') == $branch->id_cabang ? 'selected' : '' }}>
                                                    {{ $branch->nama_cabang }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-3">Nama Pembungkus</label>
                                    <div class="col-md-6 form-group">
                                        <input type="text" class="form-control" name="nama_wrapper"
                                            value="{{ old('nama_wrapper', $data ? $data->nama_wrapper : '') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-3">Berat</label>
                                    <div class="col-md-3 form-group">
                                        <div class="input-group">
                                            <input type="number" name="weight" class="form-control show-pph"
                                                value="{{ old('weight', $data ? $data->weight : '') }}" step=".0001">
                                            <span class="input-group-addon">KG</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-3">Catatan</label>
                                    <div class="col-md-9 form-group">
                                        <textarea name="catatan" class="form-control" rows="10">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-3">Gambar</label>
                                    <div class="col-md-5">
                                        <input type="file" class="form-control" name="file_upload" accept=".png,.jpeg">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-primary btn-block" type="submit">Simpan</button>
                                <a href="{{ route('master-wrapper-page') }}" class="btn btn-default btn-block">Kembali</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
