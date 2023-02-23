@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        label>span {
            color: red;
        }

        .label-checkbox {
            margin-right: 10px;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Biaya
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-slip') }}">Master Biaya</a></li>
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
        <form action="{{ route('master-biaya-save-entry', $data ? $data->id_biaya : 0) }}" method="post">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Biaya</h3>
                    <a href="{{ route('master-biaya') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                            class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali</a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cabang <span>*</span></label>
                                <select name="id_cabang" class="form-control select2">
                                    <option value="">Pilih Cabang</option>
                                    @foreach ($cabang as $branch)
                                        <option value="{{ $branch->id_cabang }}"
                                            {{ old('id_cabang', $data ? $data->id_cabang : '') == $branch->id_cabang ? 'selected' : '' }}>
                                            {{ $branch->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nama Biaya <span>*</span></label>
                                <input type="text" class="form-control" name="nama_biaya"
                                    value="{{ old('nama_biaya', $data ? $data->nama_biaya : '') }}">
                            </div>
                            <div class="form-group">
                                <label>Akun Biaya <span>*</span></label>
                                <select name="id_akun_biaya" class="form-control select2">
                                    <option value="">Pilih Akun Biaya</option>
                                    @foreach ($akunBiaya as $biaya)
                                        <option value="{{ $biaya->id_akun }}"
                                            {{ old('id_akun_biaya', $data ? $data->id_akun_biaya : '') == $biaya->id_akun ? 'selected' : '' }}>
                                            {{ $biaya->kode_akun }} - {{ $biaya->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="label-checkbox">PPn</label>
                                <input type="checkbox" name="isppn" value="1"
                                    {{ old('isppn', $data ? $data->isppn : '') ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="label-checkbox">PPh</label>
                                <input type="checkbox" name="ispph" value="1"
                                    {{ old('ispph', $data ? $data->ispph : '') ? 'checked' : '' }}>
                            </div>
                            <div class="form-group">
                                <label>Nilai PPh</label>
                                <input type="number" name="value_pph" class="form-control show-pph"
                                    value="{{ old('value_pph', $data ? $data->value_pph : '') }}" step=".01">
                            </div>
                            <div class="form-group">
                                <label>Akun PPh</label>
                                <select name="id_akun_pph" class="form-control select2 show-pph">
                                    <option value="">Pilih Akun PPh</option>
                                    @foreach ($akunBiaya as $pph)
                                        <option value="{{ $pph->id_akun }}"
                                            {{ old('id_akun_biaya', $data ? $data->id_akun_pph : '') == $pph->id_akun ? 'selected' : '' }}>
                                            {{ $pph->kode_akun }} - {{ $pph->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="label-checkbox">Aktif <span>*</span></label>
                                <input type="checkbox" name="aktif" value="1"
                                    {{ old('aktif', $data ? $data->aktif : 1) ? 'checked' : '' }}>
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
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        // $('.handle-number-2').trigger('input')
        $('[name="ispph"]').change(function() {
            if ($(this).is(':checked')) {
                $('.show-pph').prop('disabled', false)
            } else {
                $('.show-pph').prop('disabled', true).val('').trigger('change')
            }
        })

        $('.select2').select2()
        if ($('[name="ispph"]').is(':checked')) {
            $('.show-pph').prop('disabled', false)
        } else {
            $('.show-pph').prop('disabled', true)
        }

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

        $(document).on('input', '.handle-number-2', function() {
            let str = $(this).val()
            str = str.replace(/^\,/, '')
            str = str.match(/(^[0-9]*(\,?)+([0-9]{1,4})?)/)
            $(this).val(str[0])
        })
    </script>
@endsection
