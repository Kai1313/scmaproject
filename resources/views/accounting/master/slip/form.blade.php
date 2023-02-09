@extends('layouts.main')

@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Master Slip
            <small>| Tambah</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('master-slip') }}">Master Slip</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-12">
                                <h3 class="box-title">{{ (isset($data_slip)?'Ubah':'Tambah') }} Slip</h3>
                                <a href="{{ route('master-slip') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                                        class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali</a>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <form id="form_slip" data-toggle="validator" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id_slip" value="{{ old('id_slip', (isset($data_slip)) ? $data_slip->id_slip : '') }}">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Cabang</label>
                                        <select name="cabang_input" id="cabang_input" class="form-control select2" style="width: 100%;">
                                            @foreach ($data_cabang as $cabang)
                                                <option value="{{ $cabang->id_cabang }}" {{ isset($data_slip->id_cabang)?(($data_slip->id_cabang == $cabang->id_cabang)?'selected':''):'' }}>{{ $cabang->kode_cabang.' - '.$cabang->nama_cabang }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Kode Slip</label>
                                        <input type="text" class="form-control" id="kode_slip" name="kode_slip" placeholder="Masukkan kode slip" value="{{ isset($data_slip->kode_slip)?$data_slip->kode_slip:'' }}" data-validation="[NOTEMPTY]" data-validation-message="Kode Slip tidak boleh kosong" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nama Slip</label>
                                        <input type="text" class="form-control" id="nama_slip" name="nama_slip" placeholder="Masukkan nama slip" value="{{ isset($data_slip->nama_slip)?$data_slip->nama_slip:'' }}" data-validation="[NOTEMPTY]" data-validation-message="Nama Slip tidak boleh kosong"  required>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Jenis Slip</label>
                                        <select name="jenis_slip" class="form-control select2" id="jenis_slip"
                                        data-error="Wajib isi" data-validation="[NOTEMPTY]" data-validation-message="Jenis Slip tidak boleh kosong"  required>
                                            <option value="">Pilih Slip</option>
                                            <option value="0">Kas</option>
                                            <option value="1">Bank</option>
                                            <option value="2">Piutang Giro</option>
                                            <option value="3">Hutang Giro</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Akun</label>
                                        <select name="id_akun" class="form-control select2" id="id_akun"
                                        data-error="Wajib isi" data-validation="[NOTEMPTY]" data-validation-message="Akun tidak boleh kosong"  required>
                                            <option value="">Pilih Akun</option>
                                            @foreach ($data_akun as $akun)
                                                <option value="{{ $akun->id_akun }}"
                                                @if(isset($data_slip)) @if ($data_slip->id_akun == $akun->id_akun) selected @endif @endif>{{ $akun->kode_akun.' - '.$akun->nama_akun }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <button type="button" id="tombol_refresh" onclick="refresh()"
                                    class="btn btn-default pull-left sr-only"><span class="glyphicon glyphicon-repeat"
                                        aria-hidden="true"></span> Ulangi</button>
                                    {{-- <button type="button" id="tombol_buat" onclick="save_data()"
                                        class="btn btn-flat btn-primary pull-right"><span class="glyphicon glyphicon-floppy-saved"
                                            aria-hidden="true"></span> {{(isset($data_slip)) ? ' Ubah Data' : ' Simpan Data'}}</button> --}}
                                    <button type="submit" id="tombol_buat"
                                        class="btn btn-flat btn-primary pull-right"><span class="glyphicon glyphicon-floppy-saved"
                                            aria-hidden="true"></span> {{(isset($data_slip)) ? ' Ubah Data' : ' Simpan Data'}}</button>
                                    <button type="button" id="tombol_ubah" onclick="ubah_data()"
                                        class="btn btn-flat btn-warning pull-right sr-only"><span class="glyphicon glyphicon-pencil"
                                            aria-hidden="true"></span> Ubah Data</button>
                                </div>
                            </div>
                            {{-- <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="cabang_input">Cabang*</label>
                                <div class="col-sm-10">
                                    <select name="cabang_input" id="cabang_input" class="form-control input-sm select2">
                                        @foreach ($data_cabang as $cab)
                                            <option value="{{ $cab->id_cabang }}" @if(isset($data_slip)) @if($cab->id_cabang == $data_slip->id_cabang) selected @endif @endif>{{ $cab->kode_cabang }} -
                                                {{ $cab->nama_cabang }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Cabang</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div> --}}
                            {{-- <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="kode_slip">Kode Slip*</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control input-sm" id="kode_slip" name="kode_slip"
                                        data-minlength="1" data-error="Wajib isi" placeholder="Masukkan Kode Slip"
                                        value="{{ old('kode_slip', (isset($data_slip)) ? $data_slip->kode_slip : '') }}" required>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Kode
                                        Slip</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div> --}}
                            {{-- <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="nama_slip">Nama Slip*</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control input-sm" id="nama_slip" name="nama_slip"
                                        data-minlength="1" maxlength="150" data-error="Wajib isi"
                                        placeholder="Masukkan Nama Slip"
                                        value="{{ old('nama_slip', (isset($data_slip)) ? $data_slip->nama_slip : '') }}" required>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Nama
                                        Slip</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div> --}}
                            {{-- <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="jenis_slip">Jenis Slip*</label>
                                <div class="col-sm-10">
                                    <select name="jenis_slip" class="form-control input-sm select2" id="jenis_slip"
                                        data-error="Wajib isi" required>
                                        <option value="">Pilih Slip</option>
                                        <option value="0">Kas</option>
                                        <option value="1">Bank</option>
                                        <option value="2">Piutang Giro</option>
                                        <option value="3">Hutang Giro</option>
                                    </select>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Slip</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div> --}}
                            {{-- <div class="form-group form-group-sm">
                                <label class="col-sm-2" for="id_akun">Akun*</label>
                                <div class="col-sm-10">
                                    <select name="id_akun" class="form-control input-sm select2" id="id_akun"
                                        data-error="Wajib isi" required placeholder="aaaaa">
                                        <option value="">Pilih Akun</option>
                                        @foreach ($data_akun as $akun)
                                            <option value="{{ $akun->id_akun }}"
                                               @if(isset($data_slip)) @if ($data_slip->id_akun == $akun->id_akun) selected @endif @endif>{{ $akun->nama_akun }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Akun</small>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div> --}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    {{-- Swal alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let save_route = "{{ route('master-slip-store') }}"
        let data_route = "{{ route('master-slip') }}"
        let slip = JSON.parse(JSON.stringify({!! (isset($data_slip)) ? $data_slip : '{}' !!}))
        if (Object.keys(slip).length > 0) {
            save_route = "{{ route('master-slip-update') }}";
        }
        var terminations = []
        var validateSlip = {
            submit: {
                settings: {
                    form: '#form_slip',
                    inputContainer: '.form-group',
                    // errorListContainer: 'help-block',
                    errorListClass: 'form-control-error',
                    errorClass: 'has-error',
                    allErrors: true,
                    scrollToError: {
                        offset: -100,
                        duration: 500
                    }
                },
                callback: {
                    onSubmit: function(node, formData) {
                        console.log('hello')
                        save_data()
                    }
                }
            },
            dynamic: {
                settings: {
                    trigger: 'keyup',
                    delay: 1000
                },
            }
        }

        $(function() {
            $.validate(validateSlip)
            $('.select2').select2({
                width: '100%'
            })

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus()
            })
            
            $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
                $(this).closest(".select2-container").siblings('select:enabled').select2('open')
            })

            $('select.select2').on('select2:closing', function (e) {
                $(e.target).data("select2").$selection.one('focus focusin', function (e) {
                    e.stopPropagation();
                })
            })

            if(Object.keys(slip).length > 0){
                $('#jenis_slip').val(slip.jenis_slip).trigger('change');
            }
        })

        function save_data() {
            Swal.fire({
                title: 'Do you want to save the changes?',
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
                    console.log('aaa')
                    $.ajax({
                        type: "POST",
                        url: save_route,
                        data: $('form').serialize(),
                        success: function(data) {
                            if (data.result) {
                                Swal.fire('Saved!', data.message, 'success').then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = data_route;
                                    }
                                })
                            } else {
                                Swal.fire("Sorry, Can't save data. ", data.message, 'error')
                            }

                        },
                        error: function(data) {
                            Swal.fire("Sorry, Can't save data. ", data.responseJSON.message, 'error')
                        }
                    });
                } else if (result.isDenied) {
                    Swal.fire('Changes are not saved', '', 'info')
                }
            })
        }
    </script>
@endsection
