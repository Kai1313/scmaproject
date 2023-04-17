@extends('layouts.main')

@section('addedStyles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/jquery-datatables-checkboxes-1.2.12/css/dataTables.checkboxes.css') }}">
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
                                <button id="btn-transaction" type="button" class="btn btn-flat btn-success mr-1 mb-1 pull-right">Transaksi</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('modal-section')
    <div class="modal fade" id="modal-transaction">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">List Transaksi</h4>
                </div>
                <div class="modal-body">
                    <form id="form-transaction" action="" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Transaksi</label>
                                    <select name="transaction_type" id="transaction_type" class="form-control select2">
                                        <option value="">Pilih Jenis Transaksi</option>
                                        <option value="penjualan">Penjualan</option>
                                        <option value="pembelian">Pembelian</option>
                                        <option value="retur_penjualan">Retur Penjualan</option>
                                        <option value="retur_pembelian">Retur Pembelian</option>
                                        <option value="piutang_giro">Piutang Giro</option>
                                        <option value="hutang_giro">Hutang Giro</option>
                                        <option value="piutang_giro_tolak">Piutang Giro Tolak</option>
                                        <option value="hutang_giro_tolak">Hutang Giro Tolak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group transaction-filter" id="customer_transaction_select">
                                    <label>Customer</label>
                                    <select name="customer_transaction" id="customer_transaction" class="form-control select2">
                                        <option value="">Pilih Customer</option>
                                        @foreach ($data_pelanggan as $pelanggan)
                                            <option value="{{ $pelanggan->id_pelanggan }}">{{ $pelanggan->nama_pelanggan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group transaction-filter" id="supplier_transaction_select">
                                    <label>Supplier</label>
                                    <select name="supplier_transaction" id="supplier_transaction" class="form-control select2">
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($data_pemasok as $pemasok)
                                            <option value="{{ $pemasok->id_pemasok }}">{{ $pemasok->nama_pemasok }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row box-transaction" id="box-jual">
                            <div class="col-md-12">
                                <table id="table_jual" class="table table-bordered table-striped table-transaction" style="width:100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Nomor Jual</th>
                                            <th class="text-center">Sales Order</th>
                                            <th class="text-center">Customer</th>
                                            <th class="text-center">Note</th>
                                            <th class="text-center">DPP</th>
                                            <th class="text-center">PPn</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Terbayar</th>
                                            <th class="text-center">Sisa</th>
                                            <th class="text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row box-transaction" id="box-retur-jual">
                            <div class="col-md-12">
                                <table id="table_retur_jual" class="table table-bordered table-striped table-transaction" style="width:100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Nomor Jual</th>
                                            <th class="text-center">Nomor Invoice</th>
                                            <th class="text-center">Customer</th>
                                            <th class="text-center">Note</th>
                                            <th class="text-center">DPP</th>
                                            <th class="text-center">PPn</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Terbayar</th>
                                            <th class="text-center">Sisa</th>
                                            <th class="text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row box-transaction" id="box-beli">
                            <div class="col-md-12">
                                <table id="table_beli" class="table table-bordered table-striped table-transaction" style="width:100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Nomor Beli</th>
                                            <th class="text-center">Nomor PO</th>
                                            <th class="text-center">Supplier</th>
                                            <th class="text-center">Note</th>
                                            <th class="text-center">DPP</th>
                                            <th class="text-center">PPn</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Terbayar</th>
                                            <th class="text-center">Sisa</th>
                                            <th class="text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row box-transaction" id="box-retur-beli">
                            <div class="col-md-12">
                                <table id="table_retur_beli" class="table table-bordered table-striped table-transaction" style="width:100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Nomor Beli</th>
                                            <th class="text-center">Nomor Retur Pembelian</th>
                                            <th class="text-center">Supplier</th>
                                            <th class="text-center">Note</th>
                                            <th class="text-center">DPP</th>
                                            <th class="text-center">PPn</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Terbayar</th>
                                            <th class="text-center">Sisa</th>
                                            <th class="text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row box-transaction" id="box-piutang-giro">
                            <div class="col-md-12">
                                <table id="table_piutang_giro" class="table table-bordered table-striped table-transaction" style="width:100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Tanggal JT</th>
                                            <th class="text-center">Nomor Giro</th>
                                            <th class="text-center">Note</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row box-transaction" id="box-piutang-giro-tolak">
                            <div class="col-md-12">
                                <table id="table_piutang_giro_tolak" class="table table-bordered table-striped table-transaction" style="width:100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Tanggal JT</th>
                                            <th class="text-center">Nomor Giro Tolak</th>
                                            <th class="text-center">Note</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row box-transaction" id="box-hutang-giro">
                            <div class="col-md-12">
                                <table id="table_hutang_giro" class="table table-bordered table-striped table-transaction" style="width:100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Tanggal JT</th>
                                            <th class="text-center">Nomor Giro</th>
                                            <th class="text-center">Note</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="row box-transaction" id="box-hutang-giro-tolak">
                            <div class="col-md-12">
                                <table id="table_hutang_giro_tolak" class="table table-bordered table-striped table-transaction" style="width:100%">
                                    <thead width="100%">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Tanggal JT</th>
                                            <th class="text-center">Nomor Giro Tolak</th>
                                            <th class="text-center">Note</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                    <button type="button" id="btn-add-transaction" class="btn btn-primary">Tambah Transaksi</button>
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
<!-- DataTables -->
<script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-datatables-checkboxes-1.2.12/js/dataTables.checkboxes.min.js') }}"></script>
<!-- SlimScroll -->
<script src="{{ asset('assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ asset('assets/bower_components/fastclick/lib/fastclick.js') }}"></script>
<!-- Numeral -->
<script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
@endsection

@section('externalScripts')
<script>
    let save_route = "{{ route('transaction-adjustment-ledger-store') }}"
    let data_route = "{{ route('transaction-adjustment-ledger') }}"
    let coa_by_cabang_route = "{{ route('master-coa-get-by-cabang', ':id') }}"
    let slip_by_cabang_route = "{{ route('master-slip-get-by-cabang', ':id') }}"
    let coa_data_route = "{{ route('master-coa-get-data', ':id') }}"
    let setting_data_route = "{{ route('master-setting-get-pelunasan', ':id') }}"
    let giro_reject_data_route = "{{ route('transaction-adjustment-ledger-get-giro-reject', ':id') }}"
    let piutang_dagang 
    let hutang_dagang

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
        getSetting($("#cabang_input").val())

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

        // On change cabang
        $("#cabang_input").on("change", function() {
            getSetting($(this).val())
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
            let notes = detail[0]["notes"].replace('<br/>', '\n')

            // Set data on form
            $("#akun_detail").val(detail[0]["akun"]).trigger("change.select2")
            $("#notes_detail").val(notes)
            $("#debet").val(formatCurr(detail[0]["debet"]))
            $("#kredit").val(formatCurr(detail[0]["kredit"]))
            $("#edit_id").val(detail[0]["guid"])
        })

        // Open Transaction Modal
        $("#btn-transaction").on("click", function() {
            $("#modal-transaction").modal("show")
            $(".box-transaction").hide()
            $(".transaction-filter").hide()
            $("#transaction_type").val("").trigger("change.select2")
            $("#customer_transaction").val("").trigger("change.select2")
            $("#supplier_transaction").val("").trigger("change.select2")
        })

        // Open Transaction Box from selected transaction type
        $("#transaction_type").on("change", function() {
            let type = $(this).val()
            switch (type) {
                case "penjualan":
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    populate_transaction(type)
                    $("#box-jual").show()
                    $("#customer_transaction_select").show()
                    break;
                case "retur_penjualan":
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    populate_transaction(type)
                    $("#box-retur-jual").show()
                    $("#customer_transaction_select").show()
                    break;
                case "pembelian":
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    populate_transaction(type)
                    $("#box-beli").show()
                    $("#supplier_transaction_select").show()
                    break;
                case "retur_pembelian":
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    populate_transaction(type)
                    $("#box-retur-beli").show()
                    $("#supplier_transaction_select").show()
                    break;
                case "piutang_giro":
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    populate_transaction(type)
                    $("#box-piutang-giro").show()
                    break;
                case "hutang_giro":
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    populate_transaction(type)
                    $("#box-hutang-giro").show()
                    break;
                case "piutang_giro_tolak":
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    populate_transaction(type)
                    $("#box-piutang-giro-tolak").show()
                    break;
                case "hutang_giro_tolak":
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    populate_transaction(type)
                    $("#box-hutang-giro-tolak").show()
                    break;

                default:
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    break;
            }
        })

        // On change customer transaction
        $("#customer_transaction").on("change", function() {
            let trx_type = $("#transaction_type").val()
            switch (trx_type) {
                case "penjualan":
                    populate_transaction(trx_type)
                    break;
                case "retur_penjualan":
                    populate_transaction(trx_type)
                    break;
                case "pembelian":
                    populate_transaction(trx_type)
                    break;
                case "retur_pembelian":
                    populate_transaction(trx_type)
                    break;
                case "piutang_giro":
                    populate_transaction(trx_type)
                    break;
                case "hutang_giro":
                    populate_transaction(trx_type)
                    break;
                case "piutang_giro_tolak":
                    populate_transaction(trx_type)
                    break;
                case "hutang_giro_tolak":
                    populate_transaction(trx_type)
                    break;
            
                default:
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    break;
            }
        })

        // On change supplier transaction
        $("#supplier_transaction").on("change", function() {
            let trx_type = $("#transaction_type").val()
            switch (trx_type) {
                case "penjualan":
                    populate_transaction(trx_type)
                    break;
                case "retur_penjualan":
                    populate_transaction(trx_type)
                    break;
                case "pembelian":
                    populate_transaction(trx_type)
                    break;
                case "retur_pembelian":
                    populate_transaction(trx_type)
                    break;
                case "piutang_giro":
                    populate_transaction(trx_type)
                    break;
                case "hutang_giro":
                    populate_transaction(trx_type)
                    break;
                case "piutang_giro_tolak":
                    populate_transaction(trx_type)
                    break;
                case "hutang_giro_tolak":
                    populate_transaction(trx_type)
                    break;
            
                default:
                    $(".box-transaction").hide()
                    $(".transaction-filter").hide()
                    break;
            }
        })

        // Transaction table add transaction
        $("#btn-add-transaction").on("click", function() {
            let trx_type = $("#transaction_type").val()
            let transactions = []
            switch (trx_type) {
                case "penjualan":
                    let table_jual = $('#table_jual')
                    $('.dt-checkboxes:checked', table_jual).each(function() {
                        // Init data from row
                        let trx_id = $(this).closest('tr').find('.transaction-id').val()
                        let no_jual = $(this).closest('tr').find('td:eq(2)').text()
                        let pelanggan = $(this).closest('tr').find('td:eq(4)').text()
                        let kredit = $(this).closest('tr').find('.transaction-bayar').val()

                        // Remove data from details
                        details = details.filter(function(item) {
                            return item['guid'] != "trx-" + trx_id
                        })
                        details.push({
                            guid: "trx-" + trx_id,
                            akun: piutang_dagang.id_akun,
                            nama_akun: piutang_dagang.nama_akun,
                            kode_akun: piutang_dagang.kode_akun,
                            notes: 'Jurnal Otomatis Pelunasan - ' + no_jual + ' - ' + pelanggan,
                            trx: no_jual,
                            debet: 0,
                            kredit: formatNumberAsLocalFloat(kredit)
                        })
                    }).get()
                    populate_detail(details)
                    break;
                case "retur_penjualan":
                    let table_retur_jual = $('#table_retur_jual')
                    $('.dt-checkboxes:checked', table_retur_jual).each(function() {
                        // Init data from row
                        let trx_id = $(this).closest('tr').find('.transaction-id').val()
                        let no_jual = $(this).closest('tr').find('td:eq(2)').text()
                        let pelanggan = $(this).closest('tr').find('td:eq(4)').text()
                        let debet = $(this).closest('tr').find('.transaction-bayar').val()

                        // Remove data from details
                        details = details.filter(function(item) {
                            return item['guid'] != "trx-" + trx_id
                        })
                        details.push({
                            guid: "trx-" + trx_id,
                            akun: piutang_dagang.id_akun,
                            nama_akun: piutang_dagang.nama_akun,
                            kode_akun: piutang_dagang.kode_akun,
                            notes: 'Jurnal Otomatis Pelunasan - ' + no_jual + ' - ' + pelanggan,
                            trx: no_jual,
                            debet: formatNumberAsLocalFloat(debet),
                            kredit: 0
                        })
                    }).get()
                    populate_detail(details)
                    break;
                case "pembelian":
                    let table_beli = $('#table_beli')
                    $('.dt-checkboxes:checked', table_beli).each(function() {
                        // Init data from row
                        let trx_id = $(this).closest('tr').find('.transaction-id').val()
                        let no_beli = $(this).closest('tr').find('td:eq(2)').text()
                        let pemasok = $(this).closest('tr').find('td:eq(4)').text()
                        let debet = $(this).closest('tr').find('.transaction-bayar').val()

                        // Remove data from details
                        details = details.filter(function(item) {
                            return item['guid'] != "trx-" + trx_id
                        })
                        details.push({
                            guid: "trx-" + trx_id,
                            akun: hutang_dagang.id_akun,
                            nama_akun: hutang_dagang.nama_akun,
                            kode_akun: hutang_dagang.kode_akun,
                            notes: 'Jurnal Otomatis Pelunasan - ' + no_beli + ' - ' + pemasok,
                            trx: no_beli,
                            debet: formatNumberAsLocalFloat(debet),
                            kredit: 0
                        })
                    }).get()
                    populate_detail(details)
                    break;
                case "retur_pembelian":
                    let table_retur_beli = $('#table_retur_beli')
                    $('.dt-checkboxes:checked', table_retur_beli).each(function() {
                        // Init data from row
                        let trx_id = $(this).closest('tr').find('.transaction-id').val()
                        let no_beli = $(this).closest('tr').find('td:eq(2)').text()
                        let pemasok = $(this).closest('tr').find('td:eq(4)').text()
                        let kredit = $(this).closest('tr').find('.transaction-bayar').val()

                        // Remove data from details
                        details = details.filter(function(item) {
                            return item['guid'] != "trx-" + trx_id
                        })
                        details.push({
                            guid: "trx-" + trx_id,
                            akun: hutang_dagang.id_akun,
                            nama_akun: hutang_dagang.nama_akun,
                            kode_akun: hutang_dagang.kode_akun,
                            notes: 'Jurnal Otomatis Pelunasan - ' + no_beli + ' - ' + pemasok,
                            trx: no_beli,
                            debet: 0,
                            kredit: formatNumberAsLocalFloat(kredit)
                        })
                    }).get()
                    populate_detail(details)
                    break;
                case "piutang_giro":
                    let table_piutang_giro = $('#table_piutang_giro')
                    $('.dt-checkboxes:checked', table_piutang_giro).each(function() {
                        // Init data from row
                        let trx_id = $(this).closest('tr').find('.transaction-id').val()
                        let no_giro = $(this).closest('tr').find('td:eq(3)').text()
                        let kredit = $(this).closest('tr').find('.transaction-bayar').val()
                        let id_akun = $(this).closest('tr').find('.akun-id').val()
                        let nama_akun = $(this).closest('tr').find('.akun-nama').val()
                        let kode_akun = $(this).closest('tr').find('.akun-kode').val()
                        let no_trx = $(this).closest('tr').find('.transaction-no').val()

                        // Remove data from details
                        details = details.filter(function(item) {
                            return item['guid'] != "trx-" + trx_id
                        })
                        details.push({
                            guid: "trx-" + trx_id,
                            akun: id_akun,
                            nama_akun: nama_akun,
                            kode_akun: kode_akun,
                            notes: 'Jurnal Otomatis Pelunasan - ' + no_giro,
                            trx: no_trx,
                            debet: 0,
                            kredit: formatNumberAsLocalFloat(kredit)
                        })
                    }).get()
                    populate_detail(details)
                    break;
                case "hutang_giro":
                    let table_hutang_giro = $('#table_hutang_giro')
                    $('.dt-checkboxes:checked', table_hutang_giro).each(function() {
                        // Init data from row
                        let trx_id = $(this).closest('tr').find('.transaction-id').val()
                        let no_giro = $(this).closest('tr').find('td:eq(3)').text()
                        let debet = $(this).closest('tr').find('.transaction-bayar').val()
                        let id_akun = $(this).closest('tr').find('.akun-id').val()
                        let nama_akun = $(this).closest('tr').find('.akun-nama').val()
                        let kode_akun = $(this).closest('tr').find('.akun-kode').val()
                        let no_trx = $(this).closest('tr').find('.transaction-no').val()

                        // Remove data from details
                        details = details.filter(function(item) {
                            return item['guid'] != "trx-" + trx_id
                        })
                        details.push({
                            guid: "trx-" + trx_id,
                            akun: id_akun,
                            nama_akun: nama_akun,
                            kode_akun: kode_akun,
                            notes: 'Jurnal Otomatis Pelunasan - ' + no_giro,
                            trx: no_trx,
                            debet: formatNumberAsLocalFloat(debet),
                            kredit: 0
                        })
                    }).get()
                    populate_detail(details)
                    break;
                case "piutang_giro_tolak":
                    let table_piutang_giro_tolak = $('#table_piutang_giro_tolak')
                    let countChecked = $('.dt-checkboxes:checked', table_piutang_giro_tolak).length
                    if (countChecked == 1) {
                        $('.dt-checkboxes:checked', table_piutang_giro_tolak).each(function() {
                            // Init data from row
                            let trx_id = $(this).closest('tr').find('.transaction-id').val()
                            let no_giro = $(this).closest('tr').find('td:eq(3)').text()
                            let debet = $(this).closest('tr').find('.transaction-bayar').val()
                            let id_akun = $(this).closest('tr').find('.akun-id').val()
                            let nama_akun = $(this).closest('tr').find('.akun-nama').val()
                            let kode_akun = $(this).closest('tr').find('.akun-kode').val()
                            let no_trx = $(this).closest('tr').find('.transaction-no').val()
    
                            // Get Giro Reject records
                            getGiroReject(no_trx)
                        }).get()
                    }
                    else {
                        alert("Giro Tolak hanya boleh pilih 1 record")
                    }
                    populate_detail(details)
                    break;
                case "hutang_giro_tolak":
                    let table_hutang_giro_tolak = $('#table_hutang_giro_tolak')
                    let countChecked2 = $('.dt-checkboxes:checked', table_hutang_giro_tolak).length
                    if (countChecked2 == 1) {
                        $('.dt-checkboxes:checked', table_hutang_giro_tolak).each(function() {
                            // Init data from row
                            let trx_id = $(this).closest('tr').find('.transaction-id').val()
                            let no_giro = $(this).closest('tr').find('td:eq(3)').text()
                            let debet = $(this).closest('tr').find('.transaction-bayar').val()
                            let id_akun = $(this).closest('tr').find('.akun-id').val()
                            let nama_akun = $(this).closest('tr').find('.akun-nama').val()
                            let kode_akun = $(this).closest('tr').find('.akun-kode').val()
                            let no_trx = $(this).closest('tr').find('.transaction-no').val()
    
                            // Get Giro Reject records
                            getGiroReject(no_trx)
                        }).get()
                    }
                    else {
                        alert("Giro Tolak hanya boleh pilih 1 record")
                    }
                    populate_detail(details)
                    break;
            
                default:
                    alert("Nothing to add")
                    break;
            }
            $("#modal-transaction").modal("hide")
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

    function getSetting(cabang) {
        let current_setting_data_route = setting_data_route.replace(':id', cabang);
        $.ajax({
            url: current_setting_data_route,
            async: false,
            success: function(data) {
                if (data.result) {
                    piutang_dagang = data.piutang_dagang
                    hutang_dagang = data.hutang_dagang
                }
            }
        })
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
            trx: null,
            debet: formatNumberAsLocalFloat(debet),
            kredit: formatNumberAsLocalFloat(kredit)
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
        console.log(details);
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
                        let btn
                        if (data != "gen" && row["trx"] == null) {
                            btn = '<button type="button" class="btn btn-sm btn-danger remove-detail mr-1" data-guid="' + data + '"><i class="fa fa-trash"></i></button><button type="button" class="btn btn-sm btn-warning edit-detail" data-guid="' + data + '"><i class="fa fa-edit"></i></button>'
                        }
                        else {
                            btn = '<button type="button" class="btn btn-sm btn-danger remove-detail" data-guid="' + data + '"><i class="fa fa-trash"></i></button>'
                        }
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
            detail.debet = String(detail.debet);
            detail.kredit = String(detail.kredit);
            
            total_debet = parseFloat(total_debet) + parseFloat(detail.debet.replace(',', '.'))
            total_kredit = parseFloat(total_kredit) + parseFloat(detail.kredit.replace(',', '.'))
        })
        total_debet = parseFloat(total_debet).toFixed(2);
        total_kredit = parseFloat(total_kredit).toFixed(2);

        total_debet = String(total_debet);
        total_kredit = String(total_kredit);

        total_debet = total_debet.replace('.', ',');
        total_kredit = total_kredit.replace('.', ',');

        $("#total_debet").val(formatCurr(total_debet))
        $("#total_kredit").val(formatCurr(total_kredit))
    }

    function populate_transaction(type) {
        switch (type) {
            case "penjualan":
                $("#table_jual").DataTable().destroy()
                let get_penjualan_url = "{{ route('transaction-general-ledger-populate-transaction') }}"
                get_penjualan_url += '?transaction_type=' + $("#transaction_type").val() + '&customer=' + $("#customer_transaction").val()
                $('#table_jual').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": true,
                    "bDestroy": true,
                    responsive: true,
                    ajax: {
                        'url': get_penjualan_url,
                        'type': 'GET',
                        'dataType': 'JSON',
                        'error': function(xhr, textStatus, ThrownException) {
                            alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal',
                            width: '10%'
                        },
                        {
                            data: 'id_transaksi',
                            name: 'id_transaksi',
                            width: '15%'
                        },
                        {
                            data: 'ref_id',
                            name: 'ref_id',
                            width: '10%'
                        },
                        {
                            data: 'nama_pelanggan',
                            name: 'pelanggan.nama_pelanggan',
                            width: '10%'
                        },
                        {
                            data: 'catatan',
                            name: 'catatan',
                            width: '10%'
                        },
                        {
                            data: 'dpp',
                            name: 'dpp',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'ppn',
                            name: 'ppn',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'total',
                            name: 'total',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'bayar',
                            name: 'bayar',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            name: 'sisa',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            width: '10%',
                            render: function(data, type, row) {
                                return '<input type="text" class="form-control transaction-bayar" value="'+formatCurr(formatNumberAsFloatFromDB(data))+'" onblur="this.value=formatCurr(this.value)"><input type="hidden" class="form-control transaction-id" value="'+row["id"]+'">';
                            },
                            orderable: false
                        }
                    ],
                    'columnDefs': [
                        {
                           'targets': 0,
                           'checkboxes': {
                              'selectRow': true
                           }
                        }
                     ],
                     'select': {
                        'style': 'multi'
                     },
                     'order': [[1, 'asc']]
                })
                break;
            case "retur_penjualan":
                $("#table_retur_jual").DataTable().destroy()
                let get_retur_penjualan_url = "{{ route('transaction-general-ledger-populate-transaction') }}"
                get_retur_penjualan_url += '?transaction_type=' + $("#transaction_type").val() + '&customer=' + $("#customer_transaction").val()
                $('#table_retur_jual').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": true,
                    "bDestroy": true,
                    responsive: true,
                    ajax: {
                        'url': get_retur_penjualan_url,
                        'type': 'GET',
                        'dataType': 'JSON',
                        'error': function(xhr, textStatus, ThrownException) {
                            alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal',
                            width: '10%'
                        },
                        {
                            data: 'id_transaksi',
                            name: 'id_transaksi',
                            width: '15%'
                        },
                        {
                            data: 'ref_id',
                            name: 'ref_id',
                            width: '10%'
                        },
                        {
                            data: 'nama_pelanggan',
                            name: 'pelanggan.nama_pelanggan',
                            width: '10%'
                        },
                        {
                            data: 'catatan',
                            name: 'catatan',
                            width: '10%'
                        },
                        {
                            data: 'dpp',
                            name: 'dpp',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'ppn',
                            name: 'ppn',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'total',
                            name: 'total',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'bayar',
                            name: 'bayar',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            name: 'sisa',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            width: '10%',
                            render: function(data, type, row) {
                                return '<input type="text" class="form-control transaction-bayar" value="'+formatCurr(formatNumberAsFloatFromDB(data))+'" onblur="this.value=formatCurr(this.value)"><input type="hidden" class="form-control transaction-id" value="'+row["id"]+'">';
                            },
                            orderable: false
                        }
                    ],
                    'columnDefs': [
                        {
                           'targets': 0,
                           'checkboxes': {
                              'selectRow': true
                           }
                        }
                     ],
                     'select': {
                        'style': 'multi'
                     },
                     'order': [[1, 'asc']]
                })
                break;
            case "pembelian":
                $("#table_beli").DataTable().destroy()
                let get_pembelian_url = "{{ route('transaction-general-ledger-populate-transaction') }}"
                get_pembelian_url += '?transaction_type=' + $("#transaction_type").val() + '&supplier=' + $("#supplier_transaction").val()
                $('#table_beli').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": true,
                    "bDestroy": true,
                    responsive: true,
                    ajax: {
                        'url': get_pembelian_url,
                        'type': 'GET',
                        'dataType': 'JSON',
                        'error': function(xhr, textStatus, ThrownException) {
                            alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal',
                            width: '10%'
                        },
                        {
                            data: 'id_transaksi',
                            name: 'id_transaksi',
                            width: '15%'
                        },
                        {
                            data: 'ref_id',
                            name: 'ref_id',
                            width: '10%'
                        },
                        {
                            data: 'nama_pemasok',
                            name: 'pemasok.nama_pemasok',
                            width: '10%'
                        },
                        {
                            data: 'catatan',
                            name: 'catatan',
                            width: '10%'
                        },
                        {
                            data: 'dpp',
                            name: 'dpp',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'ppn',
                            name: 'ppn',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'total',
                            name: 'total',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'bayar',
                            name: 'bayar',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            name: 'sisa',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            width: '10%',
                            render: function(data, type, row) {
                                return '<input type="text" class="form-control transaction-bayar" value="'+formatCurr(formatNumberAsFloatFromDB(data))+'" onblur="this.value=formatCurr(this.value)"><input type="hidden" class="form-control transaction-id" value="'+row["id"]+'">';
                            },
                            orderable: false
                        }
                    ],
                    'columnDefs': [
                        {
                           'targets': 0,
                           'checkboxes': {
                              'selectRow': true
                           }
                        }
                     ],
                     'select': {
                        'style': 'multi'
                     },
                     'order': [[1, 'asc']]
                })
                break;
            case "retur_pembelian":
                $("#table_retur_beli").DataTable().destroy()
                let get_retur_pembelian_url = "{{ route('transaction-general-ledger-populate-transaction') }}"
                get_retur_pembelian_url += '?transaction_type=' + $("#transaction_type").val() + '&supplier=' + $("#supplier_transaction").val()
                $('#table_retur_beli').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": true,
                    "bDestroy": true,
                    responsive: true,
                    ajax: {
                        'url': get_retur_pembelian_url,
                        'type': 'GET',
                        'dataType': 'JSON',
                        'error': function(xhr, textStatus, ThrownException) {
                            alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal',
                            width: '10%'
                        },
                        {
                            data: 'id_transaksi',
                            name: 'id_transaksi',
                            width: '15%'
                        },
                        {
                            data: 'ref_id',
                            name: 'ref_id',
                            width: '10%'
                        },
                        {
                            data: 'nama_pemasok',
                            name: 'pemasok.nama_pemasok',
                            width: '10%'
                        },
                        {
                            data: 'catatan',
                            name: 'catatan',
                            width: '10%'
                        },
                        {
                            data: 'dpp',
                            name: 'dpp',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'ppn',
                            name: 'ppn',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'total',
                            name: 'total',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'bayar',
                            name: 'bayar',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            name: 'sisa',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            width: '10%',
                            render: function(data, type, row) {
                                return '<input type="text" class="form-control transaction-bayar" value="'+formatCurr(formatNumberAsFloatFromDB(data))+'" onblur="this.value=formatCurr(this.value)"><input type="hidden" class="form-control transaction-id" value="'+row["id"]+'">';
                            },
                            orderable: false
                        }
                    ],
                    'columnDefs': [
                        {
                           'targets': 0,
                           'checkboxes': {
                              'selectRow': true
                           }
                        }
                     ],
                     'select': {
                        'style': 'multi'
                     },
                     'order': [[1, 'asc']]
                })
                break;
            case "piutang_giro":
                $("#table_piutang_giro").DataTable().destroy()
                let get_piutang_giro_url = "{{ route('transaction-general-ledger-populate-transaction') }}"
                get_piutang_giro_url += '?transaction_type=' + $("#transaction_type").val() + '&supplier=' + $("#supplier_transaction").val()
                $('#table_piutang_giro').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": true,
                    "bDestroy": true,
                    responsive: true,
                    ajax: {
                        'url': get_piutang_giro_url,
                        'type': 'GET',
                        'dataType': 'JSON',
                        'error': function(xhr, textStatus, ThrownException) {
                            alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'tanggal_giro',
                            name: 'tanggal_giro',
                            width: '10%'
                        },
                        {
                            data: 'tanggal_giro_jt',
                            name: 'tanggal_giro_jt',
                            width: '15%'
                        },
                        {
                            data: 'no_giro',
                            name: 'no_giro',
                            width: '10%'
                        },
                        {
                            data: 'catatan',
                            name: 'catatan',
                            width: '10%'
                        },
                        {
                            data: 'total',
                            name: 'total',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            width: '10%',
                            render: function(data, type, row) {
                                return '<input type="text" class="form-control transaction-bayar" value="'+formatCurr(formatNumberAsFloatFromDB(data))+'" onblur="this.value=formatCurr(this.value)" readonly><input type="hidden" class="form-control transaction-id" value="'+row["id"]+'"><input type="hidden" class="form-control akun-id" value="'+row["id_akun"]+'"><input type="hidden" class="form-control akun-nama" value="'+row["nama_akun"]+'"><input type="hidden" class="form-control akun-kode" value="'+row["kode_akun"]+'"><input type="hidden" class="form-control transaction-no" value="'+row["id_transaksi"]+'">';
                            },
                            orderable: false
                        }
                    ],
                    'columnDefs': [
                        {
                           'targets': 0,
                           'checkboxes': {
                              'selectRow': true
                           }
                        }
                     ],
                     'select': {
                        'style': 'multi'
                     },
                     'order': [[1, 'asc']]
                })
                break;
            case "hutang_giro":
                $("#table_hutang_giro").DataTable().destroy()
                let get_hutang_giro_url = "{{ route('transaction-general-ledger-populate-transaction') }}"
                get_hutang_giro_url += '?transaction_type=' + $("#transaction_type").val() + '&supplier=' + $("#supplier_transaction").val()
                $('#table_hutang_giro').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": true,
                    "bDestroy": true,
                    responsive: true,
                    ajax: {
                        'url': get_hutang_giro_url,
                        'type': 'GET',
                        'dataType': 'JSON',
                        'error': function(xhr, textStatus, ThrownException) {
                            alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'tanggal_giro',
                            name: 'tanggal_giro',
                            width: '10%'
                        },
                        {
                            data: 'tanggal_giro_jt',
                            name: 'tanggal_giro_jt',
                            width: '15%'
                        },
                        {
                            data: 'no_giro',
                            name: 'no_giro',
                            width: '10%'
                        },
                        {
                            data: 'catatan',
                            name: 'catatan',
                            width: '10%'
                        },
                        {
                            data: 'total',
                            name: 'total',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            width: '10%',
                            render: function(data, type, row) {
                                return '<input type="text" class="form-control transaction-bayar" value="'+formatCurr(formatNumberAsFloatFromDB(data))+'" onblur="this.value=formatCurr(this.value)" readonly><input type="hidden" class="form-control transaction-id" value="'+row["id"]+'"><input type="hidden" class="form-control akun-id" value="'+row["id_akun"]+'"><input type="hidden" class="form-control akun-nama" value="'+row["nama_akun"]+'"><input type="hidden" class="form-control akun-kode" value="'+row["kode_akun"]+'"><input type="hidden" class="form-control transaction-no" value="'+row["id_transaksi"]+'">';
                            },
                            orderable: false
                        }
                    ],
                    'columnDefs': [
                        {
                           'targets': 0,
                           'checkboxes': {
                              'selectRow': true
                           }
                        }
                     ],
                     'select': {
                        'style': 'multi'
                     },
                     'order': [[1, 'asc']]
                })
                break;
            case "piutang_giro_tolak":
                $("#table_piutang_giro_tolak").DataTable().destroy()
                let get_piutang_giro_tolak_url = "{{ route('transaction-general-ledger-populate-transaction') }}"
                get_piutang_giro_tolak_url += '?transaction_type=' + $("#transaction_type").val() + '&supplier=' + $("#supplier_transaction").val()
                $('#table_piutang_giro_tolak').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": true,
                    "bDestroy": true,
                    responsive: true,
                    ajax: {
                        'url': get_piutang_giro_tolak_url,
                        'type': 'GET',
                        'dataType': 'JSON',
                        'error': function(xhr, textStatus, ThrownException) {
                            alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'tanggal_giro',
                            name: 'tanggal_giro',
                            width: '10%'
                        },
                        {
                            data: 'tanggal_giro_jt',
                            name: 'tanggal_giro_jt',
                            width: '15%'
                        },
                        {
                            data: 'no_giro',
                            name: 'no_giro',
                            width: '10%'
                        },
                        {
                            data: 'catatan',
                            name: 'catatan',
                            width: '10%'
                        },
                        {
                            data: 'total',
                            name: 'total',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            width: '10%',
                            render: function(data, type, row) {
                                return '<input type="text" class="form-control transaction-bayar" value="'+formatCurr(formatNumberAsFloatFromDB(data))+'" onblur="this.value=formatCurr(this.value)" readonly><input type="hidden" class="form-control transaction-id" value="'+row["id"]+'"><input type="hidden" class="form-control akun-id" value="'+row["id_akun"]+'"><input type="hidden" class="form-control akun-nama" value="'+row["nama_akun"]+'"><input type="hidden" class="form-control akun-kode" value="'+row["kode_akun"]+'"><input type="hidden" class="form-control transaction-no" value="'+row["id_transaksi"]+'">';
                            },
                            orderable: false
                        }
                    ],
                    'columnDefs': [
                        {
                           'targets': 0,
                           'checkboxes': {
                              'selectRow': true
                           }
                        }
                     ],
                     'select': {
                        'style': 'multi'
                     },
                     'order': [[1, 'asc']]
                })
                break;
            case "hutang_giro_tolak":
                $("#table_hutang_giro_tolak").DataTable().destroy()
                let get_hutang_giro_tolak_url = "{{ route('transaction-general-ledger-populate-transaction') }}"
                get_hutang_giro_tolak_url += '?transaction_type=' + $("#transaction_type").val() + '&supplier=' + $("#supplier_transaction").val()
                $('#table_hutang_giro_tolak').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": true,
                    "bDestroy": true,
                    responsive: true,
                    ajax: {
                        'url': get_hutang_giro_tolak_url,
                        'type': 'GET',
                        'dataType': 'JSON',
                        'error': function(xhr, textStatus, ThrownException) {
                            alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'tanggal_giro',
                            name: 'tanggal_giro',
                            width: '10%'
                        },
                        {
                            data: 'tanggal_giro_jt',
                            name: 'tanggal_giro_jt',
                            width: '15%'
                        },
                        {
                            data: 'no_giro',
                            name: 'no_giro',
                            width: '10%'
                        },
                        {
                            data: 'catatan',
                            name: 'catatan',
                            width: '10%'
                        },
                        {
                            data: 'total',
                            name: 'total',
                            width: '10%',
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data));
                            },
                        },
                        {
                            data: 'sisa',
                            width: '10%',
                            render: function(data, type, row) {
                                return '<input type="text" class="form-control transaction-bayar" value="'+formatCurr(formatNumberAsFloatFromDB(data))+'" onblur="this.value=formatCurr(this.value)" readonly><input type="hidden" class="form-control transaction-id" value="'+row["id"]+'"><input type="hidden" class="form-control akun-id" value="'+row["id_akun"]+'"><input type="hidden" class="form-control akun-nama" value="'+row["nama_akun"]+'"><input type="hidden" class="form-control akun-kode" value="'+row["kode_akun"]+'"><input type="hidden" class="form-control transaction-no" value="'+row["id_transaksi"]+'">';
                            },
                            orderable: false
                        }
                    ],
                    'columnDefs': [
                        {
                           'targets': 0,
                           'checkboxes': {
                              'selectRow': true
                           }
                        }
                     ],
                     'select': {
                        'style': 'multi'
                     },
                     'order': [[1, 'asc']]
                })
                break;
        
            default:
                $(".table-transaction").DataTable().destroy()
                break;
        }
    }

    function formatCurr(num) {
        num = String(num);
        
        num = num.split('.').join("");;
        num = num.replace(/,/g, '.');
        num = num.toString().replace(/\,/gi, "");
        num += '';
        x = num.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? ',' + x[1] : ',00';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + '.' + '$2');
        }
        return x1 + x2;
    }

    function formatNumberAsFloat(num) {
        num = String(num);
        num = num.replace(',', '.');
        
        return num;
    }

    function formatNumberAsLocalFloat(num) {
        num = String(num);
        num = num.split('.').join("");
        
        return num;
    }

    function formatNumberAsFloatFromDB(num) {
        num = String(num);
        num = parseFloat(num).toFixed(2);
        num = num.replace('.', ',');
        
        return num;
    }

    function getGiroReject(ids) {
        let giro_reject_data_route_url = giro_reject_data_route.replace(':id', ids);
        $.ajax({
            url: giro_reject_data_route_url,
            async: false,
            success: function(data) {
                if (data.result) {
                    details = data.details
                }
            }
        })
    }
</script>
@endsection