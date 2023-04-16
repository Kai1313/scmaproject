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
            width: 100%;
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
            <li><a href="{{ route('send_to_branch') }}">Kirim ke Cabang</a></li>
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
                                <select name="id_cabang2" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Cabang tujuan tidak boleh kosong">
                                    <option value="">Pilih Cabang Tujuan</option>
                                    @if ($data && $data->id_cabang2)
                                        <option value="{{ $data->id_cabang2 }}" selected>
                                            {{ $data->cabang2->nama_cabang }}
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
                        <input name="id_jenis_transaksi" type="hidden"
                            value="{{ old('id_jenis_transaksi', $data ? $data->id_jenis_transaksi : '21') }}">
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
                    <button class="btn btn-primary btn-flat pull-right" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <center>
                            <div id="reader"></div>
                        </center>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" name="search-qrcode" class="form-control"
                                    placeholder="QRCode barang" autocomplete="off">
                                <div class="input-group-btn">
                                    <button class="btn btn-info btn-search btn-flat">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="id_pindah_barang_detail">
                        <table class="table table-bordered">
                            <tr>
                                <td width="100"><b>QR Code</b></td>
                                <td width="20">:</td>
                                <td id="qr_code" class="setData"></td>
                                <td id="id_pindah_barang_detail" class="setData" style="display:none;"></td>
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
        let branches = {!! json_encode($cabang) !!};
        let allBranch = {!! $allCabang !!}
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
            getGudang(dataselect)
        });

        function getGudang(data) {
            $('[name="id_gudang"]').select2({
                data: [{
                    'id': "",
                    'text': 'Pilih Gudang'
                }, ...data.gudang]
            })

            let branchData = []
            for (let i = 0; i < allBranch.length; i++) {
                if (allBranch[i].id != data.id) {
                    branchData.push(allBranch[i])
                }
            }
            $('[name="id_cabang2"]').empty()
            $('[name="id_cabang2"]').select2({
                data: [{
                    'id': '',
                    'text': 'Pilih Cabang Tujuan'
                }, ...branchData]
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

            html5QrcodeScanner.render(onScanSuccess, onScanError);
            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
        })

        $('.save-entry').click(function() {
            let modal = $('#modalEntry')
            let valid = validatorModal($('#qr_code').text())
            if (!valid.status) {
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
                        if (select == 'qty') {
                            $('#' + select).text(formatNumber(res.data[select]))
                        } else {
                            $('#' + select).text(res.data[select])
                        }
                    }

                    $('[name="search-qrcode"]').val('')
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error
                        .statusText
                    Swal.fire("Gagal Mengambil Data. ", textError, 'error')
                    html5QrcodeScanner.render(onScanSuccess, onScanError);
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
