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
            Permintaan Pembelian
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Permintaan Pembelian</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        @if (session()->has('success'))
            <div class="alert alert-success">
                <ul>
                    <li>{!! session()->get('success') !!}</li>
                </ul>
            </div>
        @endif
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('purchase-request-save-entry', $data ? $data->purchase_request_id : 0) }}" method="post"
            class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Permintaan Pembelian</h3>
                    <a href="{{ route('purchase-request') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-3">Cabang <span>*</span></label>
                                <div class="col-md-5 form-group">
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
                            </div>
                            <div class="row">
                                <label class="col-md-3">Kode Permintaan</label>
                                <div class="col-md-9 form-group">
                                    <input type="text" name="purchase_request_code"
                                        value="{{ old('purchase_request_code', $data ? $data->purchase_request_code : '') }}"
                                        class="form-control" readonly placeholder="Otomatis">
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Tanggal <span>*</span></label>
                                <div class="col-md-5 form-group">
                                    <input type="text" name="purchase_request_date"
                                        value="{{ old('purchase_request_date', $data ? $data->purchase_request_date : date('Y-m-d')) }}"
                                        class="form-control datepicker" data-validation="[NOTEMPTY]"
                                        data-validation-message="Tanggal tidak boleh kosong">
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Estimasi <span>*</span></label>
                                <div class="col-md-5 form-group">
                                    <input type="text" name="purchase_request_estimation_date"
                                        value="{{ old('purchase_request_estimation_date', $data ? $data->purchase_request_estimation_date : date('Y-m-d')) }}"
                                        class=" form-control datepicker" data-validation="[NOTEMPTY]"
                                        data-validation-message="Tanggal estimasi tidak boleh kosong">
                                </div>
                            </div>
                            @if ($data)
                                <div class="row">
                                    <label class="col-md-3">Status</label>
                                    <div class="col-md-5 form-group">
                                        @if (isset($arrayStatus[$data->approval_status]))
                                            <label class="{{ $arrayStatus[$data->approval_status]['class'] }}">
                                                {{ $arrayStatus[$data->approval_status]['text'] }}
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-3">Gudang <span>*</span></label>
                                <div class="col-md-5 form-group">
                                    <select name="id_gudang" class="form-control selectAjax"
                                        data-route="{{ route('purchase-request-auto-werehouse') }}"
                                        data-validation="[NOTEMPTY]" data-validation-message="Gudang tidak boleh kosong">
                                        <option value="">Pilih Gudang</option>
                                        @if ($data && $data->id_gudang)
                                            <option value="{{ $data->id_gudang }}" selected>
                                                {{ $data->gudang->nama_gudang }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Pemohon <span>*</span></label>
                                <div class="col-md-5 form-group">
                                    <select name="purchase_request_user_id" class="form-control selectAjax"
                                        data-route="{{ route('purchase-request-auto-user') }}" data-validation="[NOTEMPTY]"
                                        data-validation-message="Pemohon tidak boleh kosong">
                                        <option value="">Pilih Pemohon</option>
                                        @if ($data && $data->purchase_request_user_id)
                                            <option value="{{ $data->purchase_request_user_id }}" selected>
                                                {{ $data->pengguna->nama_pengguna }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Catatan</label>
                                <div class="col-md-9 form-group">
                                    <textarea name="catatan" class="form-control" rows="3">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Detil Permintaan Barang</h4>
                        </div>
                        <div class="col-md-6">
                            @if (!$data || $data->approval_status == 0)
                                <button class="btn btn-info add-entry btn-flat pull-right" type="button">
                                    <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="table-responsive">
                        <input type="hidden" name="details"
                            value="{{ $data ? json_encode($data->formatdetail) : '[]' }}">
                        <table id="table-detail" class="table table-bordered data-table">
                            <thead>
                                <tr>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Catatan</th>
                                    <th>Stok</th>
                                    <th style="width:150px;">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    @if (!$data || $data->approval_status == 0)
                        <button class="btn btn-primary btn-flat pull-right" type="submit">
                            <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                        </button>
                    @endif
                </div>
            </div>
        </form>

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display:none;" id="alertModal">
                            Lengkapi Data yang diperlukan
                        </div>
                        <input type="hidden" name="index" value="0">
                        <label>Nama Barang <span>*</span></label>
                        <div class="form-group">
                            <select name="id_barang" class="form-control selectAjax validate"
                                data-route="{{ route('purchase-request-auto-item') }}">
                            </select>
                            <input type="hidden" name="nama_barang" class="validate">
                            <input type="hidden" name="kode_barang" class="validate">
                        </div>
                        <label>Satuan <span>*</span></label>
                        <div class="form-group">
                            <select name="id_satuan_barang" class="form-control select2 validate" disabled>
                            </select>
                            <input type="hidden" name="nama_satuan_barang" class="validate">
                        </div>
                        <label>Jumlah <span>*</span></label>
                        <div class="form-group">
                            <input type="text" name="qty" class="form-control validate handle-number-4">
                        </div>
                        <label>Catatan</label>
                        <div class="form-group">
                            <textarea name="notes" class="form-control" rows="5"></textarea>
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
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let detailSelect = []
        let count = details.length
        let statusModal = 'create'
        $('.select2').select2()

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        var resDataTable = $('#table-detail').DataTable({
            data: details,
            ordering: false,
            columns: [{
                    data: 'kode_barang',
                    name: 'kode_barang'
                },
                {
                    data: 'nama_barang',
                    name: 'nama_barang'
                },
                {
                    data: 'nama_satuan_barang',
                    name: 'nama_satuan_barang'
                },
                {
                    data: 'qty',
                    name: 'qty',
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
                },
                {
                    data: 'notes',
                    name: 'notes'
                },
                {
                    data: 'stok',
                    name: 'stok',
                    render: $.fn.dataTable.render.number('.', ',', 4),
                    className: 'text-right'
                },
                {
                    data: 'index',
                    className: 'text-center',
                    name: 'index',
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

        if ($('[name="id_cabang"]').val() == '') {
            $('[name="id_gudang"]').prop('disabled', true)
        }

        $('.tag-qty').each(function(i, v) {
            let num = $(v).text()
            $(this).text(formatNumber(num))
        })

        $('[name="id_cabang"]').change(function() {
            let self = $('[name="id_gudang"]')
            if ($('[name="id_cabang"]').val() == '') {
                self.val('').prop('disabled', true).trigger('change')
            } else {
                self.val('').prop('disabled', false).trigger('change')
            }
        })

        $('.selectAjax').each(function(i, v) {
            let route = $(v).data('route')
            let name = $(v).prop('name')
            $(v).select2({
                ajax: {
                    url: route,
                    dataType: 'json',
                    data: function(params) {
                        if (name == 'id_gudang') {
                            return {
                                search: params.term,
                                id_cabang: $('[name="id_cabang"]').val()
                            }
                        } else {
                            return {
                                search: params.term
                            }
                        }
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    }
                }
            }).on('select2:select', function(e) {
                let dataselect = e.params.data
                if (name == 'id_barang') {
                    $('#modalEntry').find('[name="nama_barang"]').val(dataselect.text)
                    $('#modalEntry').find('[name="kode_barang"]').val(dataselect.kode_barang)
                    $('[name="id_satuan_barang"]').html('')
                    getSatuan(dataselect.id)
                }
            });
        })

        function getSatuan(id) {
            $.ajax({
                url: "{{ route('purchase-request-auto-satuan') }}?item=" + id,
                type: 'get',
                success: function(res) {
                    $('[name="id_satuan_barang"]').prop('disabled', false).select2({
                        data: [{
                            id: '',
                            text: 'Pilih Satuan'
                        }, ...res]
                    }).on('select2:select', function(e) {
                        let dataselect = e.params.data
                        $('#modalEntry').find('[name="nama_satuan_barang"]').val(dataselect.text)
                    });
                },
                error: function(error) {
                    console.log(error)
                }
            })
        }

        $('.add-entry').click(function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })

            $('#alertModal').hide()
            statusModal = 'create'
            count += 1
            $('#modalEntry').find('[name="index"]').val(count)
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })

            $('[name="id_barang"]').select2('open')

            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
        })

        $('.save-entry').click(function() {
            let valid = validatorModal()
            if (!valid.status) {
                $('#alertModal').show()
                return false
            } else {
                $('#alertModal').hide()
            }

            let modal = $('#modalEntry')
            modal.find('input,select,textarea').each(function(i, v) {
                if ($(v).hasClass('handle-number-4')) {
                    detailSelect[$(v).prop('name')] = normalizeNumber($(v).val())
                } else {
                    detailSelect[$(v).prop('name')] = $(v).val()
                }
            })

            detailSelect['stok'] = null

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
            if (statusModal == 'create') {
                count -= 1
            }
        })

        $('body').on('click', '.edit-entry', function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })

            $('#alertModal').hide()
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })
            let index = $(this).data('index')
            statusModal = 'edit'
            detailSelect = details[index - 1]
            for (select in detailSelect) {
                if (['id_barang', 'id_satuan_barang'].includes(select)) {
                    let nameSelect = (select == 'id_barang') ? 'nama_barang' : 'nama_satuan_barang';
                    $('[name="' + select + '"]').append('<option value="' + detailSelect[select] + '" selected>' +
                        detailSelect[nameSelect] + '</option>')
                }

                $('[name="' + select + '"]').val(detailSelect[select]).trigger('change')
            }

            $('.handle-number-4').each(function(i, v) {
                let val = $(v).val().replace('.', ',')
                $(v).val(formatRupiah(val, 4))
            })
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
                    count -= 1

                    for (let i = 0; i < details.length; i++) {
                        details[i].index = i + 1
                    }

                    resDataTable.clear().rows.add(details).draw()
                    $('[name="details"]').val(JSON.stringify(details))
                }
            })
        })

        function validatorModal() {
            let valid = true
            $('#modalEntry').find('.validate').each(function(i, v) {
                if ($(v).val() == '') {
                    valid = false
                }
            })

            return {
                'status': valid
            }
        }
    </script>
@endsection
