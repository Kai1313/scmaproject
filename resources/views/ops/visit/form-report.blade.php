@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        ul.horizontal-list {
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        ul.horizontal-list li {
            display: inline;
        }

        .mb-1 {
            margin-bottom: .25rem !important;
        }

        th {
            text-align: center;
        }

        .head-checkbox {
            padding-top: 30px;
        }

        .head-checkbox label {
            margin-right: 10px;
        }

        label>span {
            color: red;
        }

        .select2 {
            width: 100% !important;
        }

        .table-detail th {
            background-color: #f39c12;
            color: white;
            text-align: center;
        }

        .handle-number-4 {
            text-align: right;
        }

        tfoot>tr>td {
            font-weight: bold;
        }

        select[readonly].select2-hidden-accessible+.select2-container {
            pointer-events: none;
            touch-action: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container .select2-selection {
            background: #eee;
            box-shadow: none;
        }

        select[readonly].select2-hidden-accessible+.select2-container .select2-selection__arrow,
        select[readonly].select2-hidden-accessible+.select2-container .select2-selection__clear {
            display: none;
        }

        .disabled {
            background: white;
            opacity: 0.5;
        }

        .list-head {
            font-weight: bold;
            width: 140px;
            color: black !important;
            vertical-align: top;
            padding-bottom: 10px;
        }

        .list-sparator {
            width: 10px;
            vertical-align: top;
        }

        .list-value {
            padding-bottom: 10px;
        }

        .select2-container .select2-selection--single {
            height: auto !important;
            padding: 9px 12px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            white-space: normal !important;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Kunjungan
            <small>| Form Hasil Kunjungan</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('visit') }}">Kunjungan</a></li>
            <li class="active">Form Hasil Kunjungan</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">

        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <table>
                            <tr>
                                <td class="list-head">Cabang</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->cabang->nama_cabang }}</td>
                            </tr>
                            <tr>
                                <td class="list-head">Tanggal Kunjungan</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->visit_date }}</td>
                            </tr>
                            <tr>
                                <td class="list-head">Kode Kunjungan</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->visit_code }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <table>
                            <tr>
                                <td class="list-head">Salesman</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->salesman->nama_salesman }}</td>
                            </tr>
                            <tr>
                                <td class="list-head">Catatan</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->pre_visit_desc }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <table>
                            <tr>
                                <td class="list-head">Pelanggan</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->pelanggan->nama_pelanggan }}</td>
                            </tr>
                            <tr>
                                <td class="list-head">Alamat</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->pelanggan->alamat_pelanggan }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <form action="{{ route('visit-save-report-entry', $data->id) }}" method="post" class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Form Hasil Kunjungan</h3>
                    <a href="{{ route('visit') }}" class="btn bg-navy btn-sm btn-default pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="">Judul laporan <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="visit_title" class="form-control"
                                    value="{{ $data->visit_title }}" data-validation="[NOTEMPTY]"
                                    data-validation-message="Judul laporan tidak boleh kosong">
                            </div>
                            <label for="">Progres <span>*</span></label>
                            <div class="form-group">
                                <select name="progress_ind" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Progress tidak boleh kosong">
                                    <option value="">Pilih Progres</option>
                                    @foreach ($progress as $pro)
                                        <option value="{{ $pro }}"
                                            {{ $data->progress_ind == $pro ? 'selected' : '' }}>{{ $pro }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="">Metode Kunjungan <span>*</span></label>
                            <div class="form-group">
                                <select name="progress_ind" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Progress tidak boleh kosong">
                                    <option value="">Pilih Metode</option>
                                    @foreach ($methods as $method)
                                        <option value="{{ $method }}">{{}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="">Catatan <span>*</span></label>
                            <div class="form-group">
                                <textarea name="visit_desc" class="form-control" data-validation="[NOTEMPTY]"
                                    data-validation-message="Catatan tidak boleh kosong">{{ $data->visit_desc }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="">Gambar 1 <span>*</span></label>
                            <div class="form-group">
                                <input type="file" name="image_path_1" class="form-control">
                                <input type="hidden" name="proofment_1">
                            </div>
                            <label for="">Gambar 2</label>
                            <div class="form-group">
                                <input type="file" name="image_path_2" class="form-control">
                                <input type="hidden" name="proofment_2">
                            </div>
                        </div>
                    </div>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o mr-1"></i>Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $('.select2').select2()

        $('[name="file_upload"]').change(function() {
            if ($(this).val()) {
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
            }
        })
    </script>
@endsection
