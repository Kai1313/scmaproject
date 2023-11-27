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
    #table_balance_recap th {
        text-align: center !important;
        font-size: 13px !important;
        border-color: white !important;
        padding: 0.6rem 0.4rem;
        font-weight: 800;
    }

    #table_balance_recap td,
    #table_balance_recap td {
        padding: 0.5rem !important;
    }
</style>
@endsection

@section('header')
<section class="content-header">
    <h1>
        Report
        <small>| Laba Rugi</small>
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
                                <h3 class="box-title">Report Laba Rugi</h3>
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
                                            <option value="recap">Laba Rugi</option>
                                            <option value="detail">Laba Rugi Detail</option>
                                            {{-- <option value="awal">Laba Rugi Awal</option>
                                            <option value="awal_detail">Laba Rugi Awal Detail</option> --}}
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
                                                    <tr style="border: 1px solid #f4f4f4;" id="head_row">
                                                        <th style="background-color: #ffffff;" width="70%"><span id="header_table">Laba Rugi</span></th>
                                                        <th style="background-color: #ffffff;" width="30%">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="coa_table">
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
                    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                    let cabang = $("#cabang_input").val()
                    let year = $("#year").val()
                    let month = $("#month").val()
                    let type = $("#type").val()
                    let param = "?id_cabang=" + cabang + "&year=" + year + "&month=" + month + "&type=" + type;

                    switch (guid) {
                        case "view":
                            // Prepare spinner on button
                            viewButton.disabled = true;
                            viewButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
                            view(param, type, month, year)
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

                    $('#header_table').text('Laba Rugi ' + monthNames[month - 1] + ' ' + year);
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

    function view(param, type, month, year) {
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        let report_type = type;
        let route = "{{ Route('report-profit-loss-populate') }}"

        let cabangInput = $('#cabang_input').val();

        // Parse the 'period' string into a Date object
        let periodDate = new Date(year + '-' + month + '-01');

        // Create a new Date object for the start of the month
        let startDate = new Date(periodDate.getFullYear(), periodDate.getMonth(), 1);

        // Create a new Date object for the end of the month
        let endDate = new Date(periodDate.getFullYear(), periodDate.getMonth() + 1, 0);

        // Format the dates as 'YYYY-MM-DD'
        startDate = startDate.toLocaleDateString('en-CA');
        endDate = endDate.toLocaleDateString('en-CA');

        $("#table_detail_div").hide()
        $("#table_recap_div").show()
        $('#table_balance_recap').treetable('destroy');
        $.ajax({
            url: route + param,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                if (data.result) {
                    let data_coa = data.data.data;
                    let data_total = data.data.total;
                    let list_cabang = null;
                    let route_general_ledger = "{{ route('report-general-ledger') }}";
                    if ($('#cabang_input').val() == '') {
                        data_coa = data.data.data;
                        data_total = data.data.total;
                        list_cabang = data.data.cabang;
                    }
                    body_coa = '';
                    body_total = '';
                    if (jQuery.isEmptyObject(data_coa) == false) {
                        console.log("data from");
                        // console.log(data_coa);
                        if (list_cabang == null) {
                            let html_thead = '<th style="background-color: #ffffff;" width="40%"><span id="header_table">Laba Rugi ' + monthNames[month - 1] + ' ' + year + '</span></th><th style="background-color: #ffffff;" width="70%">Total</th>';
                            $('#head_row').html('');
                            $('#head_row').html(html_thead);
                            getTreetable(data_coa, null, 13, report_type, route_general_ledger);
                            getTotal(data_total, route_general_ledger, cabangInput, startDate, endDate);
                        } else {
                            let html_thead = '<th style="background-color: #ffffff;" width="40%"><span id="header_table">Laba Rugi ' + monthNames[month - 1] + ' ' + year + '</span></th>';
                            list_cabang.forEach(function(cabang) {
                                html_thead += '<th style="background-color: #ffffff;" width="20%">Total ' + capitalize(cabang.new_nama_cabang.replace('_', ' ')) + '</th>';
                            });
                            html_thead += '<th style="background-color: #ffffff;" width="20%">Total</th>';
                            $('#head_row').html('');
                            $('#head_row').html(html_thead);

                            getTreetableConsolidation(data_coa, null, 13, list_cabang, report_type, route_general_ledger, data_total);
                            getTotalConsolidation(data_total, list_cabang, route_general_ledger, startDate, endDate);
                        }
                        $('#coa_table').html(body_coa);
                        $('#coa_table').append(body_total);
                        $('#table_balance_recap').treetable({
                            expandable: true
                        }).treetable('expandAll');
                    } else {
                        body_coa += '<tr><td colspan="8" class="text-center">Empty Data</td></tr>';
                        $('#coa_table').html(body_coa);
                    }
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'Close'
                    })
                }
            }
        })
        viewButton.disabled = false
        viewButton.innerHTML = '<i class="fa fa-eye"></i> View'
    }

    function excel(param) {
        let route = "{{ Route('report-profit-loss-excel') }}"
        let base_url = "{{ url('') }}";
        window.open(route + param)
        excelButton.disabled = false
        excelButton.innerHTML = '<i class="fa fa-file-excel-o"></i> Excel'
    }

    function print(param) {
        let route = "{{ Route('report-profit-loss-pdf') }}"
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
                link.setAttribute('download', 'reportProfitLoss.pdf');
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

    function getTreetableConsolidation(data, parent, fontSize, listCabang, reportType, ledgerRoute) {
        Object.values(data).forEach(element => {
            if (parent == null) {
                body_coa += '<tr data-tt-id="' + element.header + '">';
            } else {
                body_coa += '<tr data-tt-id="' + element.header + '" data-tt-parent-id="' + parent + '">';
            }
            if (typeof(element.children) != "undefined") {
                body_coa += '<td><b style="font-size:' + fontSize + 'px">' + element.header + '</b></td>';
                listCabang.forEach(function(cabang) {
                    body_coa += '<td></td>';
                });
                body_coa += '<td></td>';
            } else {
                body_coa += '<td style="font-size:' + fontSize + 'px">' + element.header + ' (Rp)</td>';
                listCabang.forEach(function(cabang) {
                    let format = 'total_' + cabang.new_nama_cabang;
                    if (element[format].toFixed(2) < 0) {
                        fontColor = '#FA0202'
                    } else {
                        fontColor = '#000000'
                    }

                    if (reportType.includes('detail') && reportType.includes('awal') == false) {
                        body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px; color: ' + fontColor + '" ><a href="' + ledgerRoute + '?kode_akun=' + element.kode_akun + '&cabang=' + cabang.id_cabang + '&startdate=' + element.start_date + '&enddate=' + element.end_date + '&type=detail" target="_blank">' + formatCurr(formatNumberAsFloatFromDB(element[format].toFixed(2))) + '</a></td>';
                    } else {
                        body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px; color: ' + fontColor + '" >' + formatCurr(formatNumberAsFloatFromDB(element[format].toFixed(2))) + '</td>';
                    }
                });

                if (element['total_all'].toFixed(2) < 0) {
                    fontColor = '#FA0202'
                } else {
                    fontColor = '#000000'
                }
                
                body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px; color: ' + fontColor + '" ><a href="' + ledgerRoute + '?kode_akun=' + element.kode_akun + '&cabang=&startdate=' + element.start_date + '&enddate=' + element.end_date + '&type=detail" target="_blank">' + formatCurr(formatNumberAsFloatFromDB(element['total_all'].toFixed(2))) + '</a></td>';

                // body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px; color: ' + fontColor + '" >' + formatCurr(formatNumberAsFloatFromDB(element['total_all'].toFixed(2))) + '</td>';
            }
            body_coa += '</tr>';
            if (typeof(element.children) != "undefined") {
                getTreetableConsolidation(element.children, element.header, fontSize, listCabang, reportType, ledgerRoute);
                if (parent == null) {
                    body_coa += '<tr>';
                } else {
                    body_coa += '<tr data-tt-id="total-' + element.header + '" data-tt-parent-id="' + parent + '">';
                }
                body_coa += '<td><b style="font-size:' + fontSize + 'px">Total ' + element.header + ' (Rp)</b></td>';
                listCabang.forEach(function(cabang) {
                    let format = 'total_' + cabang.new_nama_cabang;
                    if (element[format].toFixed(2) < 0) {
                        fontColor = '#FA0202'
                    } else {
                        fontColor = '#000000'
                    }

                    body_coa += '<td class="text-right"><b style="font-size:' + fontSize + 'px; color: ' + fontColor + '" >' + formatCurr(formatNumberAsFloatFromDB(element[format].toFixed(2))) + '</b></td>';
                });

                if (element['total_all'].toFixed(2) < 0) {
                    fontColor = '#FA0202'
                } else {
                    fontColor = '#000000'
                }
                
                // body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px; color: ' + fontColor + '" ><a href="' + ledgerRoute + '?kode_akun=' + element.kode_akun + '&cabang=&startdate=' + element.start_date + '&enddate=' + element.end_date + '&type=detail" target="_blank">' + formatCurr(formatNumberAsFloatFromDB(element['total_all'].toFixed(2))) + '</a></td>';

                body_coa += '<td class="text-right"><b style="font-size:' + fontSize + 'px; color: ' + fontColor + '" >' + formatCurr(formatNumberAsFloatFromDB(element['total_all'].toFixed(2))) + '</b></td>';
                body_coa += '</tr>';
            }
        });
    }

    function getTreetable(data, parent, fontSize, reportType, ledgerRoute) {
        console.log(reportType);
        Object.values(data).forEach(element => {
            if (parent == null) {
                body_coa += '<tr data-tt-id="' + element.header + '">';
            } else {
                body_coa += '<tr data-tt-id="' + element.header + '" data-tt-parent-id="' + parent + '">';
            }
            if (typeof(element.children) != "undefined") {
                body_coa += '<td><b style="font-size:' + fontSize + 'px">' + element.header + '</b></td>';
                body_coa += '<td></td>';
            } else {
                body_coa += '<td style="font-size:' + fontSize + 'px">' + element.header + ' (Rp)</td>';
                if (reportType.includes("detail") && reportType.includes('awal') == false) {
                    body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" ><a href="' + ledgerRoute + '?id_akun=' + element.akun + '&cabang=' + element.id_cabang + '&startdate=' + element.start_date + '&enddate=' + element.end_date + '&type=detail" target="_blank">' + formatCurr(formatNumberAsFloatFromDB(element.total.toFixed(2))) + '</a></td>';
                } else {
                    if (element.total.toFixed(2) < 0) {
                        fontColor = '#FA0202'
                    } else {
                        fontColor = '#000000'
                    }
                    body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px; color: ' + fontColor + '" >' + formatCurr(formatNumberAsFloatFromDB(element.total.toFixed(2))) + '</td>';
                }
            }
            body_coa += '</tr>';
            if (typeof(element.children) != "undefined") {
                getTreetable(element.children, element.header, fontSize, reportType, ledgerRoute);
                if (parent == null) {
                    body_coa += '<tr>';
                } else {
                    body_coa += '<tr data-tt-id="total-' + element.header + '" data-tt-parent-id="' + parent + '">';
                }
                if (element.total.toFixed(2) < 0) {
                    fontColor = '#FA0202'
                } else {
                    fontColor = '#000000'
                }
                body_coa += '<td><b style="font-size:' + fontSize + 'px">Total ' + element.header + ' (Rp)</b></td>';
                body_coa += '<td class="text-right"><b style="font-size:' + fontSize + 'px; color: ' + fontColor + '" >' + formatCurr(formatNumberAsFloatFromDB(element.total.toFixed(2))) + '</b></td>';
                body_coa += '</tr>';
            }
        });
    }

    function getTotal(total, ledgerRoute, cabang, startDate, endDate) {
        body_total += '<tr><td><b style="font-size:13px">LABA(RUGI) BERSIH</b></td></td>'

        if (total['grand_total'].toFixed(2) < 0) {
            fontColor = '#FA0202'
        } else {
            fontColor = '#000000'
        }
        
        // body_total += '<td class="text-right"><a href="' + ledgerRoute + '?id_akun=all&cabang=' + cabang + '&startdate=' + startDate + '&enddate=' + endDate + '&type=detail" target="_blank"><b style="font-size:13px; color: ' + fontColor + '" ">' + formatCurr(formatNumberAsFloatFromDB(total['grand_total'].toFixed(2))) + '</b></a></td>';
        body_total += '<td class="text-right"><b style="font-size:13px; color: ' + fontColor + '" ">' + formatCurr(formatNumberAsFloatFromDB(total['grand_total'].toFixed(2))) + '</b></td>';


        body_total += '</tr>';
    }

    function getTotalConsolidation(total, cabang, ledgerRoute, startDate, endDate) {
        body_total += '<tr><td><b style="font-size:13px">LABA(RUGI) BERSIH</b></td></td>'
        cabang.forEach(function(cabang) {
            let format = 'grand_total_' + cabang.new_nama_cabang;

            if (total[format].toFixed(2) < 0) {
                fontColor = '#FA0202'
            } else {
                fontColor = '#000000'
            }

            body_total += '<td class="text-right"><b style="font-size:13px; color: ' + fontColor + '" ">' + formatCurr(formatNumberAsFloatFromDB(total[format].toFixed(2))) + '</b></td>';
        });

        if (total['grand_total'].toFixed(2) < 0) {
            fontColor = '#FA0202'
        } else {
            fontColor = '#000000'
        }

        body_total += '<td class="text-right"><b style="font-size:13px; color: ' + fontColor + '" ">' + formatCurr(formatNumberAsFloatFromDB(total['grand_total'].toFixed(2))) + '</b></td>';

        body_total += '</tr>';
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

    function capitalize(string) {
        //split the above string into an array of strings
        //whenever a blank space is encountered
        const arr = string.split(" ");

        //loop through each element of the array and capitalize the first letter.
        for (var i = 0; i < arr.length; i++) {
            arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1);

        }

        //Join all the elements of the array back into a string
        //using a blankspace as a separator
        const str2 = arr.join(" ");

        return str2;
    }

    $.fn.expandAll = function() {
        $(this).find("tr").removeClass("collapsed").addClass("expanded").each(function() {
            $(this).expand();
        });
    };
</script>
@endsection