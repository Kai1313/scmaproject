@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
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

        .content-textarea {
            border: 1px solid grey;
            padding: 5px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Kunjungan
            <small>| Lihat</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('visit') }}">Kunjungan</a></li>
            <li class="active">Lihat</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Lihat Kunjungan</h3>
                <a href="{{ route('visit') }}" class="btn bg-navy btn-sm btn-default pull-right btn-flat">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Kode Kunjungan</label>
                            <div class="col-md-8">: {{ $data->visit_code }}</div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Cabang </label>
                            <div class="col-md-8">: {{ $data->cabang->nama_cabang }}</div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Salesman</label>
                            <div class="col-md-8">: {{ $data->salesman->nama_salesman }}</div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal Kunjungan</label>
                            <div class="col-md-8">: {{ $data->visit_date }}</div>
                        </div>
                        @if ($data && $data->alasan_ubah_tanggal)
                            <div class="row">
                                <label class="col-md-4">Alasan Ubah Tanggal</label>
                                <div class="col-md-8">: {{ $data->alasan_ubah_tanggal }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Pelanggan</label>
                            <div class="col-md-8"> : {{ $data->pelanggan ? $data->pelanggan->nama_pelanggan : '' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Alamat</label>
                            <div class="col-md-8"> : {{ $data->pelanggan ? $data->pelanggan->alamat_pelanggan : '' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Status</label>
                            <div class="col-md-8"> :
                                {{ isset($listStatus[$data->status]) ? $listStatus[$data->status]['text'] : '' }}</div>
                        </div>
                        @if ($data && $data->status == 0)
                            <div class="row">
                                <label class="col-md-4">Alasan Pembatalan</label>
                                <div class="form-group col-md-8">
                                    <textarea name="alasan_pembatalan" class="form-control" readonly>{{ $data->alasan_pembatalan }}</textarea>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <label class="col-md-4">Catatan </label>
                            <div class=" col-md-8">: </div>
                        </div>
                        @if (strip_tags($data->pre_visit_desc))
                            <div class="content-textarea">
                                {!! nl2br($data->pre_visit_desc) !!}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($data && $data->status != 0)
            @if (date('Y-m-d') >= $data->visit_date)
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Hasil Kunjungan</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                {{-- <div class="row">
                                    <label for="" class="col-md-4">Metode Kunjungan</label>
                                    <div class="col-md-8">
                                        :{{ isset($methods[$data->visit_type]) ? $methods[$data->visit_type]['text'] : '' }}
                                    </div>
                                </div> --}}
                                <div class="row">
                                    <label for="" class="col-md-4">Kategori Kunjungan</label>
                                    <div class="col-md-8">: {{ $data->kategori_kunjungan }}</div>
                                </div>
                                <div class="row">
                                    <label for="" class="col-md-4">Hasil Kunjungan</label>
                                    <div class="col-md-8">: </div>
                                </div>
                                @if (strip_tags($data->visit_title))
                                    <div class="content-textarea">
                                        {!! nl2br($data->visit_title) !!}
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="" class="col-md-4">Progres</label>
                                    <div class="col-md-8"> : {{ $data->progress_ind }}</div>
                                </div>
                                <div class="row">
                                    <label for="" class="col-md-4">Kendala</label>
                                    <div class="col-md-8"> : </div>
                                </div>
                                @if (strip_tags($data->visit_desc))
                                    <div class="content-textarea">
                                        {!! nl2br($data->visit_desc) !!}
                                    </div>
                                @endif
                                <div class="row">
                                    <label for="" class="col-md-4">Solusi</label>
                                    <div class="col-md-8">:</div>
                                </div>
                                @if (strip_tags($data->solusi))
                                    <div class="content-textarea">
                                        {!! nl2br($data->solusi) !!}
                                    </div>
                                @endif
                                <label for="">Gambar</label>
                                <div class="container-media">
                                    @foreach ($data->medias as $media)
                                        <div class="item-media">
                                            <a data-src="{{ asset($media->image) }}" data-fancybox="gallery">
                                                <img src="{{ asset($media->image) }}" style="width:100%;">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/fancybox.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
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
            for (let i = 0; i < arrayFormHeader.length; i++) {
                $('[name="' + arrayFormHeader[i] + '"]').attr('readonly', false)
            }

            $('.submit-header').css('display', 'inline')
            $('.edit-header').css('display', 'none')
            $('.cancel-edit-header').css('display', 'inline')
            $('.customer-action').css('display', 'inline')
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
