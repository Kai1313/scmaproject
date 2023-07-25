@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/dropify@0.2.2/dist/css/dropify.min.css" rel="stylesheet">
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

        // now for the good stuff 🎉
        .range-slider {
            outline: 12;
            border: 0;
            border-radius: 500px;
            display: inline !important;
            width: 400px;
            max-width: 100%;
            margin: 24px 0;
            transition: box-shadow 0.2s ease-in-out;
        }

        /* @media screen and (-webkit-min-device-pixel-ratio: 0) {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        } */

        .range-slider::-moz-range-progress {
            background-color: #43e5f7;
        }

        .range-slider::-moz-range-track {
            background-color: #9a905d;
        }

        .range-slider::-ms-fill-lower {
            background-color: #43e5f7;
        }

        .range-slider::-ms-fill-upper {
            background-color: #9a905d;
        }

        h1.slider {
            color: #333;
            font-weight: 500;
        }

        h3.slider {
            color: #aaa;
            font-weight: 500;
        }

        h4.slider {
            color: #999;
            font-weight: 500;
        }

        h4.slider:after {
            content: "%";
            padding-left: 1px;
        }

        p.dropify-infos-message {
            font-size: 12px !important;
        }

        .is-invalid {
            border: 1px solid red !important;
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
            <li><a href="{{ route('pre_visit') }}">Kunjungan</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('kunjungan.reporting.store') }}" method="post" class="post-action">
            <div class="col-sm-6">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Detail Kunjungan</h3>
                        <a href="{{ route('pre_visit') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                            <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                        </a>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12" id="map">

                            </div>
                            <div class="col-md-6">
                                <label>Kode Kunjungan</label>
                                <div class="form-group">
                                    <input type="text" name="visit_code"
                                        value="{{ old('visit_code', $data ? $data->visit_code : '') }}" class="form-control"
                                        readonly placeholder="Otomatis">
                                </div>

                            </div>
                            <div class="col-sm-6 disabled">
                                <label>Tanggal <span>*</span></label>
                                <div class="form-group">
                                    <input readonly type="text" name="visit_date"
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
                            <div class="col-md-6 disabled">
                                <label>Salesman <span>*</span></label>
                                <div class="form-group">
                                    <select name="id_salesman" class="form-control select2 trigger-change">
                                        @foreach ($salesman as $item)
                                            <option {{ $data->id_salesman == $item->id_salesman ? 'selected' : '' }}
                                                value="{{ $item->id_salesman }}">{{ $item->nama_salesman }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label>Pelanggan</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" readonly id="pelanggan"
                                        value="{{ $data->pelanggan->nama_pelanggan }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label>Catatan</label>
                                <div class="form-group">
                                    <textarea name="pre_visit_desc" readonly class="form-control" rows="3">{{ old('pre_visit_desc', $data ? $data->pre_visit_desc : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="box" id="form-report">
                    <div class="box-header">
                        <h3 class="box-title">Catatan Kunjungan</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12 parent">
                                <div class="form-group">
                                    <label>Issue <span>*</span></label>
                                    <input type="text" class="form-control required"
                                        placeholder="ex:Pengembangan customer" id="visit_title">
                                </div>
                            </div>
                            <div class="col-md-12 parent">
                                <div class="form-group">
                                    <label>Progress Indicator <span>*</span></label>
                                    <select name="progress_ind" id="progress_ind"
                                        class="form-control select2 trigger-change required">
                                        <option value="">Pilih Progress</option>
                                        @foreach (App\Visit::$progressIndicator as $key => $item)
                                            <option value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 sections section-2 parent">
                                <div class="form-group">
                                    <label>Range Potensial <span>*</span></label>
                                    <input type="range" value="0" min="0" max="100" step="1"
                                        class="range-slider required">
                                    <h4 class="slider">0</h4>
                                </div>
                            </div>
                            <div class="col-md-12 sections section-3 parent">
                                <div class="form-group">
                                    <label>Nomor Sales Order</label>
                                    <select name="sales_order_id" id="sales_order_id" class="select2 required"></select>
                                </div>
                            </div>
                            <div class="col-sm-12 sections section-3 parent">
                                <div class="form-group">
                                    <label>Total Order</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <select name="kurs_id" id="kurs_id">
                                                <option value="RP">RP</option>
                                                <option value="USD">USD</option>
                                            </select>
                                        </span>
                                        <input type="text" name="total" placeholder="xxx,xxx,xxx" id="total"
                                            class="form-control text-right required">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 parent">
                                <div class="form-group">
                                    <label>Catatan <span>*</span></label>
                                    <textarea name="visit_desc" id="visit_desc" class="form-control required"
                                        placeholder="ex:pernah membeli di PT SCMA"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-6 parent">
                                <div class="form-group">
                                    <label>Gambar 1 <span>*</span></label>
                                    <input type="file" class="dropify required"
                                        data-allowed-file-extensions="pdf png jpg" data-max-file-size-preview="3M">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Gambar 2</label>
                                    <input type="file" class="dropify" data-allowed-file-extensions="pdf png jpg"
                                        data-max-file-size-preview="3M">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary w-full mb-3" onclick="sendReport()">SEND
                                    REPORT</button>
                            </div>
                        </div>
                    </div>
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

        <div class="modal fade" id="modal-reason-cancel" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="">Alasan Pembatalan</label>
                                <textarea class="form-control" name="alasan_pembatalan" id="alasan_pembatalan" data-validation="[NOTEMPTY]"
                                    data-validation-message="Alasan Pembatalan Tidak Boleh Kosong"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cancel-entry btn-flat">Batal</button>

                        <button type="button" class="btn btn-primary btn-flat" onclick="cancelData()">Konfirmasi
                            Pembatalan</button>
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
    <script src="https://cdn.jsdelivr.net/npm/jquery-maskmoney@3.0.2/dist/jquery.maskMoney.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/accounting-js@1.1.1/dist/accounting.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dropify@0.2.2/dist/js/dropify.min.js"></script>
@endsection

@section('externalScripts')
    <script>
        $('.sections').addClass('hidden');
        let branch = {!! json_encode($cabang) !!}
        let timbangan = '';
        let details = [];
        let detailSelect = []
        let count = details.length
        let statusModal = 'create'
        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });

        var range = '{{ $range ? $range->value2 : null }}';

        let locationPelanggan = {
            latitude: '{{ $data->pelanggan->latitude_pelanggan }}' * 1,
            longitude: '{{ $data->pelanggan->longitude_pelanggan }}' * 1,
        };

        let locationUser = {};
        $('#total').maskMoney({
            precision: 0,
            defaultZero: true,
            allowZero: true,
        })

        $('.dropify').dropify({
            messages: {
                'default': '',
                'replace': 'Drag and drop or click to replace',
                'remove': 'Remove',
                'error': 'Oops.'
            }
        });

        $(function() {
            var rangePercent = $('[type="range"]').val();
            $('[type="range"]').on('change input', function() {
                rangePercent = $('[type="range"]').val();
                $('h4.slider').text(rangePercent);
                $('[type="range"]').css('filter', 'hue-rotate(-' + rangePercent + 'deg)');
            });
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

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

        $(document).ready(function() {
            appendMap('{{ $data->pelanggan->latitude_pelanggan }}',
                '{{ $data->pelanggan->longitude_pelanggan }}');
        })

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

        function openReasonCancelModal() {
            $("#modal-reason-cancel").modal('toggle');
        }

        $("#sales_order_id").select2({
            width: '100%',
            ajax: {
                url: "{{ route('kunjungan.reporting.select') }}?param=sales_order_id",
                dataType: 'json',
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                },
                cache: true,
                type: 'GET',
            },
            placeholder: 'Pilih Sales Order',
            minimumInputLength: 0,
            templateResult: formatRepoNormal,
            templateSelection: formatRepoNormalSelection
        });

        $('#sales_order_id').on('select2:select', function(event) {
            console.log(event);
            var data = event.params.data;
            $('#total').val(formatNumber(data.mtotal_permintaan_penjualan, {
                precision: 0,
            }));
        })

        function formatRepoNormalSelection(repo) {
            return repo.text || repo.text;
        }

        function formatRepoNormal(repo) {
            if (repo.loading) {
                return repo.text;
            }
            // scrolling can be used
            var markup = $('<span  data-name=' + repo.name + ' value=' + repo.id + '>' + repo.text + '</span>');
            return markup;
        }

        $('#progress_ind').change(function() {
            $('.sections').addClass('hidden');
            $(`.section-${$(this).val()}`).removeClass('hidden');
        })

        $('input,textarea').keyup(function() {
            $(this).removeClass('is-invalid');
        })

        $('select').change(function() {
            var par = $(this).parents('.parent');
            par
            $(par).find('.select2-container').removeClass('is-invalid');
        })


        function validating() {
            var validation = 0;
            $('#form-report .required').each(function() {
                var par = $(this).parents('.parent');
                if (!$(par).hasClass('hidden')) {
                    if ($(this).val() == '' || $(this).val() == null) {
                        $(this).addClass('is-invalid');
                        $(par).find('.select2-container').addClass('is-invalid');
                        $(par).find('.dropify-wrapper').addClass('is-invalid');
                        validation++;
                    }
                }
            })

            return validation;
        }

        function sendReport() {
            var validation = validating();

            if (validation != 0) {
                return Swal.fire("Cek data anda lagi.", 'Pastikan semua data telah terinput', 'error')
            }

            Swal.fire({
                title: 'Anda yakin ingin mngirim report visit anda?',
                text: 'Aksi ini tidak bisa dikembalikan',
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
                    $('#cover-spin').show()
                    $.ajax({
                        url: '{{ route('kunjungan.reporting.store') }}',
                        type: "post",
                        data: {

                        },
                        dataType: "JSON",
                        success: function(data) {
                            $('#cover-spin').hide()
                            if (data.result) {
                                Swal.fire('Berhasil!', data.message, 'success').then((result) => {
                                    location.href = data.url;
                                })
                            } else {
                                Swal.fire("Gagal Proses Data.", data.message, 'error')
                            }
                        },
                        error: function(data) {
                            $('#cover-spin').hide()
                            Swal.fire("Gagal Proses Data.", data.responseJSON.message, 'error')
                        }
                    })
                }

            })
        }
    </script>
@endsection
