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
            Terima Dari Cabang
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Terima Dari Cabang</a></li>
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
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Terima Dari Cabang</h3>
                    <a href="{{ route('received_from_branch') }}"
                        class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Cabang Penerima <span>*</span></label>
                            <div class="form-group">
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
                            <label>Gudang Penerima <span>*</span></label>
                            <div class="form-group">
                                <select name="id_gudang" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Gudang tidak boleh kosong" {{ $data ? 'readonly' : '' }}>
                                    <option value="">Pilih Gudang</option>
                                    @if ($data && $data->id_gudang)
                                        <option value="{{ $data->id_gudang }}" selected>
                                            {{ $data->gudang->kode_gudang }} - {{ $data->gudang->nama_gudang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Tanggal Penerimaan <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="tanggal_pindah_barang"
                                    value="{{ old('tanggal_pindah_barang', $data ? $data->tanggal_pindah_barang : date('Y-m-d')) }}"
                                    class="form-control datepicker" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal penerimaan tidak boleh kosong">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Referensi Kode Pindah Cabang</label>
                            <div class="form-group">
                                <select name="id_pindah_barang2" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Kode pindah cabang tidak boleh kosong">
                                    <option value="">Pilih Kode Pindah Cabang</option>
                                    @if ($data && $data->parent)
                                        <option value="{{ $data->id_pindah_barang2 }}" selected>
                                            {{ $data->parent->kode_pindah_barang }}</option>
                                    @endif
                                </select>
                            </div>
                            <label>Nama Jasa Pengiriman</label>
                            <div class="form-group">
                                <input type="text" name="transporter"
                                    value="{{ old('transporter', $data ? $data->transporter : '') }}" class="form-control"
                                    readonly>
                            </div>
                            <label>No Polisi Kendaraan</label>
                            <div class="form-group">
                                <input type="text" name="nomor_polisi"
                                    value="{{ old('nomor_polisi', $data ? $data->nomor_polisi : '') }}"
                                    class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Kode Pindah Cabang</label>
                            <div class="form-group">
                                <input type="text" name="kode_pindah_barang"
                                    value="{{ old('kode_pindah_barang', $data ? $data->kode_pindah_barang : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                            <label>Cabang Asal <span>*</span></label>
                            <div class="form-group">
                                <input type="text" class="form-control" name="nama_cabang_asal" readonly
                                    value="{{ old('nama_cabang_asal', $data ? $data->cabang2->nama_cabang : '') }}">
                                <input type="hidden" name="id_cabang2"
                                    value="{{ old('id_cabang2', $data ? $data->id_cabang2 : '') }}">
                            </div>
                            <label>Keterangan</label>
                            <div class="form-group">
                                <textarea name="keterangan_pindah_barang" class="form-control" rows="3" readonly>{{ old('keterangan_pindah_barang', $data ? $data->keterangan_pindah_barang : '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Detil Barang</h3>
                    <div class="pull-right">
                        <button type="button" class="btn btn-sm btn-danger btn-flat check-entry">
                            <i class="glyphicon glyphicon-list-alt"></i> Belum Diterima
                        </button>
                        <button type="button" class="btn btn-sm btn-info btn-flat add-entry">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <input name="id_jenis_transaksi" type="hidden"
                            value="{{ old('id_jenis_transaksi', $data ? $data->id_jenis_transaksi : '22') }}">
                        <input type="hidden" name="details" value="[]">
                        <table id="table-detail" class="table table-bordered data-table display responsive nowrap"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>QR Code</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Batch</th>
                                    <th>Kadaluarsa</th>
                                    <th>SG</th>
                                    <th>BE</th>
                                    <th>PH</th>
                                    <th>Bentuk</th>
                                    <th>Warna</th>
                                    <th>Keterangan</th>
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
    </div>

    <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <center>
                        <div id="reader"></div>
                    </center>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" name="search-qrcode" class="form-control" placeholder="QRCode barang"
                                autocomplete="off">
                            <div class="input-group-btn">
                                <button class="btn btn-info btn-search btn-flat">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="result-form" style="display:none;">
                        <table class="table table-bordered">
                            <tr>
                                <td width="100"><b>QR Code</b></td>
                                <td width="20">:</td>
                                <td id="qr_code" class="setData"></td>
                                <td id="id_pindah_barang_detail" class="setData" style="display:none;"></td>
                                <td id="status_diterima" class="setData" style="display:none;"></td>
                            </tr>
                            <tr>
                                <td><b>Nama Barang</b></td>
                                <td>:</td>
                                <td id="nama_barang" class="setData"></td>
                                <td id="id_barang" class="setData" style="display:none;"></td>
                            </tr>
                            <tr>
                                <td><b>Jumlah</b></td>
                                <td>:</td>
                                <td id="qty" class="setData"></td>
                            </tr>
                            <tr>
                                <td><b>Satuan</b></td>
                                <td>:</td>
                                <td id="nama_satuan_barang" class="setData"></td>
                                <td id="id_satuan_barang" class="setData" style="display:none;"></td>
                            </tr>
                            <tr>
                                <td><b>Jumlah Zak</b></td>
                                <td>:</td>
                                <td id="zak" class="setData"></td>
                                <td id="id_wrapper_zak" class="setData" style="display:none;"></td>
                                <td id="weight_zak" class="setData" style="display:none;"></td>
                            </tr>
                            <tr>
                                <td><b>SG</b></td>
                                <td>:</td>
                                <td id="sg" class="setData"></td>
                            </tr>
                            <tr>
                                <td><b>BE</b></td>
                                <td>:</td>
                                <td id="be" class="setData"></td>
                            </tr>
                            <tr>
                                <td><b>PH</b></td>
                                <td>:</td>
                                <td id="ph" class="setData"></td>
                            </tr>
                            <tr>
                                <td><b>Bentuk</b></td>
                                <td>:</td>
                                <td id="bentuk" class="setData"></td>
                            </tr>
                            <tr>
                                <td><b>Warna</b></td>
                                <td>:</td>
                                <td id="warna" class="setData"></td>
                            </tr>
                            <tr>
                                <td><b>Keterangan</b></td>
                                <td>:</td>
                                <td id="keterangan" class="setData"></td>
                            </tr>
                            <tr>
                                <td><b>Batch</b></td>
                                <td>:</td>
                                <td id="batch" class="setData"></td>
                            </tr>
                            <tr>
                                <td><b>Kadaluarsa</b></td>
                                <td>:</td>
                                <td id="tanggal_kadaluarsa" class="setData"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel-entry btn-flat">Batal</button>
                    <button type="button" class="btn btn-primary save-entry btn-flat result-form">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNotReceived" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Barang Belum Diterima</h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="table-detail-item" class="table table-bordered data-table display responsive nowrap"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>QR Code</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Batch</th>
                                    <th>Kadaluarsa</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Tutup</button>
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
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branches = {!! json_encode($cabang) !!};
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let notReceived = {!! $data ? $data->parent->notReceivedDetail : '[]' !!}
        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });

        $('[name="details"]').val(JSON.stringify(details))

        var resDataTable = $('#table-detail').DataTable({
            paging: false,
            data: details,
            ordering: false,
            drawCallback: function() {
                var allData = this.api().column(0).data().toArray();
                var toFindDuplicates = allData => allData.filter((item, index) => allData.indexOf(item) !==
                    index)
                var duplicateElementa = toFindDuplicates(allData);
                var indexs = []
                for (let i = 0; i < duplicateElementa.length; i++) {
                    let indexDuplicate = allData.indexOf(duplicateElementa[i])
                    $($('#table-detail tbody tr:eq(' + indexDuplicate + ')')).css('color', 'red')
                }
            },
            columns: [{
                    data: 'qr_code',
                    name: 'qr_code'
                }, {
                    data: 'nama_barang',
                    name: 'nama_barang'
                }, {
                    data: 'qty',
                    name: 'qty',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'nama_satuan_barang',
                    name: 'nama_satuan_barang'
                }, {
                    data: 'batch',
                    name: 'batch',
                    className: 'text-right'
                }, {
                    data: 'tanggal_kadaluarsa',
                    name: 'tanggal_kadaluarsa',
                }, {
                    data: 'sg',
                    name: 'sg',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'be',
                    name: 'be',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'ph',
                    name: 'ph',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'bentuk',
                    name: 'bentuk',
                },
                {
                    data: 'warna',
                    name: 'warna',
                }, {
                    data: 'keterangan',
                    name: 'keterangan',
                },
                {
                    data: 'id_pindah_barang_detail',
                    className: 'text-center',
                    name: 'id_pindah_barang_detail',
                    searchable: false,
                    render: function(data, type, row, meta) {
                        let btn = '';
                        if (row.id_pindah_barang_detail == '') {
                            btn = '<ul class="horizontal-list">';
                            btn +=
                                '<li><a href="javascript:void(0)" class="btn btn-danger btn-xs mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a></li>';
                            btn += '</ul>';
                        }

                        return btn;
                    }
                },
            ]
        });

        $('#table-detail-item').DataTable({
            pading: false,
            data: notReceived,
            ordering: false,
            columns: [{
                data: 'qr_code',
                name: 'qr_code'
            }, {
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'qty',
                name: 'qty',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang'
            }, {
                data: 'batch',
                name: 'batch',
                className: 'text-right'
            }, {
                data: 'tanggal_kadaluarsa',
                name: 'tanggal_kadaluarsa',
            }]
        });

        $('.select2').select2()
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        $('[name="id_cabang"]').select2({
            data: [{
                'id': '',
                'text': 'Pilih Cabang'
            }, ...branches]
        }).on('select2:select', function(e) {
            let dataselect = e.params.data
            getGudang(dataselect)
            getCodePindahGudang(dataselect.id)
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

        function getCodePindahGudang(idCabang) {
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('received_from_branch-code') }}',
                data: {
                    cabang: idCabang
                },
                success: function(res) {
                    $('[name="id_pindah_barang2"]').empty()
                    $('[name="id_pindah_barang2"]').select2({
                        data: [{
                            'id': "",
                            'text': 'Pilih Kode Pindah Cabang'
                        }, ...res.data]
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        $('[name="id_pindah_barang2"]').val(dataselect.id)
                        $('[name="transporter"]').val(dataselect.transporter)
                        $('[name="nomor_polisi"]').val(dataselect.nomor_polisi)
                        $('[name="keterangan_pindah_barang"]').val(dataselect.keterangan_pindah_barang)
                        $('[name="nama_cabang_asal"]').val(dataselect.nama_cabang)
                        $('[name="id_cabang2"]').val(dataselect.id_cabang)
                    });

                    $('#cover-spin').hide()
                },
                error: function(error) {
                    console.log(error)
                    $('#cover-spin').hide()
                }
            })
        }

        $('.add-entry').click(function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })

            $('#modalEntry').find('.setData').each(function(i, v) {
                $(v).text('')
            })

            statusModal = 'create'
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })

            $('.result-form').hide()
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        })

        $('.save-entry').click(function() {
            let modal = $('#modalEntry')
            let valid = validatorModal($('#qr_code').text())
            if (!valid.status) {
                html5QrcodeScanner.render(onScanSuccess, onScanError);
                Swal.fire("Gagal proses data. ", valid.message, 'error')
                return false
            }

            modal.find('.setData').each(function(i, v) {
                let id = $(v).prop('id')
                if (id == 'qty') {
                    detailSelect[id] = normalizeNumber($(v).text())
                } else {
                    detailSelect[id] = $(v).text()
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
                    resDataTable.clear().rows.add(details).draw()
                    $('[name="details"]').val(JSON.stringify(details))
                }
            })
        })

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

        $('.cancel-entry').click(function() {
            $('#modalEntry').modal('hide')
            html5QrcodeScanner.clear();
        })

        function validatorModal(id = 0) {
            let message = 'Lengkapi inputan yang diperlukan'
            let valid = true
            let findItem = details.filter(p => p.qr_code == id)
            if (findItem.length > 0 && findItem[0].qr_code == id && statusModal == 'create') {
                message = "Barang sudah ada"
                valid = false
            }

            return {
                'status': valid,
                'message': message
            }
        }

        $('.btn-search').click(function() {
            let self = $('[name="search-qrcode"]').val()
            html5QrcodeScanner.clear();
            searchAsset(self)
        })

        $('.check-entry').click(function() {
            $('#modalNotReceived').modal();
        })

        function searchAsset(string) {
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('received_from_branch-detail-item') }}',
                type: 'get',
                data: {
                    id_pindah_barang: $('[name="id_pindah_barang2"]').val(),
                    qrcode: string
                },
                success: function(res) {
                    for (select in res.data) {
                        if (select == 'qty') {
                            $('#' + select).text(formatNumber(res.data[select]))
                        } else {
                            $('#' + select).text(res.data[select])
                        }

                        $('#status_diterima').text(1)
                    }

                    $('[name="search-qrcode"]').val('')
                    $('.result-form').show()
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error
                        .statusText
                    Swal.fire("Gagal Mengambil Data. ", textError, 'error')
                    html5QrcodeScanner.render(onScanSuccess, onScanError);
                    $('.result-form').hide()
                    $('#cover-spin').hide()
                }
            })
        }

        function onScanSuccess(decodedText, decodedResult) {
            audiobarcode.play();
            $('[name="search-qrcode"]').val(decodedText)
            $('.btn-search').click()
        }

        function onScanError(errorMessage) {
            toastr.error(JSON.strignify(errorMessage))
        }
    </script>
@endsection
