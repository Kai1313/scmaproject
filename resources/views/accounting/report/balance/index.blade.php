@extends('layouts.main')

@section('addedStyles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/jquery-datatables-checkboxes-1.2.12/css/dataTables.checkboxes.css') }}">
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- bootstrap datepicker -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
<!-- Treetable -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/jquery-treetable/css/jquery.treetable.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/jquery-treetable/css/jquery.treetable.theme.default.css') }}">

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

    #table_balance_recap th,
    #table_balance_recap th{
        text-align: center !important;
        font-size: 18px !important;
        border-color: white !important;
        padding: 0.6rem 0.4rem;
        font-weight: 600;
    }

    #table_balance_recap td,
    #table_balance_recap td{
        padding: 0.5rem !important;
    }
</style>
@endsection

@section('header')
<section class="content-header">
    <h1>
        Report
        <small>| Neraca</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        {{-- <li><a href="{{ route('transaction-adjustment-ledger') }}">Transaksi Jurnal Closing</a></li> --}}
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
                                <h3 class="box-title">Report Neraca</h3>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <form id="form_report" data-toggle="validator" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-2">
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
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Periode Bulan</label>
                                        <select name="month" id="month" class="form-control select2">
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Periode Tahun</label>
                                        <select name="year" id="year" class="form-control select2">
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tipe</label>
                                        <select name="type" id="type" class="form-control select2">
                                            <option value="recap">Neraca</option>
                                            <option value="detail">Neraca Detail</option>
                                            <option value="init">Neraca Awal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button id="btn-view" type="button" class="btn btn-sm btn-default pull-right mr-1"><i class="fa fa-eye"></i> View</button>
                                    <button id="btn-excel" type="button" class="btn btn-sm btn-success pull-right mr-1"><i class="fa fa-file-excel-o"></i> Excel</button>
                                    <button id="btn-print" type="button" class="btn btn-sm btn-danger pull-right mr-1"><i class="fa fa-print"></i> Print</button>
                                    <button id="hidden-btn" style="display:none;" type="submit">HIDDEN</button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12" id="table_recap_div">
                                    <div class="box-body">
                                        <div class="table-responsive">
                                            <table id="table_balance_recap" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr style="border: 1px solid #f4f4f4;">
                                                        <th style="background-color: #ffffff;" width="60%">Header</th>
                                                        <th style="background-color: #ffffff;" width="40%">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="coa_table">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" id="table_detail_div">
                                    <div class="box-body">
                                        <div class="table-responsive">
                                            <table id="table_balance_detail" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr style="border: 1px solid #f4f4f4;">
                                                        <th style="background-color: #ffffff;" width="15%">Header</th>
                                                        <th style="background-color: #ffffff;" width="20%">Akun</th>
                                                        <th style="background-color: #ffffff;" width="65%">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="coa_table_detail">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('modal-section')

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
<!-- bootstrap datepicker -->
<script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<!-- SlimScroll -->
<script src="{{ asset('assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ asset('assets/bower_components/fastclick/lib/fastclick.js') }}"></script>
<!-- Numeral -->
<script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
<!-- TreeTable -->
<script src="{{ asset('assets/bower_components/jquery-treetable/jquery.treetable.js') }}"></script>
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
    let routeClosingStore = "{{ Route('transaction-closing-journal-store') }}"
    let piutang_dagang
    let hutang_dagang
    var viewButton = document.getElementById("btn-view")
    var excelButton = document.getElementById("btn-excel")
    var printButton = document.getElementById("btn-print")

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
                onSubmit: function(node, formData, event) {
                    // Init data
                    let cabang = $("#cabang_input").val()
                    let year = $("#year").val()
                    let month = $("#month").val()
                    let type = $("#type").val()
                    let param = "?id_cabang=" + cabang + "&year=" + year + "&month=" + month + "&type=" + type
                    switch (guid) {
                        case "view":
                            // Prepare spinner on button
                            viewButton.disabled = true;
                            viewButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
                            view(param, type)
                            break;
                        case "excel":
                            // Prepare spinner on button
                            excelButton.disabled = true;
                            excelButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
                            excel(param)
                            break;
                        case "print":
                            // Prepare spinner on button
                            printButton.disabled = true;
                            printButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
                            print(param)
                            break;

                        default:
                            break;
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
    var guid = 1

    $(function() {
        $.validate(validateLedger)

        $('.select2').select2({
            width: '100%'
        })

        $(".datepicker").datepicker({
            format: "yyyy-mm-dd"
        })

        $("#table_recap_div").hide()
        $("#table_detail_div").hide()

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

        $("#btn-view").on("click", function() {
            guid = 'view'
            console.log('view')
            $("#hidden-btn").click()
        })

        $("#btn-excel").on("click", function() {
            guid = 'excel'
            $("#hidden-btn").click()
        })

        $("#btn-print").on("click", function() {
            guid = 'print'
            console.log('print')
            $("#hidden-btn").click()
        })

    })

    function view(param, type) {
        let route = "{{ Route('report-balance-populate') }}"
        if (type == "recap") {
            console.log('recap')
            $("#table_detail_div").hide()
            $("#table_recap_div").show()
            $('#table_balance_recap').treetable('destroy');
            $.ajax({
                url: route + param,
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    if (data.result) {
                        let data_coa = data.data;
                        body_coa = '';
                        if(jQuery.isEmptyObject(data_coa) == false){
                            getTreetable(data_coa, null, 18);
                            $('#coa_table').html(body_coa);
                            $('#table_balance_recap').treetable({expandable: true}).treetable('expandAll');
                        }
                        else{
                            body_coa += '<tr><td colspan="8" class="text-center">Empty Data</td></tr>';
                            $('#coa_table').html(body_coa);
                        }
                    }
                    else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'Close'
                        })
                    }
                }
            })
        } else {
            $("#table_recap_div").hide()
            $("#table_recap_div").show()
            $('#table_balance_recap').treetable('destroy');
            $.ajax({
                url: route + param,
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    if (data.result) {
                        let data_coa = data.data;
                        body_coa = '';
                        if(jQuery.isEmptyObject(data_coa) == false){
                            getTreetable(data_coa, null, 20);
                            $('#coa_table').html(body_coa);
                            $('#table_balance_recap').treetable({expandable: true}).treetable('expandAll');
                        }
                        else{
                            body_coa += '<tr><td colspan="8" class="text-center">Empty Data</td></tr>';
                            $('#coa_table').html(body_coa);
                        }
                    }
                    else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'Close'
                        })
                    }
                }
            })
        }
        viewButton.disabled = false
        viewButton.innerHTML = '<i class="fa fa-eye"></i> View'
    }

    function excel(param) {
        let route = "{{ Route('report-general-ledger-excel') }}"
        let base_url = "{{ url('') }}";
        window.open(route + param)
        excelButton.disabled = false
        excelButton.innerHTML = '<i class="fa fa-file-excel-o"></i> Excel'
    }

    function print(param) {
        let route = "{{ Route('report-balance-pdf') }}"
        $.ajax({
            type: "GET",
            url: route + param
        }).done(function(data) {
            // console.log(data)
            if (data.result) {
                // Create a new anchor element
                var link = document.createElement('a');
                // Set the PDF data as href attribute
                link.href = 'data:application/pdf;base64,' + data.pdfData;
                // Set the PDF headers as download attribute
                link.setAttribute('download', 'reportBalance.pdf');
                link.setAttribute('target', '_blank');
                // Append the anchor element to the document
                document.body.appendChild(link);
                // Trigger a click on the anchor element to download the PDF
                link.click();
                // Remove the anchor element from the document
                document.body.removeChild(link);
            }
            printButton.disabled = false
            printButton.innerHTML = '<i class="fa fa-print"></i> Print'
        })
    }

    function getTreetable(data, parent, fontSize){
        data.forEach(element => {
            if(parent == null){
                body_coa += '<tr data-tt-id="' + element.header + '">';
            }else{
                body_coa += '<tr data-tt-id="' + element.header + '" data-tt-parent-id="' + parent + '">';
            }
            if(typeof(element.child) != "undefined"){
                body_coa += '<td><b style="font-size:' + fontSize + 'px">' + element.header + '</b></td>';
                body_coa += '<td></td>';
            }else{
                body_coa += '<td style="font-size:' + fontSize + 'px">' + element.header + '</td>';
                body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" >Rp ' + formatCurr(element.total) + '</td>';
            }
            body_coa += '</tr>';
            if(typeof(element.child) != "undefined"){
                getTreetable(element.child, element.header, fontSize - 2);
                if(parent == null){
                    body_coa += '<tr>';
                }else{
                    body_coa += '<tr data-tt-id="total-' + element.header + '" data-tt-parent-id="' + parent + '">';
                }
                body_coa += '<td><b style="font-size:' + fontSize + 'px">Total</b></td>';
                body_coa += '<td class="text-right"><b style="font-size:' + fontSize + 'px">Rp ' + formatCurr(element.total) + '</b></td>';
                body_coa += '</tr>';
            }
        });
    }

    function formatCurr(num) {
        num = String(num);
        num = num.split('.').join("");
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

    function formatNumberAsFloatFromDB(num) {
        num = String(num);
        num = num.replace('.', ',');
        // console.log(num)
        return num;
    }

    $.fn.expandAll = function() {
        $(this).find("tr").removeClass("collapsed").addClass("expanded").each(function(){
            $(this).expand();
        });
    };
</script>
@endsection
