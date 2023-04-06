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
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Terima Dari Gudang
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Terima Dari Gudang</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('received_from_branch-save-entry', $data ? $data->id_pindah_barang : 0) }}" method="post"
            class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Terima Dari Gudang</h3>
                    <a href="{{ route('received_from_warehouse') }}"
                        class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <center>
                                <div id="reader"></div>
                            </center>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" name="search-qrcode" class="form-control"
                                        placeholder="Scan QRCode">
                                    <div class="input-group-btn">
                                        <button class="btn btn-info btn-search btn-flat" type="button">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Tanggal Penerimaan <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="tanggal_pindah_gudang"
                                    value="{{ old('tanggal_pindah_gudang', $data ? $data->tanggal_pindah_gudang : date('Y-m-d')) }}"
                                    class="form-control datepicker" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal penerimaan tidak boleh kosong" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Referensi Kode Pindah Cabang</label>
                            <div class="form-group">
                                <input type="text" name="referensi_id_pindah_gudang" class="form-control" readonly>
                                <input type="hidden" name="id_pindah_gudang2"
                                    value="{{ old('id_pindah_gudang2', $data ? $data->id_pindah_gudang2 : '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Kode Pindah Barang</label>
                            <div class="form-group">
                                <input type="text" name="nama_pindah_gudang"
                                    value="{{ old('nama_pindah_gudang', $data ? $data->nama_pindah_gudang : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Detil Barang</h4>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <input type="hidden" name="details" value="[]">
                        <table id="table-detail" class="table table-bordered data-table display responsive nowrap"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>QR Code</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Batch</th>
                                    <th>Kadaluarsa</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary btn-flat pull-right" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        // let branches = {!! $cabang !!};
        // let oldDetails = {!! $data && $data->parent ? $data->parent->formatdetail : '[]' !!};
        // let arrayQRCode = {!! $data ? $data->getDetailQRCode->pluck('qr_code') : '[]' !!};
        let details = []
        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });

        // for (let i = 0; i < oldDetails.length; i++) {
        //     details.push(oldDetails[i])
        //     if (arrayQRCode.includes(oldDetails[i]['qr_code'])) {
        //         details[i]['status_diterima'] = 1
        //     } else {
        //         details[i]['id_pindah_barang_detail'] = 0
        //         details[i]['status_diterima'] = 0
        //     }
        // }

        // $('[name="details"]').val(JSON.stringify(details))

        var resDataTable = $('#table-detail').DataTable({
            data: details,
            ordering: false,
            columns: [{
                data: 'kode_batang_lama_pindah_gudang_detail',
                name: 'qr_code'
            }, {
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang'
            }, {
                data: 'jumlah_pindah_gudang_detail',
                name: 'jumlah_pindah_gudang_detail',
                render: $.fn.dataTable.render.number('.', ',', 4),
                className: 'text-right'
            }, {
                data: 'batch_pindah_gudang_detail',
                name: 'batch_pindah_gudang_detail',
                className: 'text-right'
            }, {
                data: 'tanggal_kadaluarsa_pindah_gudang_detail',
                name: 'tanggal_kadaluarsa_pindah_gudang_detail',
            }, {
                data: 'id_pindah_gudang_detail',
                className: 'text-center',
                name: 'id_pindah_gudang_detail',
                searchable: false,
                render: function(data, type, row, meta) {
                    let btn = '';
                    if (row.status_diterima == 1) {
                        if (arrayQRCode.includes(row.qr_code)) {
                            btn = '<i class="fa fa-check" aria-hidden="true"></i>';
                        } else {
                            btn = '<input name="checked_data" type="checkbox" checked>';
                        }
                    } else {
                        btn = '<input name="checked_data" type="checkbox">';
                    }

                    return btn;
                }
            }, ]
        });

        // $('.select2').select2()
        // $('.datepicker').datepicker({
        //     format: 'yyyy-mm-dd',
        // });

        // $('[name="id_cabang"]').select2({
        //     data: [{
        //         'id': '',
        //         'text': 'Pilih Cabang'
        //     }, ...branches]
        // }).on('select2:select', function(e) {
        //     let dataselect = e.params.data
        //     getGudang(dataselect.id)
        //     getCodePindahGudang(dataselect.id)
        // });

        // function getGudang(idCabang) {
        //     $('#cover-spin').show()
        //     $.ajax({
        //         url: '{{ route('purchase-request-auto-werehouse') }}',
        //         data: {
        //             cabang: idCabang
        //         },
        //         success: function(res) {
        //             $('[name="id_gudang"]').empty()
        //             $('[name="id_gudang"]').select2({
        //                 data: [{
        //                     'id': "",
        //                     'text': 'Pilih Gudang'
        //                 }, ...res.data]
        //             })

        //             $('#cover-spin').hide()
        //         },
        //         error: function(error) {
        //             console.log(error)
        //             $('#cover-spin').hide()
        //         }
        //     })
        // }

        // function getCodePindahGudang(idCabang) {
        //     $('#cover-spin').show()
        //     $.ajax({
        //         url: '{{ route('received_from_branch-code') }}',
        //         data: {
        //             cabang: idCabang
        //         },
        //         success: function(res) {
        //             $('[name="kode_pindah_barang2"]').empty()
        //             $('[name="kode_pindah_barang2"]').select2({
        //                 data: [{
        //                     'id': "",
        //                     'text': 'Pilih Kode Pindah Gudang'
        //                 }, ...res.data]
        //             }).on('select2:select', function(e) {
        //                 let dataselect = e.params.data
        //                 $('[name="id_pindah_barang2"]').val(dataselect.id_pindah_barang)
        //                 $('[name="transporter"]').val(dataselect.transporter)
        //                 $('[name="nomor_polisi"]').val(dataselect.nomor_polisi)
        //                 $('[name="keterangan_pindah_barang"]').val(dataselect.keterangan_pindah_barang)
        //                 $('[name="nama_cabang_asal"]').val(dataselect.nama_cabang)
        //                 $('[name="id_cabang_asal"]').val(dataselect.id_cabang)

        //                 getDetailItem(dataselect.id_pindah_barang)
        //             });

        //             $('#cover-spin').hide()
        //         },
        //         error: function(error) {
        //             console.log(error)
        //             $('#cover-spin').hide()
        //         }
        //     })
        // }

        // function getDetailItem(id_pindah_barang) {
        //     $('#cover-spin').show()
        //     $.ajax({
        //         url: '{{ route('received_from_branch-detail-item') }}',
        //         data: {
        //             id: id_pindah_barang
        //         },
        //         success: function(res) {
        //             details = []
        //             oldDetails = res.data
        //             for (let i = 0; i < oldDetails.length; i++) {
        //                 details.push(oldDetails[i])
        //                 if (arrayQRCode.includes(oldDetails[i]['qr_code'])) {
        //                     details[i]['status_diterima'] = 1
        //                 } else {
        //                     details[i]['id_pindah_barang_detail'] = 0
        //                     details[i]['status_diterima'] = 0
        //                 }
        //             }

        //             $('[name="details"]').val(JSON.stringify(details))
        //             resDataTable.clear().rows.add(details).draw()
        //             $('#cover-spin').hide()
        //         },
        //         error: function(error) {
        //             console.log(error)
        //             $('#cover-spin').hide()
        //         }
        //     })
        // }

        // $('body').on('change', '[name="checked_data"]', function() {
        //     let index = $(this).parents('tr').index()
        //     let detailSelect = details[index]
        //     if ($(this).is(':checked')) {
        //         detailSelect['status_diterima'] = 1
        //     } else {
        //         detailSelect['status_diterima'] = 0
        //     }

        //     details[index] = detailSelect
        //     $('[name="details"]').val(JSON.stringify(details))
        // })

        // function validatorModal() {
        //     let message = 'Lengkapi inputan yang diperlukan'
        //     let valid = true
        //     $('#modalEntry').find('.validate').each(function(i, v) {
        //         if ($(v).val() == '') {
        //             valid = false
        //         }
        //     })

        //     return {
        //         'status': valid,
        //         'message': message
        //     }
        // }

        $('.btn-search').click(function() {
            let self = $('[name="search-qrcode"]').val()
            html5QrcodeScanner.clear();
            searchAsset(self)
        })

        function searchAsset(string) {
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('received_from_warehouse-qrcode') }}',
                type: 'get',
                data: {
                    qrcode: string
                },
                success: function(res) {
                    // for (select in res.data) {
                    //     $('[name="' + select + '"]').val(res.data[select])
                    // }
                    details = res.details
                    resDataTable.clear().rows.add(details).draw()
                    $('[name="details"]').val(JSON.stringify(details))
                    $('[name="search-qrcode"]').val('')
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error
                        .statusText
                    $('#alertModal').text(textError).show()
                    $('#cover-spin').hide()
                }
            })
        }

        function onScanSuccess(decodedText, decodedResult) {
            // audiobarcode.play();
            $('[name="search-qrcode"]').val(decodedText)
            $('.btn-search').click()
        }

        function onScanError(errorMessage) {
            toastr.error(JSON.strignify(errorMessage))
        }
    </script>
@endsection
