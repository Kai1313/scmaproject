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
        <form action="{{ route('qc_receipt-save-entry', 0) }}" method="post" class="post-action">
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
                                <label>Cabang</label>
                                <input type="text" class="form-control" value="{{ $data->nama_cabang }}" readonly>
                                <input type="hidden" name="id_cabang" value="{{ $data->id_cabang }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No Bukti Penerimaan</label>
                                <input type="text" name="nama_pembelian" value="{{ $data->nama_pembelian }}" readonly
                                    class="form-control">
                                <input type="hidden" name="id_pembelian" value="{{ $data->id_pembelian }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nama Supplier</label>
                                <input type="text" class="form-control" name="pemasok" readonly
                                    value="{{ $data->nama_pemasok }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No PO</label>
                                <input type="text" class="form-control" name="po" readonly
                                    value="{{ $data->nomor_po_pembelian }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Penerimaan</label>
                                <input type="text" class="form-control" name="tanggal" readonly
                                    value="{{ $data->tanggal_pembelian }}">
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
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" name="id" value="0">
                            <label>Nama Barang</label>
                            <div class="form-group">
                                <input type="text" name="nama_barang" class="form-control"
                                    value="{{ $data->nama_barang }}" readonly>
                                <input type="hidden" name="id_barang" value="{{ $data->id_barang }}">
                            </div>
                            <div class="show-after-search">
                                <label>Total</label>
                                <div class="form-group">
                                    <div class="input-group" id="qty">
                                        <input type="text" name="jumlah_pembelian_detail"
                                            class="form-control handle-number-4" readonly
                                            value="{{ $data->jumlah_pembelian_detail }}">
                                        <span class="input-group-addon">{{ $data->nama_satuan_barang }}</span>
                                    </div>
                                    <input type="hidden" name="id_satuan_barang" class="validate"
                                        value="{{ $data->id_satuan_barang }}">
                                </div>
                                <div class="row">
                                    <label class="col-md-4">Status</label>
                                    <div class="form-group col-md-8">
                                        <div id="target-status">

                                        </div>
                                        <input type="hidden" name="status_qc"
                                            value="{{ $data->id ? $data->status_qc : '' }}">
                                    </div>
                                </div>
                                <label>Alasan <span>*</span></label>
                                <div class="form-group">
                                    <textarea name="reason" class="form-control" readonly>{{ $data->id ? $data->reason : '' }}</textarea>
                                </div>
                                <label>Tanggal QC <span>*</span></label>
                                <div class="form-group">
                                    <input type="date" name="tanggal_qc" class="form-control"
                                        value="{{ date('Y-m-d') }}"
                                        max="{{ $data->id ? $data->tanggal_qc : date('Y-m-d') }}">
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
                                <div class="col-sm-4">
                                    <label>SG <span>*</span></label>
                                    <div class="form-group">
                                        <input type="text" name="sg_pembelian_detail"
                                            class="form-control handle-number-4 check-range validate" data-type="sg"
                                            value="{{ $data->id ? $data->sg_pembelian_detail : 0 }}">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label>BE <span>*</span></label>
                                    <div class="form-group">
                                        <input type="text" name="be_pembelian_detail"
                                            class="form-control handle-number-4 check-range validate" data-type="be"
                                            value="{{ $data->id ? $data->be_pembelian_detail : 0 }}">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label>PH <span>*</span></label>
                                    <div class="form-group">
                                        <input type="text" name="ph_pembelian_detail"
                                            class="form-control handle-number-4 check-range validate" data-type="ph"
                                            value="{{ $data->id ? $data->ph_pembelian_detail : 0 }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Warna</label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="warna_pembelian_detail" class="form-control"
                                                readonly value="{{ $data->warna_qc_barang }}">
                                            <span class="input-group-addon">
                                                <input type="checkbox" name="checkbox_warna" class="check-checkbox"
                                                    {{ $data->id && $data->warna_pembelian_detail == $data->warna_qc_barang ? 'checked' : '' }}
                                                    value="1">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Bentuk</label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="bentuk_pembelian_detail" class="form-control"
                                                readonly value="{{ $data->bentuk_qc_barang }}">
                                            <span class="input-group-addon">
                                                <input type="checkbox" name="checkbox_bentuk" class="check-checkbox"
                                                    {{ $data->id && $data->bentuk_pembelian_detail == $data->bentuk_qc_barang ? 'checked' : '' }}
                                                    value="1">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Hasil Implementasi</label>
                                    <div class="form-group" style="margin-bottom:0px;">
                                        <select name="trial_pembelian_detail" class="form-control">
                                            <option value="">Pilih Hasil</option>
                                            <option value="1"
                                                {{ $data->id && $data->trial_pembelian_detail == '1' ? 'selected' : '' }}>
                                                OK</option>
                                            <option value="0"
                                                {{ $data->id && $data->trial_pembelian_detail == '0' ? 'selected' : '' }}>
                                                Tidak OK</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <label>Keterangan</label>
                            <div class="form-group">
                                <textarea name="keterangan_pembelian_detail" class="form-control">{{ $data->id ? $data->keterangan_pembelian_detail : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary pull-right btn-flat" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>
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
        let arrayStatus = {!! json_encode($arrayStatus) !!};
        let data = {!! json_encode($data) !!}
        checkRangeQc()

        $('body').on('input', '.check-range', function() {
            checkRangeQc()
        })

        $('.check-checkbox').click(function() {
            checkRangeQc()
        })

        $('[name="trial_pembelian_detail"]').change(function() {
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
                    if (value < data.start_range_sg || value > data.final_range_sg) {
                        $(this).after('<label class="label label-danger">Rentang ' + data
                            .start_range_sg + ' - ' + data.final_range_sg + '</label>')
                        countError++
                    }
                }

                if (type == 'be') {
                    if (value < data.start_range_be || value > data.final_range_be) {
                        $(this).after('<label class="label label-danger">Rentang ' + data
                            .start_range_be + ' - ' + data.final_range_be + '</label>')
                        countError++
                    }
                }

                if (type == 'ph') {
                    if (value < data.start_range_ph || value > data.final_range_ph) {
                        $(this).after('<label class="label label-danger">Rentang ' + data
                            .start_range_ph + ' - ' + data.final_range_ph + '</label>')
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

            let trial = $('[name="trial_pembelian_detail"]')
            if (trial.parents('.form-group').find('label')) {
                trial.parents('.form-group').find('label').remove()
            }

            if (trial.val() != 1) {
                trial.after('<label class="label label-danger">Tidak Sesuai</label>')
                countError++
            }

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
