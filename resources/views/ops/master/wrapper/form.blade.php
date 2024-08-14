@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        label>span {
            color: red;
        }

        .handle-number-4 {
            text-align: right;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Pembungkus
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-wrapper') }}">Master Pembungkus</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Pembungkus</h3>
                <a href="{{ route('master-wrapper') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <form action="{{ route('master-wrapper-save-entry', $data ? $data->id_wrapper : 0) }}" method="post"
                    enctype="multipart/form-data" class="post-action">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cabang <span>*</span></label>
                                <select name="id_cabang" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Cabang tidak boleh kosong">
                                    <option value="">Pilih Cabang</option>
                                    @if ($data && $data->id_cabang)
                                        <option value="{{ $data->id_cabang }}" selected>
                                            {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nama Pembungkus <span>*</span></label>
                                <input type="text" class="form-control" name="nama_wrapper"
                                    value="{{ old('nama_wrapper', $data ? $data->nama_wrapper : '') }}"
                                    data-validation="[NOTEMPTY]"
                                    data-validation-message="nama pembungkus tidak boleh kosong">
                            </div>
                            <div class="form-group">
                                <label>Kategori Pembungkus <span>*</span></label>
                                <select name="id_kategori_wrapper" class="form-control select2">
                                    <option value="">Pilih Kategori Pembungkus</option>
                                    <option value="1"
                                        {{ old('id_kategori_wrapper', $data ? $data->id_kategori_wrapper : '') == '1' ? 'selected' : '' }}>
                                        Palet
                                    </option>
                                    <option value="2"
                                        {{ old('id_kategori_wrapper', $data ? $data->id_kategori_wrapper : '') == '2' ? 'selected' : '' }}>
                                        Wadah
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Berat <span>*</span></label>
                                <div class="input-group">
                                    <input type="text" name="weight" class="form-control handle-number-4"
                                        value="{{ old('weight', $data ? $data->weight : '') }}"
                                        data-validation="[NOTEMPTY]" data-validation-message="Berat tidak boleh kosong">
                                    <span class="input-group-addon">KG</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Catatan</label>
                                <textarea name="catatan" class="form-control" rows="4">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Gambar</label>
                                <input id="f_image" type="file" class="form-control" name="file_upload"
                                    accept=".png,.jpeg,.jpg">
                                <input type="hidden" name="image_path">
                                <br>
                                @if ($data && $data->path)
                                    <a href="{{ asset('asset/' . $data->path) }}" target="_blank">
                                        <img src="{{ asset('asset/' . $data->path) }}" alt="" width="100"
                                            id="uploadPreview1" style="margin:10px 10px 10px 0px;border-radius:5px;">
                                    </a>
                                @else
                                    <img alt="" width="100" id="uploadPreview1">
                                @endif
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary btn-flat pull-right btn-sm" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branch = {!! json_encode($cabang) !!}
        $('[name="id_cabang"]').select2({
            data: branch
        })

        $('.select2').select2()

        $('[name="file_upload"]').change(function() {
            let oFReader = new FileReader();
            let file = document.getElementById("f_image").files[0];
            if (file.type.match(/image.*/)) {
                let reader = new FileReader();
                reader.onload = function(readerEvent) {
                    let image = new Image();
                    image.onload = function(imageEvent) {
                        let canvas = document.createElement('canvas'),
                            max_size = 1000,
                            width = image.width,
                            height = image.height;
                        if (width > height) {
                            if (width > max_size) {
                                height *= max_size / width;
                                width = max_size;
                            }
                        } else {
                            if (height > max_size) {
                                width *= max_size / height;
                                height = max_size;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        canvas.getContext('2d').drawImage(image, 0, 0, width, height);
                        let dataUrl = canvas.toDataURL('image/jpeg');
                        $('[name="image_path"]').val(dataUrl)
                        $('[name="file_upload"]').val('')
                        document.getElementById("uploadPreview1").src = dataUrl;
                    }
                    image.src = readerEvent.target.result;
                }
                reader.readAsDataURL(file);
            }
        })
    </script>
@endsection
