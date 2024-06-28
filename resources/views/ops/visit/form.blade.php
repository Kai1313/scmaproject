@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link href="{{ asset('css/quill.snow.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/fancybox.css') }}" />
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

        .remove-media-container {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
        }

        .item-media:hover .remove-media-container {
            opacity: 1;
        }

        .item-media {
            width: 100px;
            padding: 3px;
            position: relative;
        }

        .item-media>a>img {
            height: 94px;
            object-fit: cover;
        }

        .container-media {
            border: 1px solid #d2d6de;
            display: flex;
            flex-wrap: wrap;
            border-radius: 3px;
            margin-bottom: 10px;
            min-height: 102px;
        }

        .add-image {
            padding-top: 30px;
            border: 1px dashed #3c8dbc;
            border-radius: 3px;
        }

        .add-image:hover {
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 5px;
        }

        .ql-editor {
            overflow-y: scroll;
            min-height: 150px;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Kunjungan
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('visit') }}">Kunjungan</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <form action="{{ route('visit-save-entry', $data ? $data->id : 0) }}" method="post" class="post-action">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Edit' : 'Tambah' }} Kunjungan</h3>
                    <a href="{{ route('visit') }}" class="btn bg-navy btn-sm btn-default pull-right btn-flat">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-4">Kode Kunjungan</label>
                                <div class="form-group col-md-8">
                                    <input type="text" name="visit_code"
                                        value="{{ old('visit_code', $data ? $data->visit_code : '') }}"
                                        class="form-control" readonly placeholder="Otomatis">
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-4">Cabang <span>*</span></label>
                                <div class="form-group col-md-8">
                                    <select name="id_cabang" class="form-control select2" data-validation="[NOTEMPTY]"
                                        data-validation-message="Cabang tidak boleh kosong" {{ $data ? 'readonly' : '' }}>
                                        <option value="">Pilih Cabang</option>
                                        @foreach ($cabang as $c)
                                            <option value="{{ $c->id }}"
                                                {{ $data && $data->id_cabang == $c->id ? 'selected' : '' }}>
                                                {{ $c->text }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-4">Salesman <span>*</span></label>
                                <div class="form-group col-md-8">
                                    <select name="id_salesman" class="form-control select2" readonly
                                        data-validation="[NOTEMPTY]" data-validation-message="Sales tidak boleh kosong">
                                        <option value="">Pilih Salesman</option>
                                        @if ($data && $data->id_salesman)
                                            <option value="{{ $data->id_salesman }}" selected>
                                                {{ $data->salesman->nama_salesman }}
                                            </option>
                                        @else
                                            @if ($salesman)
                                                <option value="{{ $salesman->id_salesman }}" selected>
                                                    {{ $salesman->nama_salesman }}</option>
                                            @endif
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-4">Tanggal Kunjungan <span>*</span></label>
                                <div class="form-group col-md-8">
                                    <input type="datetime-local" name="visit_date"
                                        value="{{ old('visit_date', $data ? $data->visit_date : date('Y-m-d H:i')) }}"
                                        class="form-control datepicker" data-validation="[NOTEMPTY]"
                                        data-validation-message="Tanggal tidak boleh kosong" {{ $data ? 'readonly' : '' }}>
                                </div>
                            </div>
                            @if ($data && $data->alasan_ubah_tanggal)
                                <div class="row">
                                    <label class="col-md-4">Alasan Perubahan Tanggal</label>
                                    <div class="form-group col-md-8">
                                        <textarea name="alasan_ubah_tanggal" class="form-control" readonly>{{ $data->alasan_ubah_tanggal }}</textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-4">Pelanggan <span>*</span></label>
                                <div class="form-group col-md-8">
                                    <select name="id_pelanggan" class="form-control select2" data-validation="[NOTEMPTY]"
                                        data-validation-message="Pelanggan tidak boleh kosong"
                                        {{ $data ? 'readonly' : '' }}>
                                        <option value="">Pilih Pelanggan</option>
                                        @if ($data && $data->id_pelanggan)
                                            <option value="{{ $data->id_pelanggan }}" selected>
                                                {{ $data->pelanggan->nama_pelanggan }}</option>
                                        @endif
                                    </select>
                                    <div class="customer-action" style="{{ $data ? 'display:none' : 'display:block' }}">
                                        <a href="{{ route('visit-save-customer', [$data ? $data->id : 0, 0]) }}"
                                            class="show-customer">Tambah Pelanggan</a>
                                        @if ($data && $data->id_pelanggan)
                                            |
                                            <a href="{{ route('visit-save-customer', [$data ? $data->id : 0, $data->id_pelanggan]) }}"
                                                class="show-customer" data-id="{{ $data->id_pelanggan }}">Perbarui
                                                Pelanggan</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-4">Status</label>
                                <div class="form-group col-md-8">
                                    <select name="status" class="form-control select2" readonly>
                                        @foreach ($listStatus as $ks => $status)
                                            <option value="{{ $status['text'] }}"
                                                {{ $data ? ($data->status == $ks ? 'selected' : '') : ($ks == 1 ? 'selected' : '') }}>
                                                {{ $status['text'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-4">Catatan</label>
                            </div>
                            <div style="margin-bottom:20px;" id="pre_visit_desc" class="quill-editor"
                                data-read="{{ $data ? 'readonly' : '' }}">
                                {!! old('pre_visit_desc', $data ? $data->pre_visit_desc : '') !!}
                            </div>
                            <textarea name="pre_visit_desc" class="form-control" rows="3" {{ $data ? 'readonly' : '' }}
                                style="display:none;">{{ old('pre_visit_desc', $data ? $data->pre_visit_desc : '') }}</textarea>

                            @if ($data && $data->status == 0)
                                <div class="row">
                                    <label class="col-md-4">Alasan Pembatalan</label>
                                    <div class="form-group col-md-8">
                                        <textarea name="alasan_pembatalan" class="form-control" readonly>{{ $data->alasan_pembatalan }}</textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary submit-header btn-sm btn-flat"
                            style="{{ $data ? 'display:none' : 'display:inline' }}">
                            <i class="fa fa-floppy-o mr-1"></i> Simpan
                        </button>
                        @if ($data && $data->status != 0)
                            <button type="button" class="btn btn-warning edit-header btn-sm btn-flat">
                                <i class="fa fa-pencil mr-1"></i> Perbarui Data Kunjungan
                            </button>
                            <button type="button" class="btn btn-default cancel-edit-header btn-sm btn-flat"
                                style="display:none;">
                                <i class="fa fa-close mr-1"></i> Batal
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if ($data && $data->status != 0)
            @if (!$data->visit_title)
                <div class="text-right" style="margin-bottom:15px;">
                    <a href="{{ route('visit-save-date-change', $data->id) }}"
                        class="btn btn-success change-date btn-sm btn-flat">
                        <i class="fa fa-calendar mr-1"></i> Perbarui Tanggal Kunjungan
                    </a>
                    <a href="{{ route('cancel-visit', $data->id) }}"
                        class="btn btn-danger show-cancel-visit btn-sm btn-flat">
                        <i class="fa fa-close mr-1"></i> Batal Kunjungan
                    </a>
                </div>
            @endif
            @if (date('Y-m-d H:i:s') >= $data->visit_date)
                <div class="box">
                    <form action="{{ route('visit-save-report-entry', $data->id) }}" method="post" class="post-action">
                        <div class="box-header">
                            <h3 class="box-title">Hasil Kunjungan</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <label for="" class="col-md-4">Kategori Kunjungan <span>*</span></label>
                                        <div class="form-group col-md-8">
                                            <select name="kategori_kunjungan" class="form-control select2"
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Kategori tidak boleh kosong">
                                                <option value="">Pilih Kategori</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->nama_kategori_kunjungan }}"
                                                        {{ $data->kategori_kunjungan == $category->nama_kategori_kunjungan ? 'selected' : '' }}>
                                                        {{ $category->nama_kategori_kunjungan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <label for="">Hasil Kunjungan <span>*</span></label>
                                    <div class="form-group">
                                        <div style="margin-bottom:20px;" id="visit_title" class="quill-editor"
                                            data-read="">
                                            {!! old('visit_title', $data ? nl2br($data->visit_title) : '') !!}
                                        </div>

                                        <textarea name="visit_title" class="form-control" rows="3" data-validation="[NOTEMPTY]"
                                            data-validation-message="Hasil kunjungan tidak boleh kosong" style="display: none;">{{ $data->visit_title }}</textarea>
                                    </div>
                                    <label for="">Progres <span>*</span></label>
                                    <div class="form-group">
                                        @php
                                            $explodeExtra = explode(', ', $data->progress_ind);
                                        @endphp
                                        @foreach ($progress as $key => $pro)
                                            <span style="margin-right:10px;">
                                                @php
                                                    $extra = '';
                                                    if ($key == 0) {
                                                        $extra =
                                                            'data-validation=[NOTEMPTY] data-validation-message=Hasil_kunjungan_tidak_boleh_kosong';
                                                    }
                                                @endphp
                                                <input type="checkbox" name="progress_ind[]" value="{{ $pro }}"
                                                    {{ $extra }}
                                                    {{ in_array($pro, $explodeExtra) ? 'checked' : '' }}>
                                                {{ $pro }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <label for="">Gambar</label>
                                    <div class="container-media">
                                        @foreach ($data->medias as $media)
                                            <div class="item-media">
                                                <a data-src="{{ asset($media->image) }}" data-fancybox="gallery">
                                                    <img src="{{ asset($media->image) }}" style="width:100%;">
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="remove-media-container btn btn-danger btn-sm btn-flat">
                                                    <i class="fa fa-close"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                        <div class="item-media text-center add-image" style="">
                                            <i class="fa fa-plus" style="font-size:37px;color:#3c8dbc;"></i>
                                        </div>
                                    </div>
                                    <input type="file" name="upload_image" class="form-control" value=""
                                        multiple style="display:none;" accept=".png,.jpeg,.jpg" id="upload_image">
                                    <input type="hidden" name="upload_base64"
                                        value="{{ $data->medias ? json_encode($data->medias) : [] }}">
                                    <input type="hidden" name="remove_base64" value="[]">
                                </div>
                                <div class="col-md-6">
                                    <label for="">Kendala</label>
                                    <div class="form-group">
                                        <div style="margin-bottom:20px;" id="visit_desc" class="quill-editor"
                                            data-read="">
                                            {!! old('visit_desc', $data ? nl2br($data->visit_desc) : '') !!}
                                        </div>
                                        <textarea name="visit_desc" class="form-control" rows="3" style="display: none;">{{ $data->visit_desc }}</textarea>
                                    </div>
                                    <label for="">Solusi</label>
                                    <div class="form-group">
                                        <div style="margin-bottom:20px;" id="solusi" class="quill-editor"
                                            data-read="">
                                            {!! old('solusi', $data ? nl2br($data->solusi) : '') !!}
                                        </div>
                                        <textarea name="solusi" class="form-control" rows="3" style="display: none;">{{ $data->solusi }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="pull-right">
                                <input type="hidden" name="latitude_visit" value="{{ $data->latitude_visit }}">
                                <input type="hidden" name="longitude_visit" value="{{ $data->longitude_visit }}">
                                <button type="submit" class="btn btn-primary btn-sm btn-flat">
                                    <i class="fa fa-floppy-o mr-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        @endif
    </div>

    <div id="modal-customer-visit" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="" method="post" class="post-action">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Pelanggan</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <label for="" class="col-md-4">Nama Pelanggan <span>*</span></label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="nama_pelanggan"
                                    data-validation="[NOTEMPTY]" data-validation-message="Nama tidak boleh kosong">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Alamat <span>*</span></label>
                            <div class="form-group col-md-8">
                                <textarea name="alamat_pelanggan" class="form-control" data-validation="[NOTEMPTY]"
                                    data-validation-message="Alamat tidak boleh kosong"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Bidang usaha</label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="bidang_usaha_pelanggan">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Kota <span>*</span></label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="kota_pelanggan"
                                    data-validation="[NOTEMPTY]" data-validation-message="Kota tidak boleh kosong">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Telepon <span>*</span></label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="telepon1_pelanggan"
                                    data-validation="[NOTEMPTY]" data-validation-message="Telepon tidak boleh kosong">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Kapasitas pelanggan</label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="kapasitas_pelanggan">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Orang yang dihubungi</label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="kontak_person_pelanggan">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Posisi orang yang dihubungi</label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="posisi_kontak_person_pelanggan">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Aset</label>
                            <div class="form-group col-md-8">
                                <textarea name="aset_pelanggan" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Status</label>
                            <div class="form-group col-md-8">
                                <select name="status_aktif_pelanggan" class="form-control">
                                    {{-- <option value=""></option> --}}
                                    <option value="1">Aktif</option>
                                    <option value="2">Pindah</option>
                                    <option value="3">Tutup</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Keterangan</label>
                            <div class="form-group col-md-8">
                                <textarea name="keterangan_pelanggan" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm btn-flat">Simpan</button>
                        <button type="button" class="btn btn-default btn-sm btn-flat"
                            data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-cancel-visit" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="" method="post" class="post-action">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Batalkan Kunjungan</h4>
                    </div>
                    <div class="modal-body">
                        <label for="">Alasan Pembatalan <span>*</span></label>
                        <div class="form-group">
                            <textarea name="alasan_pembatalan" class="form-control" data-validation="[NOTEMPTY]"
                                data-validation-message="Alasan pembatalan tidak boleh kosong"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm btn-flat">Simpan</button>
                        <button type="button" class="btn btn-default btn-sm btn-flat"
                            data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-date-change-visit" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="" method="post" class="post-action">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Perbarui Tanggal Kunjungan</h4>
                    </div>
                    <div class="modal-body">
                        <label for="">Tanggal <span>*</span></label>
                        <div class="form-group">
                            <input type="datetime-local" class="form-control" data-validation="[NOTEMPTY]"
                                data-validation-message="Tanggal tidak boleh kosong" name="new_date">
                        </div>
                        <label for="">Alasan Ubah Tanggal <span>*</span></label>
                        <div class="form-group">
                            <textarea name="alasan_ubah_tanggal" class="form-control" data-validation="[NOTEMPTY]"
                                data-validation-message="Alasan tidak boleh kosong"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm btn-flat">Simpan</button>
                        <button type="button" class="btn btn-default btn-sm btn-flat"
                            data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/fancybox.min.js') }}"></script>
    <script src="{{ asset('js/quill.js') }}"></script>
    <script>
        $('.quill-editor').each(function() {
            window[$(this).prop('id')] = new Quill('#' + $(this).prop('id'), {
                theme: 'snow'
            });

            let read = $(this).data('read')
            if (read != '') {
                window[$(this).prop('id')].disable();
            }

            window[$(this).prop('id')].on('editor-change', (eventName, ...args) => {
                if (eventName === 'selection-change') {
                    $('[name="' + $(this).prop('id') + '"]').val(window[$(this).prop('id')].root.innerHTML)
                }
            });
        })
    </script>
@endsection

@section('externalScripts')
    <script>
        var visit = {!! $data ? json_encode($data->visit_title) : '' !!}
        $('.select2').select2()
        let customer = {
            id: '{{ $data ? $data->id_pelanggan : '' }}',
            title: '{{ $data ? $data->pelanggan->nama_pelaggan : '' }}',
            address: '{{ str_replace(["\n", "\r"], '', $data ? $data->pelanggan->alamat_pelanggan : '') }}'
        }

        Fancybox.bind('[data-fancybox="gallery"]');

        function formatData(data) {
            if (!data.id) {
                return data.text;
            }

            let $result = $('<div><b>' + data.text + '</b><br>' + (data.alamat_pelanggan != null ? data.alamat_pelanggan :
                customer.address) + '</div>');
            return $result;
        };

        $('[name="id_pelanggan"]').select2({
            templateResult: formatData,
            templateSelection: formatData,
            ajax: {
                url: "{{ route('visit-customer') }}",
                data: function(params) {
                    return {
                        search: params.term,
                    }
                },
                processResults: function(data) {
                    return {
                        results: [{
                            'id': '',
                            'text': 'Pilih Pelanggan'
                        }, ...data]
                    };
                }
            }
        })

        $('.show-customer').click(function(e) {
            e.preventDefault()
            let modal = $('#modal-customer-visit')
            modal.find('input,textarea').val('')
            if ($(this).data('id')) {
                $('#cover-spin').show()
                $.ajax({
                    url: "{{ route('visit-find-customer', $data ? $data->id_pelanggan : 0) }}",
                    success: function(res) {
                        for (let key in res) {
                            $('[name="' + key + '"]').val(res[key] ? res[key].trim() : res[key])
                        }

                        modal.modal()
                        $('#cover-spin').hide()
                    },
                    error: function(error) {
                        $('#cover-spin').hide()
                        Swal.fire("Gagal Menyimpan Data. ", data.responseJSON.message, 'error')
                    }
                })
            } else {
                modal.modal()
            }

            let url = $(this).prop('href')
            modal.find('form').attr('action', url)

        })

        $('.show-cancel-visit').click(function(e) {
            e.preventDefault()
            let url = $(this).prop('href')
            let modal = $('#modal-cancel-visit')
            modal.find('form').attr('action', url)
            modal.modal()
        })

        $('.edit-header').click(function() {
            let arrayFormHeader = ['id_pelanggan', 'pre_visit_desc'];
            if (visit) {
                arrayFormHeader = []
            }

            for (let i = 0; i < arrayFormHeader.length; i++) {
                $('[name="' + arrayFormHeader[i] + '"]').attr('readonly', false)
            }

            $('.submit-header').css('display', 'inline')
            $('.edit-header').css('display', 'none')
            $('.cancel-edit-header').css('display', 'inline')
            $('.customer-action').css('display', 'inline')
            window['pre_visit_desc'].enable();
        })

        $('.cancel-edit-header').click(function() {
            let arrayFormHeader = ['id_pelanggan', 'pre_visit_desc'];
            for (let i = 0; i < arrayFormHeader.length; i++) {
                $('[name="' + arrayFormHeader[i] + '"]').attr('readonly', true)
            }

            $('.submit-header').css('display', 'none')
            $('.edit-header').css('display', 'inline')
            $('.cancel-edit-header').css('display', 'none')
            $('.customer-action').css('display', 'none')
            window['pre_visit_desc'].disable();
        })

        $('.change-date').click(function(e) {
            e.preventDefault()
            let url = $(this).prop('href')
            let modal = $('#modal-date-change-visit')
            modal.find('form').attr('action', url)
            modal.find('[name="new_date"]').val($('[name="visit_date"]').val())
            modal.modal()
        })

        $('body').on('click', '.remove-media-container', function(e) {
            e.preventDefault()
            let el = $(this).parents('.item-media')
            let index = el.index()
            let medias = JSON.parse($('[name="upload_base64"]').val())
            let removeMedias = JSON.parse($('[name="remove_base64"]').val())

            removeMedias.push(medias[index])
            medias.splice(index, 1);
            el.remove()

            $('[name="remove_base64"]').val(JSON.stringify(removeMedias))
        })

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                let latitude = position.coords.latitude;
                let longitude = position.coords.longitude;
                $('[name="latitude_visit"]').val(latitude)
                $('[name="longitude_visit"]').val(longitude)
            });
        } else {
            console.log("Geolocation is not supported by this browser.");
        }

        $('.add-image').click(function() {
            $('[name="upload_image"]').click()
        })

        $('[name="upload_image"]').change(function(e) {
            createBase64Format(this)
        })

        function createBase64Format(self) {
            if ($(self).val()) {
                let files = document.getElementById("upload_image").files;
                for (let i = 0; i < files.length; i++) {
                    let file = files[i]
                    let oFReader = new FileReader();
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
                                $('.add-image').before(createHtmlImage(dataUrl))

                                let json = JSON.parse($('[name="upload_base64"]').val())
                                json.push(dataUrl)
                                $('[name="upload_base64"]').val(JSON.stringify(json))
                            }
                            image.src = readerEvent.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                }
            }
        }

        function createHtmlImage(base64) {
            let html = '<div class="item-media"><input type="hidden" name="medias[]" value="">' +
                '<a href="javascript:void(0)"><img src="' + base64 + '" style="width:100%;"></a>' +
                '<a href="javascript:void(0)" class="remove-media-container btn btn-danger btn-sm btn-sm btn-flat"><i class="fa fa-close"></i></a></div>'
            return html;
        }
    </script>
@endsection
