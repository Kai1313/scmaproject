@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        label>span {
            color: red;
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
        @if (session()->has('success'))
            <div class="alert alert-success">
                <ul>
                    <li>{!! session()->get('success') !!}</li>
                </ul>
            </div>
        @endif
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Pembungkus</h3>
                <a href="{{ route('master-wrapper') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                        class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali</a>
            </div>
            <div class="box-body">
                <form action="{{ route('master-wrapper-save-entry', $data ? $data->id_wrapper : 0) }}" method="post"
                    enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cabang <span>*</span></label>
                                <select name="id_cabang" class="form-control select2">
                                    <option value="">Pilih Cabang</option>
                                    @foreach ($cabang as $branch)
                                        <option value="{{ $branch->id_cabang }}"
                                            {{ old('id_cabang', $data ? $data->id_cabang : '') == $branch->id_cabang ? 'selected' : '' }}>
                                            {{ $branch->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nama Pembungkus <span>*</span></label>
                                <input type="text" class="form-control" name="nama_wrapper"
                                    value="{{ old('nama_wrapper', $data ? $data->nama_wrapper : '') }}">
                            </div>
                            <div class="form-group">
                                <label>Berat <span>*</span></label>
                                <div class="input-group">
                                    <input type="number" name="weight" class="form-control show-pph"
                                        value="{{ old('weight', $data ? $data->weight : '') }}" step=".0001">
                                    <span class="input-group-addon">KG</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Catatan</label>
                                <textarea name="catatan" class="form-control" rows="4">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Gambar</label>
                                <br>
                                @if ($data && $data->path)
                                    <a href="{{ env('FTP_GET_FILE') . $data->path }}" target="_blank">
                                        <img src="{{ env('FTP_GET_FILE') . $data->path2 }}" alt="" width="100"
                                            id="uploadPreview1" style="margin:10px 10px 10px 0px;border-radius:5px;">
                                    </a>
                                @else
                                    <img alt="" width="100" id="uploadPreview1">
                                @endif
                                <br>
                                <input id="f_image" type="file" class="form-control" name="file_upload"
                                    accept=".png,.jpeg,.jpg">
                                <input type="hidden" name="image_path">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary btn-flat pull-right" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
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

        $(function() {
            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus()
            })

            $(document).on('focus', '.select2-selection.select2-selection--single', function(e) {
                $(this).closest(".select2-container").siblings('select:enabled').select2('open')
            })

            $('select.select2').on('select2:closing', function(e) {
                $(e.target).data("select2").$selection.one('focus focusin', function(e) {
                    e.stopPropagation();
                })
            })
        })
    </script>
@endsection
