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

        #reader {
            width: 50%;
        }

        @media only screen and (max-width: 412px) {
            #reader {
                width: 100%;
            }
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Kirim ke Cabang
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Kirim ke Cabang</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('send_to_branch-save-entry', $data ? $data->id_pindah_barang : 0) }}" method="post"
            class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Kirim Ke Cabang</h3>
                    <a href="{{ route('send_to_branch') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Cabang <span>*</span></label>
                            <div class="form-group">
                                <select name="id_cabang" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Cabang tidak boleh kosong">
                                    <option value="">Pilih Cabang</option>
                                    @if ($data && $data->id_cabang)
                                        <option value="{{ $data->id_cabang }}" selected>
                                            {{ $data->cabang->nama_cabang }}
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
                                            {{ $data->gudang->nama_gudang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Tanggal <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="tanggal_pindah_barang"
                                    value="{{ old('tanggal_pindah_barang', $data ? $data->tanggal_pindah_barang : date('Y-m-d')) }}"
                                    class="form-control datepicker" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal tidak boleh kosong">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Kode Pindah Cabang</label>
                            <div class="form-group">
                                <input type="text" name="kode_pindah_barang"
                                    value="{{ old('kode_pindah_barang', $data ? $data->kode_pindah_barang : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                            <label>Nama Jasa Pengiriman</label>
                            <div class="form-group">
                                <input type="text" name="transporter"
                                    value="{{ old('transporter', $data ? $data->transporter : '') }}" class="form-control">
                            </div>
                            <label>No Polisi Kendaraan</label>
                            <div class="form-group">
                                <input type="text" name="nomor_polisi"
                                    value="{{ old('nomor_polisi', $data ? $data->nomor_polisi : '') }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Cabang Tujuan<span>*</span></label>
                            <div class="form-group">
                                <select name="id_cabang_tujuan" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Cabang tujuan tidak boleh kosong">
                                    <option value="">Pilih Cabang Tujuan</option>
                                    @if ($data && $data->id_cabang_tujuan)
                                        <option value="{{ $data->id_cabang_tujuan }}" selected>
                                            {{ $data->destinationBranch->nama_cabang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Keterangan</label>
                            <div class="form-group">
                                <textarea name="keterangan_pindah_barang" class="form-control" rows="3">{{ old('keterangan_pindah_barang', $data ? $data->keterangan_pindah_barang : '') }}</textarea>
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
                        <div class="col-md-6">
                            <button class="btn btn-info add-entry btn-flat pull-right" type="button">
                                <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                            </button>
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
                    <button class="btn btn-primary btn-flat pull-right" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display:none;" id="alertModal">
                        </div>
                        <center>
                            <div id="reader"></div>
                        </center>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" name="search-qrcode" class="form-control"
                                    placeholder="QRCode barang">
                                <div class="input-group-btn">
                                    <button class="btn btn-info btn-search btn-flat">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            Pastikan QR Code sudah keluar rak dan stok tidak habis
                        </div>
                        <input type="hidden" name="id_pindah_barang_detail">
                        <div class="row">
                            <div class="col-xs-6">
                                <label>QR Code</label>
                                <div class="form-group">
                                    <input type="text" name="qr_code" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <label>Nama Barang</label>
                                <div class="form-group">
                                    <input type="text" name="nama_barang" class="form-control" readonly>
                                    <input type="hidden" name="id_barang">
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <label>Satuan</label>
                                <div class="form-group">
                                    <input type="text" name="nama_satuan_barang" class="form-control" readonly>
                                    <input type="hidden" name="id_satuan_barang">
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <label>Jumlah</label>
                                <div class="form-group">
                                    <input type="text" name="qty" class="form-control handle-number-4" readonly>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <label>SG</label>
                                <div class="form-group">
                                    <input type="text" name="sg" class="form-control handle-number-4" readonly>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <label>BE</label>
                                <div class="form-group">
                                    <input type="text" name="be" class="form-control handle-number-4" readonly>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <label>PH</label>
                                <div class="form-group">
                                    <input type="text" name="ph" class="form-control handle-number-4" readonly>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <label>Bentuk</label>
                                <div class="form-group">
                                    <input type="text" name="bentuk" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <label>Warna</label>
                                <div class="form-group">
                                    <input type="text" name="warna" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <label>Batch</label>
                                <div class="form-group">
                                    <input type="text" name="batch" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <label>Kadaluarsa</label>
                                <div class="form-group">
                                    <input type="text" name="tanggal_kadaluarsa" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <label>Keterangan</label>
                        <div class="form-group">
                            <textarea name="keterangan" rows="3" class="form-control" readonly></textarea>
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
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branches = {!! $cabang !!};
        var audiobarcode = new Audio("{{ asset('files/scan.mp3') }}");
        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let detailSelect = []
        let statusModal = 'create'

        var resDataTable = $('#table-detail').DataTable({
            data: details,
            ordering: false,
            columns: [{
                    data: 'qr_code',
                    name: 'qr_code'
                }, {
                    data: 'nama_barang',
                    name: 'nama_barang'
                }, {
                    data: 'nama_satuan_barang',
                    name: 'nama_satuan_barang'
                }, {
                    data: 'qty',
                    name: 'qty',
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
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
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
                }, {
                    data: 'be',
                    name: 'be',
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
                }, {
                    data: 'ph',
                    name: 'ph',
                    render: $.fn.dataTable.render.number('.', ',', 4),
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
                        let btn = '<ul class="horizontal-list">';
                        btn +=
                            '<li><a href="javascript:void(0)" class="btn btn-danger btn-xs mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a></li>';
                        btn += '</ul>';
                        return btn;
                    }
                },
            ]
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
            getGudang(dataselect.id)
        });

        function getGudang(idCabang) {
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('purchase-request-auto-werehouse') }}',
                data: {
                    cabang: idCabang
                },
                success: function(res) {
                    $('[name="id_gudang"]').empty()
                    $('[name="id_gudang"]').select2({
                        data: [{
                            'id': "",
                            'text': 'Pilih Gudang'
                        }, ...res.data]
                    })

                    let branchData = []
                    for (let i = 0; i < branches.length; i++) {
                        if (branches[i].id != idCabang) {
                            branchData.push(branches[i])
                        }
                    }

                    $('[name="id_cabang_tujuan"]').empty()
                    $('[name="id_cabang_tujuan"]').select2({
                        data: [{
                            'id': '',
                            'text': 'Pilih Cabang Tujuan'
                        }, ...branchData]
                    })
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

            $('#alertModal').text('').hide()
            statusModal = 'create'
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })

            html5QrcodeScanner.render(onScanSuccess, onScanError);
            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
        })

        $('.save-entry').click(function() {
            let modal = $('#modalEntry')
            let valid = validatorModal(modal.find('[name="id_barang"]').val())
            if (!valid.status) {
                $('#alertModal').text(valid.message).show()
                return false
            } else {
                $('#alertModal').text('').hide()
            }

            modal.find('input,textarea').each(function(i, v) {
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
            html5QrcodeScanner.clear();
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

        function validatorModal() {
            let message = 'Lengkapi inputan yang diperlukan'
            let valid = true
            $('#modalEntry').find('.validate').each(function(i, v) {
                if ($(v).val() == '') {
                    valid = false
                }

                if ($(v).prop('name') == 'qr_code') {
                    let findItem = details.filter(p => p.kode_batang_lama_pindah_gudang_detail == $(v).val())
                    if (findItem.length > 0 && findItem[0].id_barang == id && statusModal == 'create') {
                        message = "Barang sudah ada"
                        valid = false
                    }
                }
            })

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

        function searchAsset(string) {
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('send_to_branch-qrcode') }}',
                type: 'get',
                data: {
                    id_cabang: $('[name="id_cabang"]').val(),
                    id_gudang: $('[name="id_gudang"]').val(),
                    qrcode: string
                },
                success: function(res) {
                    for (select in res.data) {
                        $('[name="' + select + '"]').val(res.data[select])
                    }
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
            audiobarcode.play();
            $('[name="search-qrcode"]').val(decodedText)
            $('.btn-search').click()
        }

        function onScanError(errorMessage) {
            toastr.error(JSON.strignify(errorMessage))
        }
    </script>
@endsection
