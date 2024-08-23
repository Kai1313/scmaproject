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
                        <table id="table-detail" class="table table-bordered data-table display nowrap" width="100%">
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
                                    {{-- <th>Foto</th> --}}
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
                            <div class="show-after-search">
                                <label>Total <span>*</span></label>
                                <div class="form-group">
                                    <div class="input-group" id="qty">
                                        <input type="text" name="jumlah_pembelian_detail" class="form-control"
                                            readonly>
                                        <span class="input-group-addon">KG</span>
                                    </div>
                                    <input type="hidden" name="nama_satuan_barang" class="validate">
                                    <input type="hidden" name="id_satuan_barang" class="validate">
                                </div>
                                <div class="row">
                                    <label class="col-md-4">Status</label>
                                    <div class="form-group col-md-8">
                                        <div id="target-status">

                                        </div>
                                        <input name="label_status_qc" class="form-control validate" readonly
                                            style="display:none;">
                                        <input type="hidden" name="status_qc" value="">
                                    </div>
                                </div>
                                <label>Alasan</label>
                                <div class="form-group">
                                    <textarea name="reason" class="form-control" readonly></textarea>
                                </div>
                                <label>Tanggal QC</label>
                                <div class="form-group">
                                    <input type="date" name="tanggal_qc" class="form-control" value="">
                                </div>
                                <label>Upload Foto</label>
                                <input id="f_image" type="file" class="form-control" name="file_upload"
                                    accept=".png,.jpeg,.jpg">
                                <input type="hidden" name="image_path">
                                <img alt="" height="100" id="uploadPreview1" style="margin:10px;">
                            </div>
                        </div>
                        <div class="col-md-6 show-after-search">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>SG <span>*</span></label>
                                    <div class="form-group">
                                        <input type="text" name="sg_pembelian_detail"
                                            class="form-control handle-number-4 check-range validate" data-type="sg">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label>BE <span>*</span></label>
                                    <div class="form-group">
                                        <input type="text" name="be_pembelian_detail"
                                            class="form-control handle-number-4 check-range validate" data-type="be">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>PH <span>*</span></label>
                                    <div class="form-group">
                                        <input type="text" name="ph_pembelian_detail"
                                            class="form-control handle-number-4 check-range validate" data-type="ph">
                                    </div>
                                </div>
                            </div>
                            <label>Warna</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" name="warna_pembelian_detail" class="form-control" readonly>
                                    <span class="input-group-addon">
                                        <input type="checkbox" name="checkbox_warna" class="check-checkbox">
                                    </span>
                                </div>
                            </div>
                            <label>Bentuk</label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" name="bentuk_pembelian_detail" class="form-control" readonly>
                                    <span class="input-group-addon">
                                        <input type="checkbox" name="checkbox_bentuk" class="check-checkbox">
                                    </span>
                                </div>
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
                    <button type="button" class="btn btn-primary save-entry btn-flat show-after-search">Simpan</button>
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
        let dateNow = '{{ date('Y-m-d') }}'
        let items = []
        let details = [];
        let detailSelect = []
        let statusModal = 'create'
        let indexSelect = -1
        let paramQcSelect = []
        $('.select2').select2()

        var resDataTable = $('#table-detail').DataTable({
            scrollX: true,
            paging: false,
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
                    let index = data
                    return '<label class="' + arrayStatus[index]['class'] + '">' + arrayStatus[index][
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
                    if (row.status_qc == 3 || row.id == '') {
                        btn = '';
                        btn +=
                            '<a href="javascript:void(0)" data-id="' + data +
                            '" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a>';
                        btn +=
                            '<a href="javascript:void(0)" data-id="' + data +
                            '" class="btn btn-danger btn-xs mr-1 mb-1 remove-entry"><i class="glyphicon glyphicon-trash"></i></a>';
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
            $('[name="id_barang"]').empty()
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
                    items = res.list_item
                    $('[name="id_barang"]').empty()
                    $('[name="id_barang"]').select2({
                        data: [{
                            'id': "",
                            'text': 'Pilih Barang'
                        }, ...items]
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        paramQcSelect = dataselect
                        $('#qty').find('[name="jumlah_pembelian_detail"]').val(dataselect
                            .jumlah_pembelian_detail).trigger('input')
                        $('#qty').find('span').text(dataselect.nama_satuan_barang)
                        $('[name="nama_barang"]').val(dataselect.text)
                        $('[name="nama_satuan_barang"]').val(dataselect.nama_satuan_barang)
                        $('[name="id_satuan_barang"]').val(dataselect.id_satuan_barang)
                        $('[name="warna_pembelian_detail"]').val(dataselect.warna_qc_barang)
                        $('[name="bentuk_pembelian_detail"]').val(dataselect.bentuk_qc_barang)
                        $('[name="tanggal_qc"]').val(dateNow).prop('max', dateNow)
                        checkRangeQc()

                        if (dataselect.id) {
                            $('.show-after-search').css('display', 'inline')
                        } else {
                            $('.show-after-search').css('display', 'none')
                        }
                    });

                    details = res.qc
                    $('.btn-print').attr('href', res.route_print).css('display', 'block')
                    resDataTable.clear().rows.add(details).draw()
                    $('[name="details"]').val(JSON.stringify(details))
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    $('#cover-spin').hide()
                }
            })
        }

        $('.add-entry').click(function() {
            statusModal = 'create'
            detailSelect = []
            indexSelect = -1
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                if ($(v).hasClass('handle-number-4')) {
                    $(v).val(0).trigger('change')
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
            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })

            $('[name="id_barang"]').attr('readonly', false)
            $('.show-after-search').css('display', 'none')
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
                } else if ($(v).prop('type') == 'checkbox') {
                    detailSelect[$(v).prop('name')] = $(v).is(':checked') ? 1 : 0;
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
            console.log(details)
            statusModal = ''
            detailSelect = []

            resDataTable.clear().rows.add(details).draw()
            $('#modalEntry').modal('hide')
        })

        $('.cancel-entry').click(function() {
            $('#modalEntry').modal('hide')
        })

        $('body').on('click', '.remove-entry', function() {
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

            for (let i = 0; i < items.length; i++) {
                if (items[i]['id'] == detailSelect.id_barang) {
                    paramQcSelect = items[i]
                    break
                }
            }

            for (select in detailSelect) {
                if (['jumlah_pembelian_detail'].includes(select)) {
                    $('#qty').find('span').text(detailSelect['nama_satuan_barang'])
                }

                $('[name="' + select + '"]').val(detailSelect[select]).trigger('change')
            }

            checkRangeQc()
            $('[name="label_status_qc"]').val(arrayStatus[detailSelect.status_qc]['text'])
            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })

            $('[name="id_barang"]').attr('readonly', true)
            $('.show-after-search').css('display', 'inline')
        })

        $('#modalEntry').on('input', '.check-range', function() {
            checkRangeQc()
        })

        function validatorModal(barang, id) {
            let message = 'Lengkapi inputan yang diperlukan'
            let valid = true
            $('#modalEntry').find('.validate').each(function(i, v) {
                $(v).parent().removeClass('has-error')
                if ($(v).val() == '') {
                    $(v).parent().addClass('has-error')
                    valid = false
                }

                if ($(v).prop('name') == 'id_barang') {
                    let findItem = details.filter(p => p.id_barang == $(v).val())
                    if (findItem.length > 0 && id == '' && findItem[0].id_barang == barang && indexSelect < 0) {
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

        $('.check-checkbox').click(function() {
            checkRangeQc()
        })

        function checkRangeQc() {
            let countError = 0;
            $('.check-range').each(function(i, v) {
                let type = $(v).data('type')
                let val = $(v).val()

                if ($(v).parent().find('label')) {
                    $(v).parent().find('label').remove()
                }

                let value = val ? normalizeNumber(val) : 0
                if (type == 'sg') {
                    if (value < paramQcSelect.start_range_sg || value > paramQcSelect.final_range_sg) {
                        $(this).after('<label class="label label-danger">Rentang ' + paramQcSelect
                            .start_range_sg + ' - ' + paramQcSelect.final_range_sg + '</label>')
                        countError++
                    }
                }

                if (type == 'be') {
                    if (value < paramQcSelect.start_range_be || value > paramQcSelect.final_range_be) {
                        $(this).after('<label class="label label-danger">Rentang ' + paramQcSelect
                            .start_range_be + ' - ' + paramQcSelect.final_range_be + '</label>')
                        countError++
                    }
                }

                if (type == 'ph') {
                    if (value < paramQcSelect.start_range_ph || value > paramQcSelect.final_range_ph) {
                        $(this).after('<label class="label label-danger">Rentang ' + paramQcSelect
                            .start_range_ph + ' - ' + paramQcSelect.final_range_ph + '</label>')
                        countError++
                    }
                }
            })

            $('.check-checkbox').each(function(i, v) {
                if ($(v).parents('.form-group').find('label')) {
                    $(v).parents('.form-group').find('label').remove()
                }

                if (!$(v).is(':checked')) {
                    $(this).parents('.input-group').after('<label class="label label-danger">Tidak Sesuai</label>')
                    countError++
                }
            })

            let selectArray = []
            if (countError > 0) {
                selectArray = arrayStatus[2]
            } else {
                selectArray = arrayStatus[1]
            }

            $('#target-status').html('<label class="' + selectArray['class'] + '" style="font-size:20px;">' + selectArray[
                'text'] + '</label>')
            $('[name="status_qc"]').val(selectArray['id'])
            $('[name="label_status_qc"]').val(selectArray['text'])

            if (selectArray['id'] == 2) {
                $('[name="reason"]').attr('readonly', false).addClass('validate')
            } else if (selectArray['id'] == 3) {
                $('[name="reason"]').attr('readonly', false).addClass('validate')
            } else {
                $('[name="reason"]').attr('readonly', true).removeClass('validate')
            }
        }

        $('[name="file_upload"]').change(function() {
            if ($(this).val()) {
                let oFReader = new FileReader();
                let file = document.getElementById("f_image").files[0];
                if (file.type.match(/image.*/)) {
                    let reader = new FileReader();
                    reader.onload = function(readerEvent) {
                        let image = new Image();
                        image.onload = function(imageEvent) {
                            let canvas = document.createElement('canvas'),
                                max_size = 1000,
                                width = image.width,
                                height = image.height;
                            if (width > height) {
                                if (width > max_size) {
                                    height *= max_size / width;
                                    width = max_size;
                                }
                            } else {
                                if (height > max_size) {
                                    width *= max_size / height;
                                    height = max_size;
                                }
                            }
                            canvas.width = width;
                            canvas.height = height;
                            canvas.getContext('2d').drawImage(image, 0, 0, width, height);
                            let dataUrl = canvas.toDataURL('image/jpeg');
                            $('[name="image_path"]').val(dataUrl)
                            $('[name="file_upload"]').val('')
                            document.getElementById("uploadPreview1").src = dataUrl;
                        }
                        image.src = readerEvent.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            }
        })
    </script>
@endsection
