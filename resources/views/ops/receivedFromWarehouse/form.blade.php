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
        <form action="{{ route('received_from_warehouse-save-entry', $data ? $data->id_pindah_barang : 0) }}" method="post"
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
                            <center style="{{ $data ? 'display:none;' : 'display:block;' }}">
                                <div id="reader"></div>
                            </center>
                        </div>
                    </div>
                    <div class="row">
                        @if (!$data)
                            <div class="col-md-4">
                                <label>Scan Kode Pengiriman</label>
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
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label>Tanggal Penerimaan <span>*</span></label>
                            <div class="form-group">
                                <input type="text" name="tanggal_pindah_barang"
                                    value="{{ old('tanggal_pindah_barang', $data ? $data->tanggal_pindah_barang : date('Y-m-d')) }}"
                                    class="form-control datepicker" data-validation="[NOTEMPTY]"
                                    data-validation-message="Tanggal penerimaan tidak boleh kosong" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Kode Terima Dari Gudang</label>
                            <div class="form-group">
                                <input type="text" name="kode_pindah_barang"
                                    value="{{ old('kode_pindah_barang', $data ? $data->kode_pindah_barang : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                                <input type="hidden" name="id_pindah_barang2"
                                    value="{{ old('id_pindah_barang2', $data ? $data->id_pindah_barang2 : '') }}">
                                <input type="hidden" name="keterangan_pindah_barang"
                                    value="{{ old('keterangan_pindah_barang', $data ? $data->keterangan_pindah_barang : '') }}">
                                <input name="id_jenis_transaksi" type="hidden"
                                    value="{{ old('id_jenis_transaksi', $data ? $data->id_jenis_transaksi : '24') }}">
                            </div>
                        </div>
                        @if ($data)
                            <div class="col-md-4">
                                <label>Kode Referensi <span>*</span></label>
                                <div class="form-group">
                                    <input type="text" name="kode_pindah_barang2"
                                        value="{{ old('kode_pindah_barang2', $data ? $data->parent->kode_pindah_barang : '') }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label>Cabang Penerima</label>
                            <div class="form-group">
                                <input type="text" name="nama_cabang"
                                    value="{{ old('nama_cabang', $data ? $data->cabang->nama_cabang : '') }}"
                                    class="form-control" readonly>
                                <input type="hidden" name="id_cabang"
                                    value="{{ old('id_cabang', $data ? $data->id_cabang : '') }}">
                                <input type="hidden" name="id_cabang2"
                                    value="{{ old('id_cabang2', $data ? $data->id_cabang2 : '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Gudang Penerima</label>
                            <div class="form-group">
                                <input type="text" name="nama_gudang"
                                    value="{{ old('nama_gudang', $data ? $data->gudang->nama_gudang : '') }}"
                                    class="form-control" readonly>
                                <input type="hidden" name="id_gudang"
                                    value="{{ old('id_gudang', $data ? $data->id_gudang : '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Gudang Asal</label>
                            <div class="form-group">
                                <input type="text" name="nama_gudang2"
                                    value="{{ old('nama_gudang2', $data ? $data->gudang2->nama_gudang : '') }}"
                                    class="form-control" readonly>
                                <input type="hidden" name="id_gudang2"
                                    value="{{ old('id_gudang2', $data ? $data->id_gudang2 : '') }}">
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
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Batch</th>
                                    <th>Kadaluarsa</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary btn-flat pull-right btn-sm" type="submit">
                        <i class="glyphicon glyphicon-check"></i> Terima Semua Barang
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
        let idData = {{ $data ? $data->id_pindah_barang : 0 }}
        let arrayQRCode = {!! $data ? $data->getDetailQRCode->pluck('qr_code') : '[]' !!};
        let oldDetails = {!! $data && $data->parent ? $data->parent->formatdetail : '[]' !!};
        let details = {!! $data ? $data->formatdetail : '[]' !!}
        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });

        if (idData == 0) {
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        }

        for (let a = 0; a < oldDetails.length; a++) {
            if (arrayQRCode.includes(oldDetails[a].qr_code) == false) {
                oldDetails[a]['id_pindah_barang_detail'] = ''
                oldDetails[a]['status_diterima'] = 1
                details.push(oldDetails[a])
            }
        }

        $('[name="details"]').val(JSON.stringify(details))
        var resDataTable = $('#table-detail').DataTable({
            paging: false,
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
                data: 'status_akhir',
                name: 'status_akhir',
            }, ]
        });

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
                    details = []
                    let newDetail = res.details
                    for (let i = 0; i < newDetail.length; i++) {
                        if (arrayQRCode.includes(newDetail[i]['qr_code'])) {
                            newDetail[i]['status_diterima'] = 1
                        } else {
                            newDetail[i]['id_pindah_barang_detail'] = ''
                            newDetail[i]['status_diterima'] = 1
                        }

                        details.push(newDetail[i])
                    }

                    resDataTable.clear().rows.add(details).draw()
                    $('[name="details"]').val(JSON.stringify(details))
                    $('[name="id_cabang"]').val(res.data.id_cabang2)
                    $('[name="id_cabang2"]').val(res.data.id_cabang)
                    $('[name="nama_cabang"]').val(res.data.cabang2.nama_cabang)
                    $('[name="id_gudang"]').val(res.data.id_gudang2)
                    $('[name="nama_gudang"]').val(res.data.gudang2.nama_gudang)
                    $('[name="id_gudang2"]').val(res.data.id_gudang)
                    $('[name="nama_gudang2"]').val(res.data.gudang.nama_gudang)
                    $('[name="id_pindah_barang2"]').val(res.data.id_pindah_barang)
                    $('[name="keterangan_pindah_barang"]').val(res.data.keterangan_pindah_barang)
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
