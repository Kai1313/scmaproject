@extends('layouts.main')

@section('addedStyles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .mt-1 {
            margin-top: .25rem !important;
        }

        .mt-2 {
            margin-top: .5rem !important;
        }

        .mt-4 {
            margin-top: 1rem !important;
        }

        .mb-1 {
            margin-bottom: .25rem !important;
        }

        .mb-4 {
            margin-bottom: 1rem !important;
        }

        ul#horizontal-list {
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        ul#horizontal-list li {
            display: inline;
        }
    </style>
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
        <form id="form_ledger" data-toggle="validator" enctype="multipart/form-data">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-primary">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-12">
                                    <h3 class="box-title">{{ isset($data_jurnal_umum) ? 'Ubah' : 'Tambah' }} Jurnal Umum</h3>
                                    <a href="{{ route('transaction-general-ledger') }}"
                                        class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span
                                            class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span>
                                        Kembali</a>
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            <form id="form_ledger" data-toggle="validator" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cabang</label>
                                            <select name="cabang_input" id="cabang_input" class="form-control select2"
                                                style="width: 100%;">
                                                @foreach ($data_cabang as $cabang)
                                                    <option value="{{ $cabang->id_cabang }}"
                                                        {{ isset($data_jurnal_umum->id_cabang) ? ($data_jurnal_umum->id_cabang == $cabang->id_cabang ? 'selected' : '') : '' }}>
                                                        {{ $cabang->kode_cabang . ' - ' . $cabang->nama_cabang }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Nomor Jurnal</label>
                                            <input type="text" class="form-control" id="kode" name="kode"
                                                placeholder="Masukkan nomor jurnal umum" value=""
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Nomor Jurnal Umum tidak boleh kosong">
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Jurnal</label>
                                            <input type="date" class="form-control" id="tanggal" name="tanggal"
                                                placeholder="Masukkan tanggal jurnal umum" value="{{ date('d/m/Y') }}"
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Tanggal Jurnal tidak boleh kosong">
                                        </div>
                                        <div class="form-group">
                                            <label>Jenis Jurnal</label>
                                            <select name="jenis" id="jenis" class="form-control select2"
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Jenis Jurnal tidak boleh kosong">
                                                <option value="">Pilih Jenis Jurnal</option>
                                                <option value="KK">Kas Keluar</option>
                                                <option value="KM">Kas Masuk</option>
                                                <option value="BK">Bank Keluar</option>
                                                <option value="BM">Bank Masuk</option>
                                                <option value="PG">Piutang Giro</option>
                                                <option value="HG">Hutang Giro</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Slip</label>
                                            <select name="slip" id="slip" class="form-control select2"
                                                data-validation="[NOTEMPTY]" data-validation-message="Slip tidak boleh kosong">
                                                {{-- <option value="">Pilih Slip</option> --}}
                                                {{-- @foreach ($data_slip as $slip)
                                                    <option value="{{ $slip->kode_slip }}">{{ $slip->kode_slip.' - '.$slip->nama_slip }}</option>
                                                @endforeach --}}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nomor Giro</label>
                                            <input type="text" name="nomor_giro" id="nomor_giro" class="form-control"
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Nomor giro tidak boleh kosong" disabled>
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Giro</label>
                                            <input type="date" class="form-control" id="tanggal_giro" name="tanggal_giro"
                                                placeholder="Masukkan tanggal giro" value="{{ date('d/m/Y') }}"
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Tanggal Giro tidak boleh kosong">
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal JT Giro</label>
                                            <input type="date" class="form-control" id="tanggal_jt_giro"
                                                name="tanggal_jt_giro" placeholder="Masukkan tanggal jatuh tempo giro"
                                                value="{{ date('d/m/Y') }}" data-validation="[NOTEMPTY]"
                                                data-validation-message="Tanggal JT Giro tidak boleh kosong">
                                        </div>
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea name="notes" class="form-control" rows="4" placeholder="Notes ..."></textarea>
                                        </div>
                                        <button id="hidden-btn" style="display:none;" type="submit">HIDDEN</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-primary">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-12">
                                    <h3 class="box-title">Detail Jurnal</h3>
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            <form id="form_detail" action="" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Akun</label>
                                            <select name="akun_detail" class="form-control select2" id="akun_detail"
                                            data-error="Wajib isi" data-validation="[NOTEMPTY]" data-validation-message="Akun tidak boleh kosong"  required>
                                                <option value="">Pilih Akun</option>
                                                {{-- @foreach ($data_akun as $akunDt)
                                                    <option value="{{ $akunDt->id_akun }}">{{ $akunDt->kode_akun.' - '.$akunDt->nama_akun }}
                                                    </option>
                                                @endforeach --}}
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Notes</label>
                                                <textarea name="notes_detail" id="notes_detail" class="form-control" rows="3" placeholder="Notes ..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-xs-12">
                                            <button type="button" id="btn-add-detail"
                                                class="btn btn-flat btn-primary pull-right"><span
                                                    class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span> Tambah
                                                Detail</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="table_detail" class="table table-bordered table-striped"
                                        style="width:100%">
                                        <thead width="100%">
                                            <tr>
                                                <th class="text-center" width="10%">No Akun</th>
                                                <th class="text-center" width="10%">Nama Akun</th>
                                                <th class="text-center" width="40%">Catatan</th>
                                                <th class="text-center" width="10%">Debet</th>
                                                <th class="text-center" width="10%">Kredit</th>
                                                <th class="text-center" width="20%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Total Debet</label>
                                    <input type="text" name="total_debet" id="total_debet" class="form-control"
                                        readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>Total Kredit</label>
                                    <input type="text" name="total_kredit" id="total_kredit" class="form-control"
                                        readonly>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-xs-12">
                                    <button type="submit" id="btn-save"
                                        class="btn btn-flat btn-primary pull-right mb-1"><span
                                            class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span> Simpan
                                        Data</button>
                                    <button class="btn btn-flat btn-success mr-1 mb-1 pull-right">Generate</button>
                                    <button type="submit" id="btn-submit-detail" class="btn btn-flat btn-primary pull-right"><span class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span> Tambah Detail</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('addedScripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    {{-- Swal alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <!-- DataTables -->
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('assets/bower_components/fastclick/lib/fastclick.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let save_route = "{{ route('transaction-general-ledger-store') }}"
        let data_route = "{{ route('transaction-general-ledger') }}"
        let coa_by_cabang_route = "{{ route('master-coa-get-by-cabang', ':id') }}"
        let slip_by_cabang_route = "{{ route('master-slip-get-by-cabang', ':id') }}"
        let coa_data_route = "{{ route('master-coa-get-data', ':id') }}"

        var validateLedger = {
            submit: {
                settings: {
                    form: '#form_ledger',
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
                        console.log(formData)
                        console.log('hello')
                        // $('#form_ledger').submit();
                        // save_data()
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


        // var validateDetail = {
        //     submit: {
        //         settings: {
        //             form: '#form_detail',
        //             inputContainer: '.form-group',
        //             // errorListContainer: 'help-block',
        //             errorListClass: 'form-control-error',
        //             errorClass: 'has-error',
        //             allErrors: true,
        //             scrollToError: {
        //                 offset: -100,
        //                 duration: 500
        //             }
        //         },
        //         callback: {
        //             onSubmit: function(node, formData) {
        //                 console.log(formData)
        //                 console.log(getCurrentCoa($('#id_akun').val()))
        //                 // save_data()
        //             }
        //         }
        //     },
        //     dynamic: {
        //         settings: {
        //             trigger: 'keyup',
        //             delay: 1000
        //         },
        //     }
        // }
        var validateDetail = {
            submit: {
                settings: {
                    form: '#form_detail',
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
                        console.log('hello detail')
                        submit_detail()
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
        var details = []
        var guid = 0

        $(function() {
            $.validate(validateLedger)
            $.validate(validateDetail)

            $('.select2').select2({
                width: '100%'
            })

            $("#btn-save").on("click", function() {
                $("#hidden-btn").click()
            })

            $('#table_detail').DataTable()

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

            // On change jenis jurnal
            $("#jenis").on("change", function() {
                let jenis = $(this).val()
                if (jenis == "PG" || jenis == "HG") {
                    $(".comp-giro").attr("disabled", false)
                }
                else {
                    $(".comp-giro").attr("disabled", true).val("")
                }
            })

            // On change debet or kredit
            $("#debet").on("change", function() {
                $("#kredit").val(0)
            })
            $("#kredit").on("change", function() {
                $("#debet").val(0)
            })

            // Remove detail
            $("#table_detail").on('click', '.remove-detail', function() {
                let guid = $(this).data('guid')
                details = details.filter(function(item){ return item['guid'] != guid })
                populate_detail(details)
            })
        })

        function save_data() {
        // $('#form_ledger').submit(function(e){
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
            })
        // });
        }

        function getCoa(id_cabang) {
            let current_coa_route = coa_by_cabang_route.replace(':id', id_cabang);

            $.getJSON(current_coa_route, function(data) {
                if (data.result) {
                    $('#id_akun').html('');

                    let data_akun = data.data;
                    let option_akun = '';

                    option_akun += `<option value="">Pilih Akun</option>`;
                    data_akun.forEach(akun => {
                        option_akun +=
                            `<option value="${akun.id_akun}">${akun.kode_akun} - ${akun.nama_akun}</option>`;
                    });

                    $('#id_akun').append(option_akun);
                }
            })
        }

        function getSlip(id_cabang) {
            let current_slip_route = slip_by_cabang_route.replace(':id', id_cabang);

            $.getJSON(current_slip_route, function(data) {
                if (data.result) {
                    $('#slip').html('');

                    let data_slip = data.data;
                    let option_slip = '';

                    option_slip += `<option value="">Pilih Slip</option>`;
                    data_slip.forEach(slip => {
                        option_slip +=
                            `<option value="${slip.id_slip}">${slip.kode_slip} - ${slip.nama_slip}</option>`;
                    });

                    $('#slip').append(option_slip);
                }
            })
        }

        function getCurrentCoa(id) {
            let current_coa_data_route = coa_data_route.replace(':id', id);
            let data_akun = null;

            $.ajax({
                url: current_coa_data_route,
                async: false,
                success: function(data) {
                    if (data.result) {
                        data_akun = data.data;
                    }
                }
            });

            return data_akun;
        }

        function submit_detail() {
            console.log("submit detail clicked"+ guid)
            let akun = $("#akun_detail").val()
            let notes = $("#notes_detail").val()
            let debet = $("#debet").val()
            let kredit = $("#kredit").val()

            details.push ({
                guid: guid,
                akun: akun,
                notes: notes,
                debet: debet,
                kredit: kredit
            })
            guid++
            detail_clear()
            populate_detail(details)
        }

        function detail_clear() {
            $("#akun_detail").val("").trigger("change.select2").focus()
            $("#notes_detail").val("")
            $("#debet").val("")
            $("#kredit").val("")
        }

        function populate_detail(details) {
            detail_list = $('#table_detail').DataTable({
                data: details,
                columns: [
                    {
                        data: 'akun',
                        name: 'akun'
                    },
                    {
                        data: 'akun',
                        name: 'akun'
                    },
                    {
                        data: 'notes',
                        name: 'notes'
                    },
                    {
                        data: 'debet',
                        name: 'debet'
                    },
                    {
                        data: 'kredit',
                        name: 'kredit'
                    },
                    {
                        data: 'guid',
                        name: 'guid',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            let btn = '<button type="button" class="btn btn-sm btn-danger remove-detail" data-guid="'+data+'"><i class="fa fa-trash"></i></button>'
                            return btn
                        }
                    },
                ],
                paging: false,
                bDestroy: true,
                order: [],
            })
            calculate_detail(details)
        }

        function calculate_detail(details) {
            let total_debet = parseInt(0)
            let total_kredit = parseInt(0)
            details.forEach(detail => {
                total_debet = parseInt(total_debet) + parseInt(detail.debet)
                total_kredit = parseInt(total_kredit) + parseInt(detail.kredit)
            })
            $("#total_debet").val(parseInt(total_debet))
            $("#total_kredit").val(parseInt(total_kredit))
        }
    </script>
@endsection
