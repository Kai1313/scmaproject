@extends('layouts.main')
@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection
@section('header')
<section class="content-header">
    <h1>
        Master Chart of Account
        <small>| Create</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('master-coa') }}">Master CoA</a></li>
        <li class="active">Form</li>
    </ol>
</section>
@endsection

@section('main-section')
<section class="content container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <div class="row">
                        <div class="col-xs-12">
                            <h3 class="box-title">{{ (isset($akun->id_akun)?'Edit':'Add') }} Chart of Account</h3>
                            <a href="{{ route('master-coa') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali</a>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <form action="" id="form-akun" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Cabang</label>
                                    <select name="cabang" id="cabang" class="form-control select2" style="width: 100%;">
                                        @foreach ($data_cabang as $cabang)
                                            <option value="{{ $cabang->id_cabang }}" {{ isset($akun->id_cabang)?(($akun->id_cabang == $cabang->id_cabang)?'selected':''):'' }}>{{ $cabang->kode_cabang.' - '.$cabang->nama_cabang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Kode Akun</label>
                                    <input type="text" class="form-control" id="kode" name="kode" placeholder="Masukkan kode akun" value="{{ isset($akun->kode_akun)?$akun->kode_akun:'' }}" data-validation="[NOTEMPTY]" data-validation-message="Kode Akun tidak boleh kosong">
                                </div>
                                <div class="form-group">
                                    <label>Nama Akun</label>
                                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama akun" value="{{ isset($akun->nama_akun)?$akun->nama_akun:'' }}" data-validation="[NOTEMPTY]" data-validation-message="Nama Akun tidak boleh kosong">
                                </div>
                                <div class="form-group">
                                    <label>Tipe Akun</label>
                                    <select name="tipe" class="form-control select2" style="width: 100%;" data-validation="[NOTEMPTY]" data-validation-message="Tipe Akun tidak boleh kosong">
                                        <option value="">Tanpa Tipe</option>
                                        <option value="0" {{ (isset($akun->tipe_akun)?(($akun->tipe_akun == 0)?'selected':''):'') }}>Neraca</option>
                                        <option value="1" {{ (isset($akun->tipe_akun)?(($akun->tipe_akun == 1)?'selected':''):'') }}>Laba Rugi</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Parent</label>
                                    <select name="parent" id="parent" class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Parent</option>
                                        {{-- @foreach ($data_akun as $akuns)
                                            <option value="{{ $akuns->id_akun }}" {{ (isset($akun->id_parent)?(($akun->id_parent == $akuns->id_akun)?'selected':''):'') }}>{{ $akuns->kode_akun.' - '.$akuns->nama_akun }}</option>
                                        @endforeach --}}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Is Shown</label>
                                    <select name="shown" class="form-control select2" style="width: 100%;">
                                        <option value="0" {{ (isset($akun->isshown
                                        )?(($akun->isshown
                                         == 0)?'selected':''):'') }}>Tidak</option>
                                        <option value="1" {{ (isset($akun->isshown
                                        )?(($akun->isshown
                                         == 1)?'selected':''):'') }}>Tampil</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Header 1</label>
                                    <select name="header1" id="header1" class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Header</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Header 2</label>
                                    <select name="header2" id="header2" class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Header</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Header 3</label>
                                    <select name="header3" id="header3" class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Header</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Notes ...">{{ (isset($akun->catatan)?$akun->catatan:'') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                @if (!isset($akun))
                                    <button type="submit" id="store-btn" class="btn btn-flat btn-primary pull-right"><span class="glyphicon glyphicon-pencil"></span> Simpan</button>
                                @else
                                    <button type="submit" id="update-btn" data-idakun="{{ $akun->id_akun }}" class="btn btn-flat btn-primary pull-right"><span class="glyphicon glyphicon-pencil"></span> Update</button>    
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('addedScripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let coa_by_cabang_route = "{{ route('master-coa-get-by-cabang', ':id') }}"
        var validateCoa = {
            submit: {
                settings: {
                    form: '#form-akun',
                    inputContainer: '.form-group',
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
                        console.log(node.find(':submit'))
                        let button_status = node.find(":submit").attr("id")
                        console.log(button_status)
                        if (button_status == "store-btn") {
                            store_akun()
                            // console.log('store')
                        }
                        else {
                            update_akun(node.find(":submit").data("idakun"))
                            // console.log('update')
                        }
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
        $(function () {
            $.validate(validateCoa)

            getCoa()

            $('.select2').select2()

            $("#header1").select2({
                tags: true
            })

            $("#header2").select2({
                tags: true
            })

            $("#header3").select2({
                tags: true
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

            let header1 = "{{ (isset($akun->header1))?$akun->header1:'' }}"
            let header2 = "{{ (isset($akun->header2))?$akun->header2:'' }}"
            let header3 = "{{ (isset($akun->header3))?$akun->header3:'' }}"

            get_header1(header1)
            get_header2(header2)
            get_header3(header3)

            // On change cabang
            $("#cabang").on("change", function() {
                getCoa()
            })
        })

        function store_akun() {
            $.ajax({
                url: "{{ route('master-coa-store') }}",
                type: "POST",
                data: $("#form-akun").serialize(),
                dataType: "JSON",
                success: function(data) {
                    console.log(data)
                    if (data.result) {
                        Swal.fire('Tersimpan!', data.message, 'success').then((result) => {
                            if (result.isConfirmed) {
                                get_header1(data.akun.header1)
                                get_header2(data.akun.header2)
                                get_header3(data.akun.header3)
                                window.location.href = "{{ route('master-coa') }}";
                            }
                        })
                        
                    }
                    else {
                        Swal.fire("Gagal Menyimpan Data. ", data.message, 'error')
                    }
                },
                error: function(data) {
                    Swal.fire("Gagal Menyimpan Data. ", data.responseJSON.message, 'error')
                }
            })
        }

        function update_akun(id) {
            let url = "{{ route('master-coa-update', ":id") }}"
            url = url.replace(':id', id)
            $.ajax({
                url: url,
                type: "POST",
                data: $("#form-akun").serialize(),
                dataType: "JSON",
                success: function(data) {
                    console.log(data)
                    if (data.result) {
                        Swal.fire('Tersimpan!', data.message, 'success').then((result) => {
                            if (result.isConfirmed) {
                                get_header1(data.akun.header1)
                                get_header2(data.akun.header2)
                                get_header3(data.akun.header3)
                                window.location.href = "{{ route('master-coa') }}";
                            }
                        })
                        
                    }
                    else {
                        Swal.fire("Gagal Menyimpan Data. ", data.message, 'error')
                    }
                },
                error: function(data) {
                    Swal.fire("Gagal Menyimpan Data. ", data.responseJSON.message, 'error')
                }
            })
        }

        function get_header1(header1) {
            let url = "{{ route('master-coa-header1') }}"
            $.ajax({
                url: url,
                type: "GET",
                data: $("#form-akun").serialize(),
                dataType: "JSON",
                success: function(data) {
                    $("#header1").empty()
                    let options = data.options
                    let html = '<option value="">Tanpa Header</option>'
                    options.forEach(opt => {
                        let selected = (header1 == opt.header1)?'selected':''
                        if(opt.header1 != null) {
                            html += '<option value="'+opt.header1+'"'+selected+'>'+opt.header1+'</option>'
                        }
                    })
                    $("#header1").append(html)
                }
            })
        }

        function get_header2(header2) {
            let url = "{{ route('master-coa-header2') }}"
            $.ajax({
                url: url,
                type: "GET",
                data: $("#form-akun").serialize(),
                dataType: "JSON",
                success: function(data) {
                    $("#header2").empty()
                    let options = data.options
                    let html = '<option value="">Tanpa Header</option>'
                    options.forEach(opt => {
                        let selected = (header2 == opt.header2)?'selected':''
                        if(opt.header2 != null) {
                            html += '<option value="'+opt.header2+'"'+selected+'>'+opt.header2+'</option>'
                        }
                    })
                    $("#header2").append(html)
                }
            })
        }

        function get_header3(header3) {
            let url = "{{ route('master-coa-header3') }}"
            $.ajax({
                url: url,
                type: "GET",
                data: $("#form-akun").serialize(),
                dataType: "JSON",
                success: function(data) {
                    console.log(data)
                    $("#header3").empty()
                    let options = data.options
                    let html = '<option value="">Tanpa Header</option>'
                    options.forEach(opt => {
                        let selected = (header3 == opt.header3)?'selected':''
                        if(opt.header3 != null) {
                            html += '<option value="'+opt.header3+'"'+selected+'>'+opt.header3+'</option>'
                        }
                    })
                    $("#header3").append(html)
                }
            })
        }

        function getCoa() {
        let id_cabang = $("#cabang").val()
        let current_coa_route = coa_by_cabang_route.replace(':id', id_cabang);

        $.getJSON(current_coa_route, function(data) {
            if (data.result) {
                $('#parent').html('');

                let data_akun = data.data;
                let option_akun = '';

                option_akun += `<option value="">Tanpa Parent</option>`;
                data_akun.forEach(akun => {
                    option_akun +=
                        `<option value="${akun.id_akun}" data-nama="${akun.nama_akun}" data-kode="${akun.kode_akun}">${akun.kode_akun} - ${akun.nama_akun}</option>`;
                });

                $('#akun_detail').append(option_akun);
            }
        })
    }
    </script>
@endsection