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
    </style>
@endsection

@section('header')
    <p>{{ $data ? 'Edit' : 'Tambah' }} Permintaan Pembelian</p>
@endsection

@section('main-section')
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
    <form action="{{ route('purchase-request-save-entry', $data ? $data->purchase_request_id : 0) }}" method="post">
        <div class="panel">
            <div class="panel-body">
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
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-3">Gudang <span>*</span></label>
                            <div class="col-md-5 form-group">
                                <select name="id_gudang" class="form-control selectAjax"
                                    data-route="{{ route('purchase-request-auto-werehouse') }}">
                                    @if ($data && $data->id_gudang)
                                        <option value="{{ $data->id_gudang }}" selected>{{ $data->gudang->nama_gudang }}
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
                                <textarea name="catatan" class="form-control" rows="5">{{ old('catatan', $data ? $data->catatan : '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary add-entry" type="button">Tambah Barang</button>
                <div class="table-responsive">
                    <input type="hidden" name="details">
                    <table class="table">
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
                                @foreach ($data->details as $detail)
                                    <tr data-index="{{ $detail->index }}">
                                        <td>{{ $detail->barang->kode_barang }}</td>
                                        <td>{{ $detail->barang->nama_barang }}</td>
                                        <td>{{ $detail->satuan->nama_satuan_barang }}</td>
                                        <td>{{ $detail->qty }}</td>
                                        <td>{{ $detail->notes }}</td>
                                        <td>
                                            <a href="javascript:void(0)" class="btn btn-warning edit-entry">Edit</a>
                                            <a href="javascript:void(0)" class="btn btn-danger delete-entry">Hapus</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <a href="{{ route('purchase-request') }}" class="btn btn-default">Kembali</a>
            </div>
        </div>
    </form>

    <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="index" value="0">
                    <label>Nama Barang</label>
                    <div class="form-group">
                        <select name="id_barang" class="form-control selectAjax"
                            data-route="{{ route('purchase-request-auto-item') }}">
                        </select>
                        <input type="hidden" name="nama_barang">
                        <input type="hidden" name="kode_barang">
                    </div>
                    <label>Satuan</label>
                    <div class="form-group">
                        <select name="id_satuan_barang" class="form-control selectAjax"
                            data-route="{{ route('purchase-request-auto-satuan') }}">
                        </select>
                        <input type="hidden" name="nama_satuan_barang">
                    </div>
                    <label>Jumlah</label>
                    <div class="form-group">
                        <input type="number" name="jumlah" class="form-control">
                    </div>
                    <label>Catatan</label>
                    <div class="form-group">
                        <textarea name="notes" class="form-control" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel-entry">Batal</button>
                    <button type="button" class="btn btn-primary save-entry">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let details = []
        let detailSelect = []
        let count = details.length
        let statusModal = 'create'
        $('.select2').select2()
        if ($('[name="id_cabang"]').val() == '') {
            $('[name="id_gudang"]').prop('disabled', true)
        }

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
                }

                if (name == 'id_satuan_barang') {
                    $('#modalEntry').find('[name="nama_satuan_barang"]').val(dataselect.text)
                }
            });;
        })

        $('.add-entry').click(function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })

            statusModal = 'create'
            count += 1
            $('#modalEntry').find('[name="index"]').val(count)
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })
        })

        $('.save-entry').click(function() {
            let modal = $('#modalEntry')
            modal.find('input,select,textarea').each(function(i, v) {
                detailSelect[$(v).prop('name')] = $(v).val()
            })

            let newObj = Object.assign({}, detailSelect)
            let html = '<tr data-index="' + newObj.index + '">' +
                '<td>' + newObj.kode_barang + '</td>' +
                '<td>' + newObj.nama_barang + '</td>' +
                '<td>' + newObj.nama_satuan_barang + '</td>' +
                '<td>' + newObj.jumlah + '</td>' +
                '<td>' + newObj.notes + '</td>' +
                '<td>' +
                '<a href="javascript:void(0)" class="btn btn-warning edit-entry"><i class="glyphicon glyphicon-pencil"></i></a>' +
                '<a href="javascript:void(0)" class="btn btn-danger delete-entry"><i class="glyphicon glyphicon-trash"></i></a>' +
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
        })

        $('body').on('click', '.delete-entry', function() {
            let parent = $(this).parents('tr')
            let index = parent.data('index')
            details.splice(index - 1, 1)
            parent.remove()
            count -= 1

            for (let i = 0; i < details.length; i++) {
                $('#target-table').find('[data-index="' + details[i].index + '"]').attr('data-index', i + 1)
                details[i].index = i + 1
            }

            $('[name="details"]').val(JSON.stringify(details))
        })
    </script>
@endsection
