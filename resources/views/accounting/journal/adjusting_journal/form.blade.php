@extends('layouts.main')

@section('addedStyles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>

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
        Transaksi Jurnal Penyesuaian
        <small>| Tambah</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('transaction-adjustment-ledger') }}">Transaksi Jurnal Penyesuaian</a></li>
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
                                <h3 class="box-title">{{ isset($data_jurnal_umum) ? 'Ubah' : 'Tambah' }} Jurnal Penyesuaian</h3>
                                <a href="{{ route('transaction-adjustment-ledger') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span>
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
                                        <select name="cabang_input" id="cabang_input" class="form-control select2" style="width: 100%;">
                                            @foreach ($data_cabang as $cabang)
                                            <option value="{{ $cabang->id_cabang }}" {{ isset($data_jurnal_umum->id_cabang) ? ($data_jurnal_umum->id_cabang == $cabang->id_cabang ? 'selected' : '') : '' }}>
                                                {{ $cabang->kode_cabang . ' - ' . $cabang->nama_cabang }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Jurnal</label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" placeholder="Masukkan tanggal jurnal umum" value="{{ date('Y-m-d') }}" data-validation="[NOTEMPTY]" data-validation-message="Tanggal Jurnal tidak boleh kosong">
                                    </div>
                                    {{-- <div class="form-group">
                                        <label>Jenis Jurnal</label>
                                        <input type="text" name="jenis" id="jenis" class="form-control" value="ME" data-validation="[NOTEMPTY]" data-validation-message="Jenis Jurnal tidak boleh kosong" readonly>
                                    </div> --}}
                                    {{-- <div class="form-group">
                                        <label>Slip</label>
                                        <select name="slip" id="slip" class="form-control select2" data-validation="[NOTEMPTY]" data-validation-message="Slip tidak boleh kosong">
                                            <option value="">Pilih Slip</option>
                                            @foreach ($data_slip as $slip)
                                                    <option value="{{ $slip->kode_slip }}">{{ $slip->kode_slip.' - '.$slip->nama_slip }}</option>
                                            @endforeach
                                        </select>
                                    </div> --}}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Notes ..."></textarea>
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
                                        <select name="akun_detail" class="form-control select2" id="akun_detail" data-error="Wajib isi" data-validation="[NOTEMPTY]" data-validation-message="Akun tidak boleh kosong" required>
                                            <option value="">Pilih Akun</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes_detail" id="notes_detail" class="form-control" rows="3" placeholder="Notes ..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Debet</label>
                                        <input type="text" name="debet" id="debet" class="form-control" data-validation="[NOTEMPTY]" data-validation-message="Debet tidak boleh kosong" value="0" onblur="this.value=formatCurr(this.value)">
                                    </div>
                                    <div class="form-group">
                                        <label>Kredit</label>
                                        <input type="text" name="kredit" id="kredit" class="form-control" data-validation="[NOTEMPTY]" data-validation-message="Kredit tidak boleh kosong" value="0" onblur="this.value=formatCurr(this.value)">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-xs-12">
                                    <input type="hidden" id="edit_id" name="edit_id">
                                    <button type="submit" id="btn-submit-detail" class="btn btn-flat btn-primary pull-right"><span class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span> Tambah Detail</button>
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
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table id="table_detail" class="table table-bordered table-striped" style="width:100%">
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
                                <input type="text" name="total_debet" id="total_debet" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Total Kredit</label>
                                <input type="text" name="total_kredit" id="total_kredit" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-xs-12">
                                <button type="submit" id="btn-save" class="btn btn-flat btn-primary pull-right mb-1"><span class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span> Simpan
                                    Data</button>
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
    let save_route = "{{ route('transaction-adjustment-ledger-store') }}"
    let data_route = "{{ route('transaction-adjustment-ledger') }}"
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
                    let total_debet = parseFloat(0)
                    let total_kredit = parseFloat(0)
                    details.forEach(detail => {
                        total_debet = parseFloat(total_debet) + parseFloat(detail.debet)
                        total_kredit = parseFloat(total_kredit) + parseFloat(detail.kredit)
                    })
                    if (total_debet == total_kredit) {
                        save_data()
                    } 
                    else {
                        Swal.fire("Sorry, Can't save data. ", "Jumlah total debet harus sama dengan dari total kredit", 'error')
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
    var guid = 1

    $(function() {
        $.validate(validateLedger)
        $.validate(validateDetail)

        getCoa()
        getSlip()

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
            details = details.filter(function(item) {
                return item['guid'] != guid
            })
            populate_detail(details)
        })

        // Edit detail
        $("#table_detail").on("click", ".edit-detail", function() {
            let guid = $(this).data('guid')
            detail = details.filter(function(item) {
                return item['guid'] == guid
            })
            // Set data on form
            $("#akun_detail").val(detail[0]["akun"]).trigger("change.select2")
            $("#notes_detail").val(detail[0]["notes"])
            $("#debet").val(detail[0]["debet"])
            $("#kredit").val(detail[0]["kredit"])
            $("#edit_id").val(detail[0]["guid"])
        })
    })

    function save_data() {
        let header = []
        header.push({
            cabang: $("#cabang_input").val(),
            tanggal: $("#tanggal").val(),
            jenis: $("#jenis").val(),
            slip: $("#slip").val(),
            nomor_giro: $("#nomor_giro").val(),
            tanggal_giro: $("#tanggal_giro").val(),
            tanggal_jt_giro: $("#tanggal_jt_giro").val(),
            notes: $("#notes").val(),
        })
        $.ajax({
            type: "POST",
            url: save_route,
            data: {
                "_token": "{{ csrf_token() }}",
                "header": header,
                "detail": details
            },
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
    }

    function getCoa() {
        let id_cabang = $("#cabang_input").val()
        let current_coa_route = coa_by_cabang_route.replace(':id', id_cabang);

        $.getJSON(current_coa_route, function(data) {
            if (data.result) {
                $('#akun_detail').html('');

                let data_akun = data.data;
                let option_akun = '';

                option_akun += `<option value="">Pilih Akun</option>`;
                data_akun.forEach(akun => {
                    option_akun +=
                        `<option value="${akun.id_akun}" data-nama="${akun.nama_akun}" data-kode="${akun.kode_akun}">${akun.kode_akun} - ${akun.nama_akun}</option>`;
                });

                $('#akun_detail').append(option_akun);
            }
        })
    }

    function getSlip() {
        let id_cabang = $("#cabang_input").val()
        let current_slip_route = slip_by_cabang_route.replace(':id', id_cabang);

        $.getJSON(current_slip_route, function(data) {
            if (data.result) {
                $('#slip').html('');

                let data_slip = data.data;
                let option_slip = '';

                option_slip += `<option value="">Pilih Slip</option>`;
                data_slip.forEach(slip => {
                    option_slip +=
                        `<option value="${slip.id_slip}" data-nama="${slip.nama_slip}" data-akun="${slip.id_akun}" data-namaakun="${slip.nama_akun}">${slip.kode_slip} - ${slip.nama_slip}</option>`;
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
        console.log("submit detail clicked" + guid)
        let detailguid = $("#edit_id").val()
        let akun = $("#akun_detail").val()
        let nama_akun = $("#akun_detail").find(":selected").data("nama")
        let kode_akun = $("#akun_detail").find(":selected").data("kode")
        let notes = $("#notes_detail").val()
        let debet = $("#debet").val()
        let kredit = $("#kredit").val()

        if (detailguid != "") {
            details = details.filter(function(item) {
                return item['guid'] != detailguid
            })
        }
        details.push({
            guid: (detailguid != "")?detailguid:guid,
            akun: akun,
            nama_akun: nama_akun,
            kode_akun: kode_akun,
            notes: notes,
            debet: debet.replace(/,/g, ''),
            kredit: kredit.replace(/,/g, '')
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
        $("#edit_id").val("")
    }

    function populate_detail(details) {
        detail_list = $('#table_detail').DataTable({
            data: details,
            columns: [{
                    data: 'kode_akun',
                    name: 'kode_akun'
                },
                {
                    data: 'nama_akun',
                    name: 'nama_akun'
                },
                {
                    data: 'notes',
                    name: 'notes'
                },
                {
                    className: 'text-right',
                    data: 'debet',
                    name: 'debet',
                    render: function(data, type, row) {
                        return formatCurr(data)
                    }
                },
                {
                    className: 'text-right',
                    data: 'kredit',
                    name: 'kredit',
                    render: function(data, type, row) {
                        return formatCurr(data)
                    }
                },
                {
                    className: 'text-center',
                    data: 'guid',
                    name: 'guid',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let btn = '<button type="button" class="btn btn-sm btn-danger remove-detail mr-1" data-guid="' + data + '"><i class="fa fa-trash"></i></button><button type="button" class="btn btn-sm btn-warning edit-detail" data-guid="' + data + '"><i class="fa fa-edit"></i></button>'
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
        let total_debet = parseFloat(0)
        let total_kredit = parseFloat(0)
        details.forEach(detail => {
            total_debet = parseFloat(total_debet) + parseFloat(detail.debet)
            total_kredit = parseFloat(total_kredit) + parseFloat(detail.kredit)
        })
        $("#total_debet").val(formatCurr(total_debet))
        $("#total_kredit").val(formatCurr(total_kredit))
    }

    function formatCurr(num) {
        num = String(num);
        num = num.replace(/[^0-9.]/g, '');
        return numeral(num).format('0,0.00');
    }
</script>
@endsection