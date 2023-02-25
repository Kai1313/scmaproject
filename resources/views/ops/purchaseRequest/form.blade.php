@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
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

        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Permintaan Pembelian</h3>
                <a href="{{ route('purchase-request') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                        class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali</a>
            </div>
            <div class="box-body">
                <form action="{{ route('purchase-request-save-entry', $data ? $data->purchase_request_id : 0) }}"
                    method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-3">Cabang <span>*</span></label>
                                <div class="col-md-5 form-group">
                                    <select name="id_cabang" class="form-control select2">
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
                                    <input type="date" name="purchase_request_date"
                                        value="{{ old('purchase_request_date', $data ? $data->purchase_request_date : date('Y-m-d')) }}"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3">Estimasi <span>*</span></label>
                                <div class="col-md-5 form-group">
                                    <input type="date" name="purchase_request_estimation_date"
                                        value="{{ old('purchase_request_estimation_date', $data ? $data->purchase_request_estimation_date : date('Y-m-d')) }}"
                                        class=" form-control">
                                </div>
                            </div>
                            @if ($data)
                                <div class="row">
                                    <label class="col-md-3">Status</label>
                                    <div class="col-md-5 form-group">
                                        @if (isset($arrayStatus[$data->approval_status]))
                                            <label
                                                class="{{ $arrayStatus[$data->approval_status]['class'] }}">{{ $arrayStatus[$data->approval_status]['text'] }}</label>
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
                                        data-route="{{ route('purchase-request-auto-werehouse') }}">
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
                                        data-route="{{ route('purchase-request-auto-user') }}">
                                        @if ($data && $data->purchase_request_user_id)
                                            <option value="{{ $data->purchase_request_user_id }}">
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
                    @if (!$data || $data->approval_status == 0)
                        <button class="btn btn-primary add-entry btn-flat" type="button"><i
                                class="glyphicon glyphicon-plus"></i> Tambah Barang</button>
                    @endif
                    <br><br>
                    <div class="table-responsive">
                        <input type="hidden" name="details">
                        <table class="table table-detail table-bordered">
                            <thead>
                                <tr>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Catatan</th>
                                    <th style="width:150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="target-table">
                                @if ($data)
                                    @foreach ($data->formatdetail as $detail)
                                        <tr data-index="{{ $detail->index }}">
                                            <td>{{ $detail->kode_barang }}</td>
                                            <td>{{ $detail->nama_barang }}</td>
                                            <td>{{ $detail->nama_satuan_barang }}</td>
                                            <td class="text-right tag-qty">{{ $detail->qty }}</td>
                                            <td>{{ $detail->notes }}</td>
                                            <td class="text-center">
                                                @if (!$data || $data->approval_status == 0)
                                                    <a href="javascript:void(0)"
                                                        class="btn btn-warning edit-entry btn-sm btn-flat">
                                                        <i class="glyphicon glyphicon-pencil"></i>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                        class="btn btn-danger delete-entry btn-sm btn-flat">
                                                        <i class="glyphicon glyphicon-trash"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    @if (!$data || $data->approval_status == 0)
                        <button class="btn btn-primary btn-flat pull-right" type="submit">
                            <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display:none;" id="alertModal">
                            Lengkapi Data yang diperlukan
                        </div>
                        <input type="hidden" name="index" value="0">
                        <label>Nama Barang</label>
                        <div class="form-group">
                            <select name="id_barang" class="form-control selectAjax validate"
                                data-route="{{ route('purchase-request-auto-item') }}">
                            </select>
                            <input type="hidden" name="nama_barang" class="validate">
                            <input type="hidden" name="kode_barang" class="validate">
                        </div>
                        <label>Satuan</label>
                        <div class="form-group">
                            <select name="id_satuan_barang" class="form-control select2 validate" disabled>
                            </select>
                            <input type="hidden" name="nama_satuan_barang" class="validate">
                        </div>
                        <label>Jumlah</label>
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
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
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

            let newObj = Object.assign({}, detailSelect)
            let html = '<tr data-index="' + newObj.index + '">' +
                '<td>' + newObj.kode_barang + '</td>' +
                '<td>' + newObj.nama_barang + '</td>' +
                '<td>' + newObj.nama_satuan_barang + '</td>' +
                '<td class="text-right tag-qty">' + formatNumber(newObj.qty) + '</td>' +
                '<td>' + newObj.notes + '</td>' +
                '<td class="text-center">' +
                '<a href="javascript:void(0)" class="btn btn-warning edit-entry btn-sm btn-flat"><i class="glyphicon glyphicon-pencil"></i></a>' +
                '<a href="javascript:void(0)" class="btn btn-danger delete-entry btn-sm btn-flat"><i class="glyphicon glyphicon-trash"></i></a>' +
                '</td>' +
                '</tr>'
            if (statusModal == 'create') {
                $('#target-table').append(html)
                details.push(newObj)
            } else if (statusModal == 'edit') {
                $('#target-table').find('[data-index="' + newObj.index + '"]').replaceWith(html)
                details[newObj.index - 1] = newObj
            }

            $('[name="details"]').val(JSON.stringify(details))

            statusModal = ''
            detailSelect = []
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
            let index = $(this).parents('tr').data('index')
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
            let parent = $(this).parents('tr')
            let index = parent.data('index')
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
                    details.splice(index - 1, 1)
                    parent.remove()
                    count -= 1

                    for (let i = 0; i < details.length; i++) {
                        $('#target-table').find('[data-index="' + details[i].index + '"]').attr(
                            'data-index',
                            i + 1)
                        details[i].index = i + 1
                    }

                    $('[name="details"]').val(JSON.stringify(details))
                }
            })
        })

        $(function() {
            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus()
            })

            $(document).on('focus', '.select2-selection.select2-selection--single', function(e) {
                $(this).closest(".select2-container").siblings('select:enabled').select2('open')
            })

            $('select.select2').on('select2:closing', function(e) {
                $(e.target).data("select2").$selection.one('focus focusin', function(e) {
                    e.stopPropagation();
                })
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
