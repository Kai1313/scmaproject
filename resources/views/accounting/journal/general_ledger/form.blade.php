@extends('layouts.main')

@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">

@endsection

@section('header')
    <section class="content-header">
        <h1>
            Transaksi Jurnal Umum
            <small>| Tambah</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('transaction-general-ledger') }}">Transaksi Jurnal Umum</a></li>
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
                                <h3 class="box-title">{{ (isset($data_jurnal_umum)?'Ubah':'Tambah') }} Jurnal Umum</h3>
                                <a href="{{ route('transaction-general-ledger') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                                        class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali</a>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <form id="form_slip" data-toggle="validator" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id_jurnal_umum" value="{{ old('id_jurnal_umum', (isset($data_jurnal_umum)) ? $data_jurnal_umum->id_jurnal_umum : '') }}">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Nama Jurnal Umum</label>
                                        <input type="text" class="form-control" id="nama_slip" name="nama_slip" placeholder="Masukkan nama jurnal umum" value="{{ isset($data_jurnal_umum->nama_jurnal_umum)?$data_jurnal_umum->nama_jurnal_umum:'' }}" data-validation="[NOTEMPTY]" data-validation-message="Nama Jurnal Umum tidak boleh kosong"  required>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Jurnal</label>
                                        <input type="date" class="form-control" id="date_jurnal" name="date_jurnal" placeholder="Masukkan tanggal jurnal umum" value="{{ isset($data_jurnal_umum->date_jurnal_umum)?$data_jurnal_umum->date_jurnal_umum:'' }}" data-validation="[NOTEMPTY]" data-validation-message="Tanggal Jurnal tidak boleh kosong" required>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Cabang</label>
                                        <select name="cabang_input" id="cabang_input" class="form-control select2" style="width: 100%;">
                                            @foreach ($data_cabang as $cabang)
                                                <option value="{{ $cabang->id_cabang }}" {{ isset($data_jurnal_umum->id_cabang)?(($data_jurnal_umum->id_cabang == $cabang->id_cabang)?'selected':''):'' }}>{{ $cabang->kode_cabang.' - '.$cabang->nama_cabang }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Akun</label>
                                        <select name="id_akun" class="form-control select2" id="id_akun"
                                        data-error="Wajib isi" data-validation="[NOTEMPTY]" data-validation-message="Akun tidak boleh kosong"  required>
                                            <option value="">Pilih Akun</option>
                                            @foreach ($data_akun as $akun)
                                                <option value="{{ $akun->id_akun }}"
                                                @if(isset($data_jurnal_umum)) @if ($data_jurnal_umum->id_akun == $akun->id_akun) selected @endif @endif>{{ $akun->kode_akun.' - '.$akun->nama_akun }}
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
                                            aria-hidden="true"></span> {{(isset($data_jurnal_umum)) ? ' Ubah Data' : ' Simpan Data'}}</button> --}}
                                    <button type="submit" id="tombol_buat"
                                        class="btn btn-flat btn-primary pull-right"><span class="glyphicon glyphicon-floppy-saved"
                                            aria-hidden="true"></span> {{(isset($data_jurnal_umum)) ? ' Ubah Data' : ' Simpan Data'}}</button>
                                    <button type="button" id="tombol_ubah" onclick="ubah_data()"
                                        class="btn btn-flat btn-warning pull-right sr-only"><span class="glyphicon glyphicon-pencil"
                                            aria-hidden="true"></span> Ubah Data</button>
                                </div>
                            </div>
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
        let save_route = "{{ route('transaction-general-ledger-store') }}"
        let data_route = "{{ route('transaction-general-ledger') }}"
        let slip = JSON.parse(JSON.stringify({!! (isset($data_jurnal_umum)) ? $data_jurnal_umum : '{}' !!}))
        if (Object.keys(slip).length > 0) {
            save_route = "{{ route('transaction-general-ledger-update') }}";
        }

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

        function save_data_old() {
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

        function save_data() {
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
                    }
                    else {
                        Swal.fire("Sorry, Can't save data. ", data.message, 'error')
                    }

                },
                error: function(data) {
                    Swal.fire("Sorry, Can't save data. ", data.responseJSON.message, 'error')
                }
            })
        }
    </script>
@endsection
