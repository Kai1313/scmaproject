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
            QC Penerimaan Pembelian
            {{-- <small>| {{ $data ? 'Edit' : 'Tambah' }}</small> --}}
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
                    <h3 class="box-title">Cari Penerimaan Pembelian</h3>
                    <a href="javascript:void(0)" class="btn btn-default btn-flat pull-right btn-print pull-right btn-sm"
                        target="_blank" style="display:none;margin-right:10px;">
                        <span class="glyphicon glyphicon-print mr-1"></span> Cetak
                    </a>
                    <a href="{{ route('qc_receipt') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"
                        style="margin-right:10px;">
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
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No Bukti Penerimaan <span>*</span></label>
                                <select name="id_pembelian" class="form-control select2" data-validation="[NOTEMPTY]"
                                    data-validation-message="Pembelian tidak boleh kosong">
                                    <option value="">Pilih No Bukti Penerimaan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nama Supplier</label>
                                <input type="text" class="form-control" name="pemasok" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No PO</label>
                                <input type="text" class="form-control" name="po" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Penerimaan</label>
                                <input type="text" class="form-control" name="tanggal" disabled>
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
                        <input type="hidden" name="details"
                            value="{{ $data ? json_encode($data->purchasing->qc) : '[]' }}">
                        <table id="table-detail" class="table table-bordered data-table display responsive nowrap"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Tanggal QC</th>
                                    <th>Status</th>
                                    <th>Alasan</th>
                                    <th>SG</th>
                                    <th>BE</th>
                                    <th>PH</th>
                                    <th>Warna</th>
                                    <th>Bentuk</th>
                                    <th>Keterangan</th>
                                    <th>Action</th>
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
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Form QC Barang</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" style="display:none;" id="alertModal">
                        asd
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" name="id" value="0">
                            <label>Nama Barang <span>*</span></label>
                            <div class="form-group">
                                <select name="id_barang" class="form-control validate select2">
                                </select>
                                <input type="hidden" name="nama_barang" class="validate">
                            </div>
                            <label>Total <span>*</span></label>
                            <div class="form-group">
                                <div class="input-group" id="qty">
                                    <input type="text" name="jumlah_pembelian_detail" class="form-control" readonly>
                                    <span class="input-group-addon">KG</span>
                                </div>
                                <input type="hidden" name="nama_satuan_barang" class="validate">
                                <input type="hidden" name="id_satuan_barang" class="validate">
                                <input type="hidden" name="tanggal_qc" value="{{ date('Y-m-d') }}">
                            </div>
                            <label>Status <span>*</span></label>
                            <div class="form-group">
                                <select name="status_qc" class="form-control validate">
                                </select>
                            </div>
                            <label>Alasan</label>
                            <div class="form-group">
                                <textarea name="reason" class="form-control" readonly></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>SG </label>
                                    <div class="form-group">
                                        <input type="text" name="sg_pembelian_detail"
                                            class="form-control handle-number-4">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label>BE </label>
                                    <div class="form-group">
                                        <input type="text" name="be_pembelian_detail"
                                            class="form-control handle-number-4">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>PH </label>
                                    <div class="form-group">
                                        <input type="text" name="ph_pembelian_detail"
                                            class="form-control handle-number-4">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label>Warna</label>
                                    <div class="form-group">
                                        <input type="text" name="warna_pembelian_detail" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <label>Bentuk</label>
                            <div class="form-group">
                                <input type="text" name="bentuk_pembelian_detail" class="form-control">
                            </div>
                            <label>Keterangan</label>
                            <div class="form-group">
                                <textarea name="keterangan_pembelian_detail" class="form-control"></textarea>
                            </div>
                        </div>
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
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branch = {!! json_encode($cabang) !!}
        let arrayStatus = {!! json_encode($arrayStatus) !!};
        let details = [];
        let detailSelect = []
        let statusModal = 'create'
        let indexSelect = 0
        $('.select2').select2()
        $('[name="status_qc"]').select2({
            data: arrayStatus
        })

        var resDataTable = $('#table-detail').DataTable({
            data: details,
            ordering: false,
            columns: [{
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'jumlah_pembelian_detail',
                name: 'jumlah_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang'
            }, {
                data: 'tanggal_qc',
                name: 'tanggal_qc',
            }, {
                data: 'status_qc',
                name: 'status_qc',
                render: function(data, type, row) {
                    return '<label class="' + arrayStatus[data]['class'] + '">' + arrayStatus[data][
                        'text'
                    ] + '</label>';
                },
                className: 'text-center'
            }, {
                data: 'reason',
                name: 'reason',
            }, {
                data: 'sg_pembelian_detail',
                name: 'sg_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'be_pembelian_detail',
                name: 'be_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'ph_pembelian_detail',
                name: 'ph_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'warna_pembelian_detail',
                name: 'warna_pembelian_detail',
            }, {
                data: 'bentuk_pembelian_detail',
                name: 'bentuk_pembelian_detail',
            }, {
                data: 'keterangan_pembelian_detail',
                name: 'keterangan_pembelian_detail',
            }, {
                data: 'id_barang',
                className: 'text-center',
                name: 'id_barang',
                searchable: false,
                render: function(data, type, row, meta) {
                    let btn = '';
                    if (row.status_qc == 3) {
                        btn = '<ul class="horizontal-list">';
                        btn +=
                            '<li><a href="javascript:void(0)" data-id="' + data +
                            '" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a></li>';
                        btn += '</ul>';
                    }

                    return btn;
                }
            }]
        });

        $('[name="id_cabang"]').select2({
            data: branch
        }).on('select2:select', function(e) {
            let dataselect = e.params.data
            getPurchasingNumber()
        });


        function getPurchasingNumber() {
            $('#cover-spin').show()
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
                            'text': 'Pilih No Bukti Penerimaan'
                        }, ...res.data]
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        $('[name="pemasok"]').val(dataselect.nama_pemasok)
                        $('[name="tanggal"]').val(dataselect.tanggal_pembelian)
                        $('[name="po"]').val(dataselect.nomor_po_pembelian)
                        getItem(dataselect.id)
                    });
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    console.log(error)
                    $('#cover-spin').hide()
                }
            })
        }

        function getItem(param) {
            $('#cover-spin').show()
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
                        }, ...res.list_item]
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        $('#qty').find('[name="jumlah_pembelian_detail"]').val(dataselect
                            .jumlah_pembelian_detail).trigger('input')
                        $('#qty').find('span').text(dataselect.nama_satuan_barang)
                        $('[name="nama_barang"]').val(dataselect.text)
                        $('[name="nama_satuan_barang"]').val(dataselect.nama_satuan_barang)
                        $('[name="id_satuan_barang"]').val(dataselect.id_satuan_barang)
                    });

                    details = res.qc
                    $('.btn-print').attr('href', res.route_print).css('display', 'block')
                    resDataTable.clear().rows.add(details).draw()
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    $('#cover-spin').hide()
                    console.log(error)
                }
            })
        }

        $('.add-entry').click(function() {
            statusModal = 'create'
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                if ($(v).hasClass('handle-number-4')) {
                    if ($(v).prop('name') == 'sg_pembelian_detail') {
                        $(v).val(1).trigger('change')
                    } else {
                        $(v).val(0).trigger('change')
                    }
                } else {
                    $(v).val('').trigger('change')
                }
            })

            $('#alertModal').text('').hide()
            $('#modalEntry').modal('show')
            setTimeout(() => {
                $('[name="id_barang"]').select2('open')
            }, 500);

            $('[name="status_qc"]').empty()
            $('[name="status_qc"]').select2({
                data: arrayStatus
            })

            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
        })

        $('.save-entry').click(function() {
            let modal = $('#modalEntry')
            let valid = validatorModal(modal.find('[name="id_barang"]').val(), $('[name="id"]').val())
            if (!valid.status) {
                $('#alertModal').text(valid.message).show()
                return false
            } else {
                $('#alertModal').text('').hide()
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
                details[indexSelect] = newObj
                detailSelect = 0
            }

            $('[name="details"]').val(JSON.stringify(details))
            statusModal = ''
            detailSelect = []

            resDataTable.clear().rows.add(details).draw()
            $('#modalEntry').modal('hide')
        })

        $('[name="status_qc"]').change(function() {
            $('[name="reason"]').attr('readonly', true)
            if ($(this).val() == 2) {
                $('[name="reason"]').attr('readonly', false).addClass('validate')
            } else if ($(this).val() == 3) {
                $('[name="reason"]').attr('readonly', false)
            } else {
                $('[name="reason"]').attr('readonly', true).removeClass('validate')
            }
        })

        $('.cancel-entry').click(function() {
            $('#modalEntry').modal('hide')
        })

        $('body').on('click', '.edit-entry', function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                if ($(v).hasClass('handle-number-4')) {
                    $(v).val(0).trigger('change')
                } else {
                    $(v).val('').trigger('change')
                }
            })

            $('#alertModal').text('').hide()
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })
            let id = $(this).data('id')
            statusModal = 'edit'
            let findItem = details.filter(p => p.id_barang == id)
            if (findItem.length > 0) {
                detailSelect = findItem[0]
                indexSelect = details.indexOf(detailSelect)
            } else {
                detailSelect = []
            }

            for (select in detailSelect) {
                if (['jumlah_pembelian_detail'].includes(select)) {
                    $('#qty').find('span').text(detailSelect['nama_satuan_barang'])
                }

                if (['id_barang'].includes(select)) {
                    let nameSelect = 'nama_barang';
                    $('[name="' + select + '"]').append('<option value="' + detailSelect[select] + '" selected>' +
                        detailSelect[nameSelect] + '</option>')
                }

                if (['status_qc'].includes(select)) {
                    $('[name="' + select + '"]').empty()
                    $('[name="' + select + '"]').select2({
                        data: [
                            arrayStatus[1],
                            arrayStatus[2],
                            arrayStatus[3]
                        ]
                    })
                }

                $('[name="' + select + '"]').val(detailSelect[select]).trigger('change')
            }

            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
        })

        function validatorModal(barang, id) {
            let message = 'Lengkapi inputan yang diperlukan'
            let valid = true
            $('#modalEntry').find('.validate').each(function(i, v) {
                if ($(v).val() == '') {
                    valid = false
                }

                if ($(v).prop('name') == 'id_barang') {
                    let findItem = details.filter(p => p.id_barang == $(v).val())
                    if (findItem.length > 0 && id == 0 && findItem[0].id_barang == barang) {
                        message = "Barang sudah ada dalam daftar"
                        valid = false
                    }
                }
            })

            return {
                'status': valid,
                'message': message
            }
        }
    </script>
@endsection
