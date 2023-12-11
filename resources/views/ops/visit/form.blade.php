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

        .disabled {
            background: white;
            opacity: 0.5;
        }

        .select2-container .select2-selection--single {
            height: auto !important;
            padding: 9px 12px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            white-space: normal !important;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Kunjungan
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('visit') }}">Kunjungan</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <form action="{{ route('visit-save-entry', $data ? $data->id : 0) }}" method="post" class="post-action">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Edit' : 'Tambah' }} Kunjungan</h3>
                    <a href="{{ route('visit') }}" class="btn bg-navy btn-sm btn-default pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="row">
                                <label class="col-md-4">Cabang <span>*</span></label>
                                <div class="form-group col-md-8">
                                    <select name="id_cabang" class="form-control select2" data-validation="[NOTEMPTY]"
                                        data-validation-message="Cabang tidak boleh kosong" {{ $data ? 'readonly' : '' }}>
                                        <option value="">Pilih Cabang</option>
                                        @foreach ($cabang as $c)
                                            <option value="{{ $c->id }}"
                                                {{ $data && $data->id_cabang == $c->id ? 'selected' : '' }}>
                                                {{ $c->text }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-4">Tanggal Kunjungan <span>*</span></label>
                                <div class="form-group col-md-8">
                                    <input type="date" name="visit_date"
                                        value="{{ old('visit_date', $data ? $data->visit_date : date('Y-m-d')) }}"
                                        class="form-control datepicker" data-validation="[NOTEMPTY]"
                                        data-validation-message="Tanggal tidak boleh kosong">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <label class="col-md-4">Kode Kunjungan</label>
                                <div class="form-group col-md-8">
                                    <input type="text" name="visit_code"
                                        value="{{ old('visit_code', $data ? $data->visit_code : '') }}"
                                        class="form-control" readonly placeholder="Otomatis">
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-4">Salesman <span>*</span></label>
                                <div class="form-group col-md-8">
                                    <select name="id_salesman" class="form-control select2" readonly
                                        data-validation="[NOTEMPTY]" data-validation-message="Sales tidak boleh kosong">
                                        <option value="">Pilih Salesman</option>
                                        @if ($data && $data->id_salesman)
                                            <option value="{{ $data->id_salesman }}" selected>
                                                {{ $data->salesman->nama_salesman }}
                                            </option>
                                        @else
                                            @if ($salesman)
                                                <option value="{{ $salesman->id_salesman }}" selected>
                                                    {{ $salesman->nama_salesman }}</option>
                                            @endif
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <label class="col-md-4">Pelanggan <span>*</span></label>
                                <div class="form-group col-md-8">
                                    <select name="id_pelanggan" class="form-control select2" data-validation="[NOTEMPTY]"
                                        data-validation-message="Pelanggan tidak boleh kosong">
                                        <option value="">Pilih Pelanggan</option>
                                        @if ($data && $data->id_pelanggan)
                                            <option value="{{ $data->id_pelanggan }}" selected>
                                                {{ $data->pelanggan->nama_pelanggan }}</option>
                                        @endif
                                    </select>
                                    <a href="javascript:void(0)" class="show-customer">Tambah Pelanggan Baru</a>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-4">Catatan</label>
                                <div class="form-group col-md-8">
                                    <textarea name="pre_visit_desc" class="form-control" rows="3">{{ old('pre_visit_desc', $data ? $data->pre_visit_desc : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o mr-1"></i> Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="modal-customer-visit" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="" method="post" class="post-action">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Pelanggan</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <label for="" class="col-md-4">Nama Pelanggan</label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="nama_pelanggan">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Alamat</label>
                            <div class="form-group col-md-8">
                                <textarea name="alamat_pelanggan" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Kota</label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="kota_pelanggan">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Telepon</label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="telepon1_pelanggan">
                            </div>
                        </div>
                        <div class="row">
                            <label for="" class="col-md-4">Orang yang dihubungi</label>
                            <div class="form-group col-md-8">
                                <input type="text" class="form-control" name="kontak_person_pelanggan">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $('.select2').select2()
        let customer = {
            id: '{{ $data ? $data->id_pelanggan : '' }}',
            title: '{{ $data ? $data->pelanggan->nama_pelaggan : '' }}',
            address: '{{ $data ? $data->pelanggan->alamat_pelanggan : '' }}'
        }

        function formatData(data) {
            if (!data.id) {
                return data.text;
            }

            let $result = $('<div><b>' + data.text + '</b><br>' + (data.alamat_pelanggan != null ? data.alamat_pelanggan :
                customer.address) + '</div>');
            return $result;
        };

        $('[name="id_pelanggan"]').select2({
            templateResult: formatData,
            templateSelection: formatData,
            ajax: {
                url: "{{ route('visit-customer') }}",
                data: function(params) {
                    return {
                        search: params.term,
                    }
                },
                processResults: function(data) {
                    return {
                        results: [{
                            'id': '',
                            'text': 'Pilih Pelanggan'
                        }, ...data]
                    };
                }
            }
        })

        $('.show-customer').click(function(e) {
            e.preventDefault()
            console.log($('#modal-customer-visit').modal())
        })
    </script>
@endsection
