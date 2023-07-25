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
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Jadwal Kunjungan
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('pre_visit') }}">Jadwal Kunjungan</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('pre_visit-save-entry', $data ? $data->id : 0) }}" method="post" class="post-action">
            <div class="col-sm-6">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Jadwal Kunjungan</h3>
                        <a href="{{ route('pre_visit') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                            <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                        </a>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Kode Kunjungan</label>
                                <div class="form-group">
                                    <input type="text" name="visit_code"
                                        value="{{ old('visit_code', $data ? $data->visit_code : '') }}" class="form-control"
                                        readonly placeholder="Otomatis">
                                </div>

                            </div>
                            <div class="col-sm-6">
                                <label>Tanggal <span>*</span></label>
                                <div class="form-group">
                                    <input type="text" name="visit_date"
                                        value="{{ old('visit_date', $data ? $data->visit_date : date('Y-m-d')) }}"
                                        class="form-control datepicker" data-validation="[NOTEMPTY]"
                                        data-validation-message="Tanggal tidak boleh kosong">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cabang <span>*</span></label>
                                    <select name="id_cabang" class="form-control select2" data-validation="[NOTEMPTY]"
                                        data-validation-message="Cabang tidak boleh kosong" {{ $data ? 'readonly' : '' }}>
                                        <option value="">Pilih Cabang</option>
                                        @if ($data && $data->id_cabang)
                                            <option value="{{ $data->id_cabang }}" selected>
                                                {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Salesman <span>*</span></label>
                                <div class="form-group">
                                    <select name="id_salesman" class="form-control select2 trigger-change">
                                        @foreach ($salesman as $item)
                                            <option value="{{ $item->id_salesman }}">{{ $item->nama_salesman }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label>Pelanggan <span>*</span>&nbsp;&nbsp;<a href="javascript:;"
                                        onclick="window.open(`https://vps.scasda.my.id//v2/#pelanggan`)">Tambah
                                        Pelanggan</a></label>
                                <div class="form-group">
                                    <select name="id_pelanggan" class="form-control select2 trigger-change"
                                        id="id_pelanggan">
                                        @foreach ($pelanggan as $item)
                                            <option data-latitude="{{ $item->latitude_pelanggan }}"
                                                data-longitude="{{ $item->longitude_pelanggan }}"
                                                value="{{ $item->id_pelanggan }}">{{ $item->nama_pelanggan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Latitude</label>
                                <div class="form-group">
                                    <input type="text" readonly name="latitude" id="latitude" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Longitude</label>
                                <div class="form-group">
                                    <input type="text" readonly name="longitude" id="longitude" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label>Catatan</label>
                                <div class="form-group">
                                    <textarea name="pre_visit_desc" class="form-control" rows="3">{{ old('pre_visit_desc', $data ? $data->pre_visit_desc : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button class="btn btn-primary btn-flat pull-right" type="submit">
                            <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 map hidden" id="map">

                <br />
            </div>
        </form>
        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display:none;" id="alertModal">
                        </div>
                        <input type="hidden" name="index" value="0">
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
                        <div class="result-form" style="display:none;">
                            <label>QR Code <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="kode_batang" class="validate form-control" readonly>
                            </div>
                            <label>Nama Barang <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="nama_barang" class="validate form-control" readonly>
                                <input type="hidden" name="id_barang" class="validate">
                            </div>
                            <label>Satuan <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="nama_satuan_barang" class="validate form-control" readonly>
                                <input type="hidden" name="id_satuan_barang" class="validate">
                            </div>
                            <label id="label-jumlah-zak">Jumlah Zak</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" name="jumlah_zak" class="form-control validate handle-number-4"
                                        autocomplete="off">
                                    <span class="input-group-addon" id="max-jumlah-zak"></span>
                                </div>
                                <label id="alertZak" style="display:none;color:red;"></label>
                                <input type="hidden" name="weight_zak" class="validate">
                                <input type="hidden" name="wrapper_weight" class="validate">
                            </div>
                            <label id="label-timbangan">Timbangan</label>
                            <div class="form-group">
                                <select name="id_timbangan" class="form-control select2">
                                    <option value="">Pilih Timbangan</option>
                                </select>
                            </div>
                            <label id="label-berat">Jumlah</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" name="jumlah" class="form-control handle-number-4"
                                        autocomplete="off">
                                    <span class="input-group-addon" id="max-jumlah"></span>
                                    <div class="input-group-btn">
                                        <a href="javascript:void(0)" class="btn btn-warning reload-timbangan">
                                            <i class="glyphicon glyphicon-refresh"></i>
                                        </a>
                                    </div>
                                </div>
                                <input type="hidden" name="max_weight" class="validate">
                                <label id="alertWeight" style="display:none;color:red;"></label>
                            </div>
                            <label>Catatan</label>
                            <div class="form-group">
                                <textarea name="catatan" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cancel-entry btn-flat">Batal</button>

                        <button type="button" class="btn btn-primary save-entry btn-flat result-form"
                            style="display:none;">Simpan</button>
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
        let timbangan = '';
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let detailSelect = []
        let count = details.length
        let statusModal = 'create'
        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        $("#id_pelanggan").change(function() {
            var lat = $(this).find('option:selected').data('latitude');
            var long = $(this).find('option:selected').data('longitude');
            if (lat != '' && long != '') {
                console.log(lat, long);
                $('#map').removeClass('hidden');
                $("#google-map").prop('src',
                    `https://maps.google.com/maps?q=${lat},${long}&hl=id&z=14&amp;output=embed`)
                appendMap(lat, long);
                $('#latitude').val(lat);
                $('#longitude').val(long);
            } else {
                $('#map').addClass('hidden');
                $('#latitude').val('');
                $('#longitude').val('');
            }
        })

        $('.select2').select2()
        var resDataTable = $('#table-detail').DataTable({
            paging: false,
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
                data: 'catatan',
                name: 'catatan',
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
                '<td></td><td></td>' +
                '</tr></tfoot>'
            );
        }

        $('[name="id_cabang"]').select2({
            data: [{
                'id': '',
                'text': 'Pilih Cabang'
            }, ...branch]
        }).on('select2:select', function(e) {
            let dataselect = e.params.data
            getGudang(dataselect)
        });

        function getGudang(data) {
            $('[name="id_gudang"]').empty()
            $('[name="id_gudang"]').select2({
                data: [{
                    'id': "",
                    'text': 'Pilih Gudang'
                }, ...data.gudang]
            })
        }

        $('[name="id_timbangan"]').select2({
            data: timbangan
        })

        $('#modalEntry').on('input', '[name="jumlah"]', function() {
            if (normalizeNumber($(this).val()) > detailSelect.sisa_master_qr_code) {
                $(this).addClass('error-field')
                $('#alertWeight').text('Berat melebihi stok').show()
            } else {
                $(this).removeClass('error-field')
                $('#alertWeight').text('').hide()
            }
        })

        $('.reload-timbangan').click(function() {
            reloadTimbangan()
        })

        function reloadTimbangan() {
            $.ajax({
                url: '{{ route('material_usage-reload-weight') }}',
                data: {
                    id: $('[name="id_timbangan"]').val()
                },
                success: function(res) {
                    let beratTimbangan = res.data
                    let beratMax = $('[name="max_weight"]').val()
                    if (parseFloat(beratTimbangan) > parseFloat(beratMax)) {
                        $('[name="jumlah"]').addClass('error-field')
                        $('#alertWeight').text('Berat melebihi stok').show()
                    } else {
                        $('[name="jumlah"]').removeClass('error-field')
                        $('#alertWeight').text('').hide()
                    }

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
                $(v).removeClass('error-field')
            })

            $('#alertWeight').text('').hide()
            $('#max-jumlah').text('')
            $('#max-jumlah-zak').text('')
            statusModal = 'create'
            count += 1
            $('#modalEntry').find('[name="index"]').val(count)
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })

            $('#message-stok').text('').hide()
            $('#alertZak').text('').hide()
            $('#label-timbangan').html('Timbangan')
            $('#label-berat').html('Berat Barang')
            $('#label-jumlah-zak').html('Jumlah Zak')
            $('[name="jumlah_zak"]').val(0)
            $('[name="weight_zak"]').val(0)
            $('.result-form').hide()
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        })

        $('#modalEntry').on('input', '[name="jumlah_zak"]', function() {
            let weightWrapper = $('[name="wrapper_weight"]').val()
            let jumlahZak = normalizeNumber($(this).val() ? $(this).val() : '0')
            let weightZak = jumlahZak * weightWrapper
            if (jumlahZak > detailSelect.jumlah_zak) {
                $('#alertZak').text('Jumlah melebihi maksimal').show()
                $(this).addClass('error-field')
            } else {
                $('#alertZak').text('').hide()
                $(this).removeClass('error-field')
            }

            $('[name="jumlah_zak"]').val(jumlahZak)
            $('[name="weight_zak"]').val(weightZak)
        })

        $('.save-entry').click(function() {
            let modal = $('#modalEntry')
            let valid = validatorModal(modal.find('[name="kode_batang"]').val())
            if (!valid.status) {
                Swal.fire("Gagal Menyimpan Data. ", valid.message, 'error')
                return false
            }

            detailSelect = []
            modal.find('input,select,textarea').each(function(i, v) {
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
            console.log(details)

            statusModal = ''
            detailSelect = []

            resDataTable.clear().rows.add(details).draw()
            $('#modalEntry').modal('hide')
        })

        $('.cancel-entry').click(function() {
            html5QrcodeScanner.clear();
            if (statusModal == 'create') {
                count -= 1
            }

            $('#modalEntry').modal('hide')
        })

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

        $('[name="is_qc"]').click(function() {
            details = []
            resDataTable.clear().rows.add(details).draw()
        })

        function searchAsset(string) {
            $('#cover-spin').show()
            let isQc = 0
            if ($('[name="is_qc"]').is(':checked')) {
                isQc = $('[name="is_qc"]').val()
            }

            $.ajax({
                url: '{{ route('material_usage-qrcode') }}',
                type: 'get',
                data: {
                    qrcode: string,
                    id_cabang: $('[name="id_cabang"]').val(),
                    id_gudang: $('[name="id_gudang"]').val(),
                    is_qc: isQc,
                },
                success: function(res) {
                    detailSelect = res.data
                    let modal = $('#modalEntry')
                    let data = res.data
                    for (let key in data) {
                        modal.find('[name="' + key + '"]').val(data[key])

                        if (['weight_zak', 'wrapper_weight'].includes(key) && data[key] == null) {
                            modal.find('[name="' + key + '"]').val(0)
                        }
                    }

                    $('#max-jumlah').text('Max ' + formatNumber(data.sisa_master_qr_code))

                    modal.find('[name="max_weight"]').val(data.sisa_master_qr_code)
                    if (data.jumlah_zak == null) {
                        modal.find('[name="jumlah_zak"]').prop('readonly', true).removeClass('validate')
                    } else {
                        $('#label-jumlah-zak').html('Jumlah Zak <span>*</span>')
                        $('#max-jumlah-zak').text('Max ' + formatNumber(data.jumlah_zak, 4))
                        modal.find('[name="jumlah_zak"]').prop('readonly', false).addClass('validate')
                    }

                    $('#label-berat').html('Berat Barang <span>*</span>')
                    if (data.isweighed == 1) {
                        modal.find('[name="jumlah"]').prop('readonly', true).addClass('validate')
                        modal.find('[name="id_timbangan"]').prop('disabled', false).addClass('validate')
                        $('#label-timbangan').html('Timbangan <span>*</span>')
                        $('.reload-timbangan').show()
                    } else {
                        modal.find('[name="jumlah"]').prop('readonly', false).addClass('validate')
                        modal.find('[name="id_timbangan"]').prop('disabled', true).removeClass('validate')
                        $('.reload-timbangan').hide()
                    }

                    $('.result-form').show()
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error
                        .statusText
                    Swal.fire("Gagal Mengambil Data. ", textError, 'error')
                    html5QrcodeScanner.render(onScanSuccess, onScanError);
                    $('[name="search-qrcode"]').val('')
                    $('#cover-spin').hide()
                    $('.result-form').hide()
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

                if ($('[name="jumlah"]').val() == '0') {
                    message = "Jumlah harus lebih dari 0"
                    valid = false
                }

                // if ($(v).prop('name') == 'kode_batang') {
                //     let findItem = details.filter(p => p.kode_batang == $(v).val())
                //     if (findItem.length > 0 && findItem[0].kode_batang == id && statusModal == 'create') {
                //         message = "QR Code sudah ada"
                //         valid = false
                //     }
                // }

                if ($(v).hasClass('error-field')) {
                    valid = false
                    message = "Jumlah melebihi batas maksimal"
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

        function appendMap(latitude, longitude) {
            $.ajax({
                url: '{{ route('append-map') }}',
                data: {
                    latitude: latitude,
                    longitude: longitude,
                },
                success: function(response) {
                    $('#map').html(response);
                }
            });
        }
    </script>
@endsection
