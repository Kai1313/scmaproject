@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
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
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Pemakaian
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('material_usage') }}">Pemakaian</a></li>
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

        <form action="{{ route('material_usage-save-entry', $data ? $data->id_pemakaian : 0) }}" method="post"
            class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Pemakaian</h3>
                    <a href="{{ route('material_usage') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
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
                        </div>
                        <div class="col-md-4">
                            <label>Kode Pemakaian</label>
                            <div class="form-group">
                                <input type="text" name="kode_pemakaian"
                                    value="{{ old('kode_pemakaian', $data ? $data->kode_pemakaian : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                            <label>Tanggal <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="tanggal"
                                    value="{{ old('tanggal', $data ? $data->tanggal : date('Y-m-d')) }}"
                                    class="form-control datepicker" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal tidak boleh kosong">
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    <h3 class="box-title">Detail Barang</h3>
                    <button class="btn btn-info add-entry btn-flat pull-right btn-sm" type="button">
                        <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                    </button>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <input type="hidden" name="details" value="{{ $data ? json_encode($data->formatdetail) : '[]' }}">
                        <table id="table-detail" class="table table-bordered data-table display responsive nowrap"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Gross</th>
                                    <th>Jumlah Zak</th>
                                    <th>Tare</th>
                                    <th>Nett</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary btn-flat pull-right btn-sm" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display:none;" id="alertModal">
                        </div>
                        <input type="hidden" name="index" value="0">
                        {{-- <input type="hidden" name="id_pemakaian_detail" value="0"> --}}
                        <div id="reader"></div>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" name="search-qrcode" class="form-control"
                                    placeholder="Scan QRCode" autocomplete="off">
                                <div class="input-group-btn">
                                    <button class="btn btn-info btn-search btn-flat" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <label>QR Code</label>
                        <div class="form-group">
                            <input type="text" name="kode_batang" class="validate form-control" readonly>
                        </div>
                        <label>Nama Barang</label>
                        <div class="form-group">
                            <input type="text" name="nama_barang" class="validate form-control" readonly>
                            <input type="hidden" name="id_barang" class="validate">
                        </div>
                        <label>Satuan</label>
                        <div class="form-group">
                            <input type="text" name="nama_satuan_barang" class="validate form-control" readonly>
                            <input type="hidden" name="id_satuan_barang" class="validate">
                        </div>
                        <label>Jumlah Zak <span>*</span></label>
                        <div class="form-group">
                            <input type="text" name="jumlah_zak" class="form-control validate handle-number-4"
                                autocomplete="off">
                            <input type="hidden" name="weight_zak" class="validate">
                            <input type="hidden" name="wrapper_weight" class="validate">
                        </div>
                        <label id="label-timbangan">Timbangan</label>
                        <div class="form-group">
                            <select name="id_timbangan" class="form-control select2">
                            </select>
                        </div>
                        <label id="label-berat">Berat Barang</label>
                        <div class="form-group">
                            <input type="text" name="jumlah" class="form-control handle-number-4"
                                autocomplete="off">
                            <input type="hidden" name="max_weight" class="validate">
                            <label id="alertWeight" style="display:none;color:red;"></label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cancel-entry btn-flat">Batal</button>
                        <button type="button" class="btn btn-primary save-entry btn-flat">Simpan</button>
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
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branch = {!! json_encode($cabang) !!}
        let timbangan = {!! $timbangan !!}
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        console.log(details)
        let detailSelect = []
        let count = details.length
        let statusModal = 'create'
        let intervalReloadTimbangan = ''
        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        $('.select2').select2()
        var resDataTable = $('#table-detail').DataTable({
            data: details,
            ordering: false,
            columns: [{
                data: 'kode_batang',
                name: 'kode_batang'
            }, {
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang'
            }, {
                data: 'jumlah',
                name: 'jumlah',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'jumlah_zak',
                name: 'jumlah_zak',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'tare',
                name: 'tare',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'nett',
                name: 'nett',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'index',
                className: 'text-center',
                name: 'index',
                searchable: false,
                render: function(data, type, row, meta) {
                    let btn = ''
                    if (!row.hasOwnProperty('id_pemakaian')) {
                        btn += '<ul class="horizontal-list">';
                        // btn +=
                        //     '<li><a href="javascript:void(0)" data-index="' + data +
                        //     '" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a></li>';
                        btn +=
                            '<li><a href="javascript:void(0)" data-index="' + data +
                            '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a></li>';
                        btn += '</ul>';
                    }

                    return btn;
                }
            }, ],
            initComplete: function(settings, json) {
                sumDetail()
            },
            drawCallback: function(settings) {
                sumDetail()
            }
        });

        function sumDetail() {
            let totalJumlah = 0;
            let totalJumlahZak = 0;
            let totalTare = 0;
            let totalNett = 0;
            for (let i = 0; i < details.length; i++) {
                totalJumlah += parseFloat(details[i].jumlah)
                totalJumlahZak += parseFloat(details[i].jumlah_zak)
                totalTare += parseFloat(details[i].tare)
                totalNett += parseFloat(details[i].nett)
            }

            $('#table-detail').find('tfoot').remove()
            $('#table-detail tbody').after(
                '<tfoot><tr>' +
                '<td colspan="3" class="text-left"><b>Total</b></td>' +
                '<td class="text-right">' + formatNumber(totalJumlah, 4) + '</td>' +
                '<td class="text-right">' + formatNumber(totalJumlahZak, 4) + '</td>' +
                '<td class="text-right">' + formatNumber(totalTare, 4) + '</td>' +
                '<td class="text-right">' + formatNumber(totalNett, 4) + '</td>' +
                '<td></td>' +
                '</tr></tfoot>'
            );
        }

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

        $('[name="id_timbangan"]').select2({
            data: timbangan
        }).on('select2:select', function(e) {
            let dataselect = e.params.data
            let beratTimbangan = dataselect.value
            let beratMax = $('[name="max_weight"]').val()
            if (parseFloat(beratTimbangan) > parseFloat(beratMax)) {
                $('#alertWeight').text('Berat melebihi stok').show()
            } else {
                $('#alertWeight').text('').hide()
            }

            intervalReloadTimbangan = setInterval(reloadTimbangan, 2000)
            $('#modalEntry').find('[name="jumlah"]').val(formatNumber(dataselect.value, 4))
        })

        function reloadTimbangan() {
            $.ajax({
                url: '{{ route('material_usage-reload-weight') }}',
                data: {
                    id: $('[name="id_timbangan"]').val()
                },
                success: function(res) {
                    $('#modalEntry').find('[name="jumlah"]').val(formatNumber(res.data, 4))
                },
                error: function(error) {
                    console.log(error)
                }
            })
        }

        $('.add-entry').click(function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })

            $('#alertModal').text('').hide()
            statusModal = 'create'
            count += 1
            $('#modalEntry').find('[name="index"]').val(count)
            // $('#modalEntry').find('[name="id_pemakaian_detail"]').val(0)
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })

            $('#message-stok').text('')
            $('#label-timbangan').html('Timbangan')
            $('#label-berat').html('Berat Barang')
            $('[name="jumlah_zak"]').val(0)
            $('[name="weight_zak"]').val(0)
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        })

        $('#modalEntry').on('input', '[name="jumlah_zak"]', function() {
            let weightWrapper = $('[name="wrapper_weight"]').val()
            let jumlah = $(this).val()
            $('[name="weight_zak"]').val(jumlah * weightWrapper)
        })

        $('.save-entry').click(function() {
            let modal = $('#modalEntry')
            let valid = validatorModal(modal.find('[name="kode_batang"]').val())
            if (!valid.status) {
                $('#alertModal').text(valid.message).show()
                return false
            } else {
                $('#alertModal').text('').hide()
            }

            modal.find('input,select').each(function(i, v) {
                if ($(v).hasClass('handle-number-4')) {
                    detailSelect[$(v).prop('name')] = normalizeNumber($(v).val())
                } else {
                    detailSelect[$(v).prop('name')] = $(v).val()
                }
            })

            detailSelect['tare'] = detailSelect['weight_zak']
            detailSelect['nett'] = detailSelect['jumlah'] - detailSelect['tare']

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
            stopInterval()
            console.log('lalala')
            $('#modalEntry').modal('hide')
        })

        $('.cancel-entry').click(function() {
            html5QrcodeScanner.clear();
            stopInterval()
            if (statusModal == 'create') {
                count -= 1
            }

            $('#modalEntry').modal('hide')
        })

        // $('body').on('click', '.edit-entry', function() {
        //     detailSelect = []
        //     $('#modalEntry').find('input,select,textarea').each(function(i, v) {
        //         $(v).val('').trigger('change')
        //     })
        //     $('#message-stok').text('')

        //     $('#alertModal').text('').hide()
        //     $('#modalEntry').modal({
        //         backdrop: 'static',
        //         keyboard: false
        //     })
        //     let index = $(this).data('index')
        //     statusModal = 'edit'
        //     detailSelect = details[index - 1]
        //     for (select in detailSelect) {
        //         if (['id_barang', 'id_satuan_barang'].includes(select)) {
        //             let nameSelect = (select == 'id_barang') ? 'nama_barang' : 'nama_satuan_barang';
        //             $('[name="' + select + '"]').append('<option value="' + detailSelect[select] + '" selected>' +
        //                 detailSelect[nameSelect] + '</option>')
        //         }

        //         $('[name="' + select + '"]').val(detailSelect[select]).trigger('change')
        //         if (select == 'stok') {
        //             $('#message-stok').text(formatRupiah(detailSelect[select], 4) + ' ' + detailSelect
        //                 .nama_satuan_barang)
        //         }
        //     }

        //     $('.handle-number-4').each(function(i, v) {
        //         let val = $(v).val().replace('.', ',')
        //         $(v).val(formatRupiah(val, 4))
        //     })
        // })

        $('body').on('click', '.delete-entry', function() {
            let index = $(this).parents('tr').index()
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
                    details.splice(index, 1)
                    count -= 1

                    for (let i = 0; i < details.length; i++) {
                        details[i].index = i + 1
                    }

                    resDataTable.clear().rows.add(details).draw()
                    $('[name="details"]').val(JSON.stringify(details))
                }
            })
        })

        $('.btn-search').click(function() {
            let self = $('[name="search-qrcode"]').val()
            html5QrcodeScanner.clear();
            searchAsset(self)
        })

        function searchAsset(string) {
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('material_usage-qrcode') }}',
                type: 'get',
                data: {
                    qrcode: string,
                    id_cabang: $('[name="id_cabang"]').val(),
                    id_gudang: $('[name="id_gudang"]').val()
                },
                success: function(res) {
                    let modal = $('#modalEntry')
                    let data = res.data
                    for (let key in data) {
                        modal.find('[name="' + key + '"]').val(data[key])
                    }

                    modal.find('[name="max_weight"]').val(data.sisa_master_qr_code)
                    if (data.isweighed == 1) {
                        modal.find('[name="jumlah"]').prop('readonly', true).removeClass('validate')
                        modal.find('[name="id_timbangan"]').prop('disabled', false).addClass('validate')
                        $('#label-timbangan').html('Timbangan <span>*</span>')
                    } else {
                        modal.find('[name="jumlah"]').prop('readonly', false).addClass('validate')
                        modal.find('[name="id_timbangan"]').prop('disabled', true).removeClass('validate')
                        $('#label-berat').html('Berat barang <span>*</span>')
                    }

                    $('#cover-spin').hide()
                },
                error: function(error) {
                    let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error
                        .statusText
                    Swal.fire("Gagal Mengambil Data. ", textError, 'error')
                    html5QrcodeScanner.render(onScanSuccess, onScanError);
                    $('[name="search-qrcode"]').val('')
                    $('#cover-spin').hide()
                }
            })
        }

        function validatorModal(id = 0) {
            let message = 'Lengkapi inputan yang diperlukan'
            let valid = true
            $('#modalEntry').find('.validate').each(function(i, v) {
                if ($(v).val() == '') {
                    valid = false
                }

                if ($(v).prop('name') == 'kode_batang') {
                    let findItem = details.filter(p => p.kode_batang == $(v).val())
                    if (findItem.length > 0 && findItem[0].kode_batang == id && statusModal == 'create') {
                        message = "QR Code sudah ada"
                        valid = false
                    }
                }
            })

            return {
                'status': valid,
                'message': message
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            // audiobarcode.play();
            $('[name="search-qrcode"]').val(decodedText)
            $('.btn-search').click()
        }

        function onScanError(errorMessage) {
            toastr.error(JSON.strignify(errorMessage))
        }

        function stopInterval() {
            clearInterval(intervalReloadTimbangan);
        }
    </script>
@endsection
