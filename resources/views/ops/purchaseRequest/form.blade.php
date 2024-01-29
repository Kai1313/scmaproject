@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
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
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Permintaan Pembelian
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Permintaan Pembelian</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('purchase-request-save-entry', $data ? $data->purchase_request_id : 0) }}" method="post"
            class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Permintaan Pembelian</h3>
                    <a href="{{ route('purchase-request') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
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

                            <label>Gudang <span>*</span></label>
                            <div class="form-group">
                                <select name="id_gudang" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Gudang tidak boleh kosong">
                                    <option value="">Pilih Gudang</option>
                                    @if ($data && $data->id_gudang)
                                        <option value="{{ $data->id_gudang }}" selected>
                                            {{ $data->gudang->kode_gudang }} - {{ $data->gudang->nama_gudang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            @if ($data)
                                <div class="row">
                                    <label class="col-md-3">Status</label>
                                    <div class="col-md-5 form-group">
                                        @if (isset($arrayStatus[$data->approval_status]))
                                            <label class="{{ $arrayStatus[$data->approval_status]['class'] }}">
                                                {{ $arrayStatus[$data->approval_status]['text'] }}
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label>Tanggal <span>*</span></label>
                            <div class="form-group">
                                <input type="date" name="purchase_request_date"
                                    value="{{ old('purchase_request_date', $data ? $data->purchase_request_date : date('Y-m-d')) }}"
                                    class="form-control" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal tidak boleh kosong" max="{{ date('Y-m-d') }}">
                            </div>
                            <label>Deadline <span>*</span></label>
                            <div class="form-group">
                                <input type="date" name="purchase_request_estimation_date"
                                    value="{{ old('purchase_request_estimation_date', $data ? $data->purchase_request_estimation_date : date('Y-m-d')) }}"
                                    class=" form-control" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal deadline tidak boleh kosong"
                                    min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Kode Permintaan</label>
                            <div class="form-group">
                                <input type="text" name="purchase_request_code"
                                    value="{{ old('purchase_request_code', $data ? $data->purchase_request_code : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                            <label>Catatan</label>
                            <div class="form-group">
                                <textarea name="catatan" class="form-control" rows="3">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Detil Permintaan Barang</h3>
                    @if (!$data || $data->approval_status == 0)
                        <button class="btn btn-info add-entry btn-flat pull-right btn-sm" type="button">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                        </button>
                    @endif
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <input type="hidden" name="details" value="">
                        <table id="table-detail" class="table table-bordered data-table display nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Catatan</th>
                                    <th>Stok</th>
                                    {{-- <th>Status</th> --}}
                                    <th style="width:150px;">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    @if (!$data || $data->approval_status == 0)
                        <button class="btn btn-primary btn-flat pull-right" type="submit">
                            <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                        </button>
                    @endif
                    <div style="margin-top:10px;color:red;">
                        <span>*</span> Upload gambar pendukung bisa di tambahkan disetiap detail barang
                        permintaan setelah disimpan data
                    </div>
                </div>
            </div>
        </form>

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <input type="hidden" name="index" value="0">
                        <label>Nama Barang <span>*</span></label>
                        <div class="form-group">
                            <select name="id_barang" class="form-control validate">
                            </select>
                            <input type="hidden" name="nama_barang" class="validate">
                            <input type="hidden" name="kode_barang" class="validate">
                        </div>
                        <div class="form-group">
                            <label>Stok : </label>
                            <label id="message-stok"></label>
                        </div>
                        <label>Satuan <span>*</span></label>
                        <div class="form-group">
                            <select name="id_satuan_barang" class="form-control select2 validate" disabled>
                            </select>
                            <input type="hidden" name="nama_satuan_barang" class="validate">
                        </div>
                        <label>Jumlah <span>*</span></label>
                        <div class="form-group">
                            <input type="text" name="qty" class="form-control validate handle-number-4"
                                autocomplete="off">
                        </div>
                        <label>Catatan <span>*</span></label>
                        <div class="form-group">
                            <textarea name="notes" class="form-control validate" rows="5"></textarea>
                        </div>
                        <input type="hidden" name="stok">
                        <input type="hidden" name="closed" value="0">
                        <input type="hidden" name="old_index">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cancel-entry btn-flat">Batal</button>
                        <button type="button" class="btn btn-primary save-entry btn-flat">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalCamera" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>Gambar</h4>
                    </div>
                    <div class="modal-body">
                        <div class="container-media" id="target-result-image-upload">
                            <div class="item-media text-center add-image" style="">
                                <i class="fa fa-plus" style="font-size:37px;color:#3c8dbc;"></i>
                            </div>
                        </div>
                        <input type="file" name="upload_image" class="form-control" value="" multiple
                            style="display:none;" accept=".png,.jpeg,.jpg" id="upload_image">
                        {{-- <input type="hidden" name="upload_base64"
                                value="{{ $data->medias ? json_encode($data->medias) : [] }}"> --}}
                        <input type="hidden" name="upload_base64" value="[]">
                        <input type="hidden" name="remove_base64" value="[]">
                        <input type="hidden" name="purchase_request_id" value="">
                        <input type="hidden" name="index" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary btn-flat post-image">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/fancybox.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branch = {!! json_encode($cabang) !!}
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let detailSelect = []
        let count = 0
        if (details[details.length - 1]) {
            count = details[details.length - 1].index
        }

        let statusModal = 'create'
        let urlShowImage = '{{ route('purchase-request-show-image') }}';
        let urlPostImage = '{{ route('purchase-request-post-image') }}'
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        $('.select2').select2()
        $('[name="details"]').val(JSON.stringify(details))

        var resDataTable = $('#table-detail').DataTable({
            scrollX: true,
            paging: false,
            data: details,
            ordering: false,
            columns: [{
                data: 'kode_barang',
                name: 'kode_barang'
            }, {
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'qty',
                name: 'qty',
                render: function(data) {
                    return formatNumber(data, 4)
                },
                className: 'text-right'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang'
            }, {
                data: 'notes',
                name: 'notes'
            }, {
                data: 'stok',
                name: 'stok',
                render: function(data) {
                    return formatNumber(data, 4)
                },
                className: 'text-right'
            }, {
                data: 'index',
                className: 'text-center',
                name: 'index',
                searchable: false,
                render: function(data, type, row, meta) {
                    console.log(row)
                    let btn = ''
                    if (row.purchase_request_id) {
                        btn +=
                            '<a href="javascript:void(0)" class="btn btn-info btn-xs mr-1 mb-1 edit-camera" data-index="' +
                            row.index + '" data-parent="' + row.purchase_request_id +
                            '"><i class="glyphicon glyphicon-camera"></i></a>';
                    }

                    btn +=
                        '<a href="javascript:void(0)" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a>';
                    btn +=
                        '<a href="javascript:void(0)" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a>';
                    return btn;
                }
            }]
        });

        $('.tag-qty').each(function(i, v) {
            let num = $(v).text()
            $(this).text(formatNumber(num, 4))
        })

        $('[name="id_cabang"]').select2({
            data: branch
        }).on('select2:select', function(e) {
            let dataselect = e.params.data
            getGudang(dataselect)
        });

        function getGudang(data) {
            $('[name="id_gudang"]').select2({
                data: [{
                    'id': "",
                    'text': 'Pilih Gudang'
                }, ...data.gudang]
            })
        }

        $('[name="id_barang"]').select2({
            ajax: {
                url: '{{ route('purchase-request-auto-item') }}',
                dataType: 'json',
                data: function(params) {
                    return {
                        search: params.term
                    }
                },
                processResults: function(data) {
                    return {
                        results: data.data
                    };
                }
            }
        }).on('select2:select', function(e) {
            let dataselect = e.params.data
            $('#modalEntry').find('[name="nama_barang"]').val(dataselect.text)
            $('#modalEntry').find('[name="kode_barang"]').val(dataselect.kode_barang)
            $('[name="id_satuan_barang"]').html('')
            getSatuan(dataselect.id)

        });

        function getSatuan(id) {
            $('#cover-spin').show()
            $.ajax({
                url: "{{ route('purchase-request-auto-satuan') }}?item=" + id + '&cabang=' + $(
                    '[name="id_cabang"]').val() + '&gudang=' + $('[name="id_gudang"]').val(),
                type: 'get',
                success: function(res) {
                    $('[name="id_satuan_barang"]').empty()
                    $('[name="id_satuan_barang"]').prop('disabled', false).select2({
                        data: res.satuan
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        $('#modalEntry').find('[name="nama_satuan_barang"]').val(dataselect
                            .text)
                    });

                    if (res.satuan.length > 0) {
                        $('[name="id_satuan_barang"]').val(res.satuan[0].id).trigger('change')
                        $('#modalEntry').find('[name="nama_satuan_barang"]').val(res.satuan[0].text)
                    }

                    $('#message-stok').text(formatNumber(res.stok, 4) + ' ' + res.satuan_stok)
                    $('#modalEntry').find('[name="stok"]').val(res.stok)
                    $('#modalEntry').find('[name="closed"]').val(0)
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    $('#cover-spin').hide()
                }
            })
        }

        $('.add-entry').click(function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })

            statusModal = 'create'
            count += 1
            $('#modalEntry').find('[name="index"]').val(count)
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })

            $('[name="id_barang"]').select2('open')
            $('#message-stok').text('')

            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
        })

        $('.save-entry').click(function() {
            let modal = $('#modalEntry')
            let valid = validatorModal(modal.find('[name="id_barang"]').val())
            if (!valid.status) {
                Swal.fire("Gagal tambah data. ", valid.message, 'error')
                return false
            }

            modal.find('input,select,textarea').each(function(i, v) {
                if ($(v).hasClass('handle-number-4')) {
                    detailSelect[$(v).prop('name')] = normalizeNumber($(v).val())
                } else {
                    detailSelect[$(v).prop('name')] = $(v).val()
                }
            })

            let newObj = Object.assign({}, detailSelect)
            if (statusModal == 'create') {
                details.push(newObj)
            } else if (statusModal == 'edit') {
                details[newObj.index - 1] = newObj
            }

            $('[name="details"]').val(JSON.stringify(details))

            statusModal = ''
            detailSelect = []

            resDataTable.clear().rows.add(details).draw()
            $('#modalEntry').modal('hide')
        })

        $('.cancel-entry').click(function() {
            $('#modalEntry').modal('hide')
            if (statusModal == 'create') {
                count -= 1
            }
        })

        $('body').on('click', '.edit-entry', function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })
            $('#message-stok').text('')

            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })
            let index = $(this).parents('tr').index()
            statusModal = 'edit'
            detailSelect = details[index]
            for (select in detailSelect) {
                if (['id_barang', 'id_satuan_barang'].includes(select)) {
                    let nameSelect = (select == 'id_barang') ? 'nama_barang' : 'nama_satuan_barang';
                    $('[name="' + select + '"]').append('<option value="' + detailSelect[select] +
                        '" selected>' +
                        detailSelect[nameSelect] + '</option>')
                }

                $('[name="' + select + '"]').val(detailSelect[select]).trigger('change')
                if (select == 'stok') {
                    $('#message-stok').text(formatRupiah(detailSelect[select], 4) + ' ' + detailSelect
                        .nama_satuan_barang)
                }
            }

            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
        })

        $('body').on('click', '.delete-entry', function() {
            let targetElement = $(this).parents('tr')
            Swal.fire({
                title: 'Anda yakin ingin menghapus data ini?',
                icon: 'info',
                showDenyButton: true,
                confirmButtonText: 'Yes',
                denyButtonText: 'No',
                reverseButtons: true,
                customClass: {
                    actions: 'my-actions',
                    confirmButton: 'order-1',
                    denyButton: 'order-3',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    details.splice(targetElement.index(), 1);

                    if (details[details.length - 1]) {
                        count = details[details.length - 1].index
                    } else {
                        count = 0
                    }

                    resDataTable.clear().rows.add(details).draw()
                    $('[name="details"]').val(JSON.stringify(details))
                }
            })
        })

        function validatorModal(id = 0) {
            let message = 'Lengkapi inputan yang diperlukan'
            let valid = true

            $('#modalEntry').find('.validate').each(function(i, v) {
                if ($(v).val() == '') {
                    valid = false
                }
            })

            let id_barang = $('#modalEntry').find('[name="id_barang"]').val();
            let notes = $('#modalEntry').find('[name="notes"]').val();

            let findItem = details.filter(p => p.id_barang == id_barang && p.notes == notes)
            if (findItem.length > 0 && findItem[0].id_barang == id && statusModal == 'create') {
                message = "Barang sudah ada"
                valid = false
            }

            return {
                'status': valid,
                'message': message
            }
        }

        $('body').on('click', '.edit-camera', function() {
            let index = $(this).data('index')
            let parent = $(this).data('parent')
            $('#cover-spin').show()
            $.ajax({
                url: urlShowImage + '?parent=' + parent + '&index=' + index,
                type: 'get',
                success: function(res) {
                    $('.add-image').prevAll('.item-media').remove()
                    let el = $('#modalCamera')
                    el.find('[name="index"]').val(index)
                    el.find('[name="purchase_request_id"]').val(parent)
                    el.find('[name="upload_base64"]').val(JSON.stringify(res.datas))
                    $('.add-image').before(res.html)
                    $('#cover-spin').hide()
                    el.modal()
                    Fancybox.bind('[data-fancybox="gallery"]');
                },
                error: function(error) {
                    Swal.fire("Gagal Menyimpan Data. ", error.responseJSON.message, 'error')
                    $('#cover-spin').hide()
                }
            })
        })

        $('body').on('click', '.remove-media-container', function(e) {
            e.preventDefault()
            let el = $(this).parents('.item-media')
            let id = $(this).data('id')
            let medias = JSON.parse($('[name="upload_base64"]').val())
            let removeMedias = JSON.parse($('[name="remove_base64"]').val())

            if (Number.isInteger(id)) {
                let index = medias.indexOf(id);
                if (index > -1) {
                    removeMedias.push(medias[index])
                    medias.splice(index, 1);
                    el.remove()
                }
            } else {
                let index = el.index()
                medias.splice(index, 1)
                el.remove()
            }

            $('[name="remove_base64"]').val(JSON.stringify(removeMedias))
            $('[name="upload_base64"]').val(JSON.stringify(medias))
        })

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

        $('.post-image').click(function() {
            postImage()
        })

        function postImage() {
            $('#cover-spin').show()
            let el = $('#modalCamera')
            $.ajax({
                url: urlPostImage,
                type: 'post',
                data: {
                    purchase_request_id: el.find('[name="purchase_request_id"]').val(),
                    index: el.find('[name="index"]').val(),
                    upload_base64: el.find('[name="upload_base64"]').val(),
                    remove_base64: el.find('[name="remove_base64"]').val()
                },
                success: function(data) {
                    el.modal('hide')
                    $('#cover-spin').hide()
                    el.find('[name="upload_base64"]').val('[]')
                    el.find('[name="remove_base64"]').val('[]')
                    Swal.fire('Tersimpan!', data.message, 'success')
                },
                error: function(error) {
                    $('#cover-spin').hide()
                    Swal.fire("Gagal Menyimpan Data. ", error.responseJSON.message, 'error')
                }
            })
        }
    </script>
@endsection
