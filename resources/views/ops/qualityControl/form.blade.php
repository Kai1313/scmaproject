@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
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
            QC Penerimaan Pembelian
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('qc_receipt') }}">QC Penerimaan Pembelian</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('qc_receipt-save-entry', $data ? $data->id : 0) }}" method="post" class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} QC Penerimaan Pembelian</h3>
                    <a href="{{ route('qc_receipt') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
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
                                    @foreach ($cabang as $branch)
                                        <option value="{{ $branch->id_cabang }}"
                                            {{ old('id_cabang', $data ? $data->id_cabang : '') == $branch->id_cabang ? 'selected' : '' }}>
                                            {{ $branch->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>No Bukti Penerimaan <span>*</span></label>
                                <select name="id_pembelian" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Pembelian tidak boleh kosong">
                                    <option value="">Pilih No Bukti Penerimaan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Keterangan <span>*</span></label>
                                <textarea name="" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Detil Penerimaan Pembelian</h4>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-info add-entry btn-flat pull-right" type="button">
                                <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <input type="hidden" name="details"
                            value="{{ $data ? json_encode($data->purchasing->qc) : '[]' }}">
                        <table id="table-detail" class="table table-bordered data-table">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal QC</th>
                                    <th>Status</th>
                                    <th>Alasan</th>
                                    <th>SG</th>
                                    <th>BE</th>
                                    <th>PH</th>
                                    <th>Warna</th>
                                    <th style="width:150px;">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary pull-right btn-flat" type="submit">
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
                    <div class="alert alert-danger" style="display:none;" id="alertModal">
                    </div>
                    <input type="hidden" name="index" value="0">
                    <label>Nama Barang <span>*</span></label>
                    <div class="form-group">
                        <select name="id_barang" class="form-control validate">
                        </select>
                        <input type="hidden" name="nama_barang" class="validate">
                    </div>
                    <label>Total</label>
                    <div class="form-group">
                        <div class="input-group" id="qty">
                            <input type="text" name="jumlah_pembelian_detail" class="form-control handle-number-4"
                                readonly>
                            <span class="input-group-addon">KG</span>
                        </div>
                    </div>
                    <label>SG</label>
                    <div class="form-group">
                        <input type="text" name="sg_pembelian_detail" class="form-control handle-number-4">
                    </div>
                    <label>BE</label>
                    <div class="form-group">
                        <input type="text" name="be_pembelian_detail" class="form-control handle-number-4">
                    </div>
                    <label>PH</label>
                    <div class="form-group">
                        <input type="text" name="ph_pembelian_detail" class="form-control handle-number-4">
                    </div>
                    <label>Warna</label>
                    <div class="form-group">
                        <input type="text" name="warna_pembelian_detail" class="form-control">
                    </div>
                    <label>Keterangan</label>
                    <div class="form-group">
                        <textarea name="keterangan_pembelian_detail" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel-entry btn-flat">Batal</button>
                    <button type="button" class="btn btn-primary save-entry btn-flat">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let details = {!! $data ? $data->purchasing->qc : '[]' !!};
        let detailSelect = []
        let count = details.length
        let statusModal = 'create'

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        var resDataTable = $('#table-detail').DataTable({
            data: details,
            ordering: false,
            columns: [{
                    data: 'nama_barang',
                    name: 'nama_barang'
                },
                {
                    data: 'nama_satuan_barang',
                    name: 'nama_satuan_barang'
                },
                {
                    data: 'jumlah_pembelian_detail',
                    name: 'jumlah_pembelian_detail',
                },
                {
                    data: 'tanggal_qc',
                    name: 'tanggal_qc',
                },
                {
                    data: 'status_qc',
                    name: 'status_qc'
                },
                {
                    data: 'reason',
                    name: 'reason',
                },
                {
                    data: 'sg_pembelian_detail',
                    name: 'sg_pembelian_detail',
                },
                {
                    data: 'be_pembelian_detail',
                    name: 'be_pembelian_detail',
                },
                {
                    data: 'ph_pembelian_detail',
                    name: 'ph_pembelian_detail',
                },
                {
                    data: 'warna_pembelian_detail',
                    name: 'warna_pembelian_detail',
                },
                {
                    data: 'id',
                    className: 'text-center',
                    name: 'id',
                    searchable: false,
                    render: function(data, type, row, meta) {
                        let btn = '<ul class="horizontal-list">';
                        btn +=
                            '<li><a href="javascript:void(0)" data-index="' + data +
                            '" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a></li>';
                        btn +=
                            '<li><a href="javascript:void(0)" data-index="' + data +
                            '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a></li>';
                        btn += '</ul>';
                        return btn;
                    }
                },
            ]
        });

        $('[name="id_cabang"]').select2().on('select2:select', function(e) {
            let dataselect = e.params.data
            getPurchasingNumber()
        });

        function getPurchasingNumber() {
            $.ajax({
                url: '{{ route('qc_receipt-auto-purchasing') }}',
                data: {
                    cabang: $('[name="id_cabang"]').val(),
                },
                success: function(res) {
                    $('[name="id_pembelian"]').empty()
                    $('[name="id_pembelian"]').select2({
                        data: [{
                            'id': "",
                            'text': 'Pilih No Bukti Pembelian'
                        }, ...res.data]
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        getItem(dataselect.id)
                    });
                },
                error: function(error) {
                    console.log(error)
                }
            })
        }

        function getItem(param) {
            $.ajax({
                url: '{{ route('qc_receipt-auto-item') }}',
                data: {
                    number: param,
                },
                success: function(res) {
                    $('[name="id_barang"]').empty()
                    $('[name="id_barang"]').select2({
                        data: [{
                            'id': "",
                            'text': 'Pilih Barang'
                        }, ...res.data]
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        $('#qty').find('[name="jumlah_pembelian_detail"]').val(dataselect
                            .jumlah_pembelian_detail).trigger('input')
                        $('#qty').find('span').text(dataselect.nama_satuan_barang)
                    });
                },
                error: function(error) {

                }
            })
        }

        $('.add-entry').click(function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })

            // $('#alertModal').text('').hide()
            // statusModal = 'create'
            // count += 1
            // $('#modalEntry').find('[name="index"]').val(count)
            $('#modalEntry').modal('show')

            // $('[name="id_barang"]').select2('open')
            // $('#message-stok').text('')

            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
        })
    </script>
@endsection
