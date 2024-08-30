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

        label.has-error {
            background: #fb434a;
            padding: 5px 8px;
            -webkit-border-radius: 3px;
            border-radius: 3px;
            position: absolute;
            right: 0;
            bottom: 37px;
            margin-bottom: 8px;
            max-width: 230px;
            font-size: 80%;
            z-index: 1;
            color: white;
            font-weight: normal;
        }

        label.has-error:after {
            width: 0px;
            height: 0px;
            content: '';
            display: block;
            border-style: solid;
            border-width: 5px 5px 0;
            border-color: #fb434a transparent transparent;
            position: absolute;
            right: 20px;
            bottom: -4px;
            margin-left: -5px;
        }

        .form-group.error {
            color: #fb434a;
        }

        .error input,
        .error textarea {
            border-color: #fb434a;
        }

        .form-group {
            margin-bottom: 5px;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Surat Jalan Umum
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="javascript:history.back()">Surat Jalan Umum</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('surat_jalan_umum-save-entry', $data ? $data->id : 0) }}" method="post"
            class="post-action-custom">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Surat Jalan Umum</h3>
                    <a href="javascript:history.back()" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-3">Tanggal <span>*</span></label>
                                <div class="form-group col-md-9">
                                    <div class="form-group">
                                        <input type="date"
                                            value="{{ old('tanggal', $data ? $data->tanggal : date('Y-m-d')) }}"
                                            name="tanggal" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Nomer Surat Jalan <span>*</span></label>
                                <div class="form-group col-md-9">
                                    <div class="form-group">
                                        <input type="text"
                                            value="{{ old('no_surat_jalan', $data ? $data->no_surat_jalan : '') }}"
                                            name="no_surat_jalan" placeholder="Otomatis" readonly class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Nomer Dokumen Lain</label>
                                <div class="form-group col-md-9">
                                    <div class="form-group">
                                        <input type="text"
                                            value="{{ old('no_dokumen_lain', $data ? $data->no_dokumen_lain : '') }}"
                                            name="no_dokumen_lain" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-3">Penerima <span>*</span></label>
                                <div class="form-group col-md-9">
                                    <div class="form-group">
                                        <input type="text" value="{{ old('penerima', $data ? $data->penerima : '') }}"
                                            name="penerima" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Alamat Penerima <span>*</span></label>
                                <div class="form-group col-md-9">
                                    <div class="form-group">
                                        <textarea name="alamat_penerima" class="form-control">{{ old('alamat_penerima', $data ? $data->alamat_penerima : '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Keterangan</label>
                                <div class="form-group col-md-9">
                                    <div class="form-group">
                                        <textarea name="keterangan" class="form-control">{{ old('keterangan', $data ? $data->keterangan : '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Detail Barang</h3>
                    <button class="btn btn-info add-entry btn-flat pull-right btn-sm" type="button">
                        <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                    </button>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <input type="hidden" name="details" value="{{ $data ? json_encode($data->formatdetail) : '[]' }}">
                        <input type="hidden" name="detele_details" value="[]">
                        <table id="table-detail" class="table table-bordered data-table display nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Keterangan</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <button class="btn btn-primary btn-flat pull-right btn-sm" type="submit">
                    <i class="glyphicon glyphicon-floppy-saved mr-1"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
    <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Detail Barang</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label for="" class="col-md-3">Nama Barang</label>
                        <div class="col-md-9 form-group">
                            <input type="text" class="form-control" name="nama_barang">
                        </div>
                    </div>
                    <div class="row">
                        <label for="" class="col-md-3">Jumlah</label>
                        <div class="col-md-9 form-group">
                            <input type="text" class="form-control" name="jumlah">
                        </div>
                    </div>
                    <div class="row">
                        <label for="" class="col-md-3">Satuan</label>
                        <div class="col-md-9 form-group">
                            <input type="text" class="form-control" name="satuan">
                        </div>
                    </div>
                    <div class="row">
                        <label for="" class="col-md-3">Keterangan</label>
                        <div class="col-md-9 form-group">
                            <textarea name="keterangan" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="text-right">
                        <input type="hidden" name="id">
                        <a href="javascript:void(0)" class="btn btn-primary btn-save">Simpan</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}?t={{ time() }}"></script>
@endsection

@section('externalScripts')
    <script>
        let details = {!! $data ? $data->details : '[]' !!};
        let deleteDetails = []
        let detailSelect = []
        let statusModal = 'create'
        $('.add-entry').click(function() {
            $('#modalEntry').modal('show')
            statusModal == 'create'
            $('#modalEntry').find('input,textarea').val('')
        })

        $('.btn-save').click(function() {
            let modal = $('#modalEntry')
            let array = []
            modal.find('input,textarea').each(function(i, v) {
                array[$(v).prop('name')] = $(v).val()
            })

            let newObj = Object.assign({}, array)
            if (statusModal == 'create') {
                details.push(newObj)
            } else if (statusModal == 'edit') {
                details[newObj.index - 1] = newObj
            }

            table.clear().rows.add(details).draw()
            $('[name="details"]').val(JSON.stringify(details))

            modal.find('input,textarea').val('')
            modal.modal('hide')
        })

        var table = $('#table-detail').DataTable({
            data: details,
            paging: false,
            ordering: false,
            columns: [{
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'jumlah',
                name: 'jumlah',
                width: 100
            }, {
                data: 'satuan',
                name: 'satuan',
                width: 0
            }, {
                data: 'keterangan',
                name: 'keterangan',
            }, {
                data: 'id',
                name: 'id',
                className: 'text-center',
                render: function(data, type, row, meta) {
                    let btn = '';
                    btn +=
                        '<a href="javascript:void(0)" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a>';
                    btn +=
                        '<a href="javascript:void(0)" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a>';

                    return btn;
                },
                orderable: false,
                searchable: false,
                width: 87
            }, ]
        });

        $.extend($.validator.messages, {
            required: "Tidak boleh kosong",
            email: "Pastikan format email sudah benar",
            number: "Pastikan hanya angka",
        });

        let validateForm = $(".post-action-custom").validate({
            rules: {
                date: "required",
                penerima: "required",
                alamat_penerima: 'required',
            },
            errorClass: 'has-error',
            highlight: function(element, errorClass, validClass) {
                $(element).parents("div.form-group").addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".error").removeClass('error');
            },
            submitHandler: function(form, e) {
                e.preventDefault()
                saveData($(form))
                return false;
            }
        });

        $('#table-detail').on('click', '.edit-entry', function() {
            let index = $(this).parents('tr').index()
            let selData = details[index]
            let modal = $('#modalEntry')
            modal.find('input,textarea').each(function(i, v) {
                let nameInput = $(v).prop('name')
                $(v).val(selData[nameInput])
            })

            modal.modal('show')
            statusModal = 'edit'
        })

        $('body').on('click', '.delete-entry', function() {
            let targetElement = $(this).parents('tr')
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
                    deleteDetails.push(details[targetElement.index()])

                    details.splice(targetElement.index(), 1);
                    table.clear().rows.add(details).draw()

                    $('[name="details"]').val(JSON.stringify(details))
                    $('[name="detele_details"]').val(JSON.stringify(deleteDetails))
                }
            })
        })
    </script>
@endsection
