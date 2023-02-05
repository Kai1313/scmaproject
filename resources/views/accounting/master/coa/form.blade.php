@extends('layouts.main')
@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
@section('header')
<section class="content-header">
    <h1>
        Master CoA
        <small>Chart of Account | Create</small>
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
            <a href="{{ route('master-coa') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">Back</a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Add Chart of Account</h3>
                </div>
                <div class="box-body">
                    <form action="" id="form-akun" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Cabang</label>
                                    <select name="cabang" class="form-control select2" style="width: 100%;">
                                        @foreach ($data_cabang as $cabang)
                                            <option value="{{ $cabang->id_cabang }}" {{ isset($akun->id_cabang)?(($akun->id_cabang == $cabang->id_cabang)?'selected':''):'' }}>{{ $cabang->kode_cabang.' - '.$cabang->nama_cabang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Kode Akun</label>
                                    <input type="text" class="form-control" id="kode" name="kode" placeholder="Masukkan kode akun" value="{{ isset($akun->kode_akun)?$akun->kode_akun:'' }}">
                                </div>
                                <div class="form-group">
                                    <label>Nama Akun</label>
                                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama akun" value="{{ isset($akun->nama_akun)?$akun->nama_akun:'' }}">
                                </div>
                                <div class="form-group">
                                    <label>Tipe Akun</label>
                                    <select name="tipe" class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Tipe</option>
                                        <option value="0" {{ (isset($akun->tipe_akun)?(($akun->tipe_akun == 0)?'selected':''):'') }}>Neraca</option>
                                        <option value="1" {{ (isset($akun->tipe_akun)?(($akun->tipe_akun == 1)?'selected':''):'') }}>Laba Rugi</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Parent</label>
                                    <select name="parent" class="form-control select2" style="width: 100%;">
                                        <option value="">Tanpa Parent</option>
                                        @foreach ($data_akun as $akuns)
                                            <option value="{{ $akuns->id_akun }}" {{ (isset($akun->id_parent)?(($akun->id_parent == $akuns->id_akun)?'selected':''):'') }}>{{ $akuns->kode_akun.' - '.$akuns->nama_akun }}</option>
                                        @endforeach
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
                                    <button type="button" id="store-btn" class="btn btn-primary pull-right">Simpan</button>
                                @else
                                    <button type="button" id="update-btn" data-idakun="{{ $akun->id_akun }}" class="btn btn-primary pull-right">Update</button>    
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
@endsection

@section('externalScripts')
    <script>
        $(function () {
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

            let header1 = "{{ (isset($akun->header1))?$akun->header1:'' }}"
            let header2 = "{{ (isset($akun->header2))?$akun->header2:'' }}"
            let header3 = "{{ (isset($akun->header3))?$akun->header3:'' }}"

            get_header1(header1)
            get_header2(header2)
            get_header3(header3)

            $("#store-btn").on("click", function(e) {
                e.preventDefault()
                store_akun()
            })

            $("#update-btn").on("click", function(e) {
                e.preventDefault()
                update_akun($("#update-btn").data("idakun"))
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
                    get_header1(data.akun.header1)
                    get_header2(data.akun.header2)
                    get_header3(data.akun.header3)
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    })
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
                    get_header1(data.akun.header1)
                    get_header2(data.akun.header2)
                    get_header3(data.akun.header3)
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    })
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
    </script>
@endsection