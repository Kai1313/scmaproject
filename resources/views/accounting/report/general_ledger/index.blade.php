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
        .dataTable {
            width: 100%;
            max-width: 100%;
        }

        .dataTables_scrollHeadInner {
            width: 100% !important;
        }

        .table {
            width: 100% !important;
        }

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

        #table_static_report th,
        #table_static_report th {
            text-align: center !important;
            font-size: 13px !important;
            border-color: white !important;
            padding: 0.6rem 0.4rem;
            font-weight: 800;
        }

        #table_static_report td,
        #table_static_report td {
            padding: 0.5rem !important;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Report
            <small>| Buku Besar</small>
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
                                    <h3 class="box-title">Report Buku Besar</h3>
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
                                            <select name="cabang_input" id="cabang_input" class="form-control select2 comp-param"
                                                style="width: 100%;">
                                                @foreach ($data_cabang as $cb)
                                                    <option value="{{ $cb->id_cabang }}"
                                                        {{ isset($cabang) ? ($cabang == $cb->id_cabang ? 'selected' : '') : '' }}>
                                                        {{ $cb->kode_cabang . ' - ' . $cb->nama_cabang }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Tanggal Awal</label>
                                            <input type="text" class="form-control datepicker comp-param" id="start_date"
                                                name="start_date" placeholder="Masukkan tanggal awal"
                                                value="{{ isset($startdate) ? $startdate : date('Y-m-d') }}"
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Tanggal awal tidak boleh kosong">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Tanggal Akhir</label>
                                            <input type="text" class="form-control datepicker comp-param" id="end_date"
                                                name="end_date" placeholder="Masukkan tanggal akhir"
                                                value="{{ isset($enddate) ? $enddate : date('Y-m-d') }}"
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Tanggal akhir tidak boleh kosong">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Tipe</label>
                                            <select name="type" id="type" class="form-control select2 comp-param">
                                                <option value="recap" {{ $type == 'recap' ? 'selected' : '' }}>Rekap
                                                </option>
                                                <option value="detail" {{ $type == 'detail' ? 'selected' : '' }}>Detail
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Akun</label>
                                            <select name="coa" id="coa" class="form-control select2"
                                                data-validation="[NOTEMPTY]"
                                                data-validation-message="Akun tidak boleh kosong" disabled>
                                                <option value="recap">Rekap</option>
                                                <option value="detail">Detail</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button id="btn-view" type="button"
                                            class="btn btn-sm btn-default pull-right mr-1"><i class="fa fa-eye"></i>
                                            View</button>
                                        <button id="btn-excel" type="button"
                                            class="btn btn-sm btn-success pull-right mr-1"><i
                                                class="fa fa-file-excel-o"></i> Excel</button>
                                        <button id="btn-print" type="button"
                                            class="btn btn-sm btn-danger pull-right mr-1"><i class="fa fa-print"></i>
                                            Print</button>
                                        <button id="btn-static" type="button"
                                            class="btn btn-sm btn-primary pull-right mr-1"><i class="fa fa-list-ul"></i>
                                            Static View</button>
                                        <button id="hidden-btn" style="display:none;" type="submit">HIDDEN</button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12" id="table_recap_div">
                                        <table class="table table-striped" id="table_recap" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Kode Akun</th>
                                                    <th>Nama Akun</th>
                                                    <th>Saldo Awal</th>
                                                    <th>Debet</th>
                                                    <th>Kredit</th>
                                                    <th>Saldo Akhir</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="col-md-12" id="table_detail_div">
                                        <table class="table table-striped" id="table_detail" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>No Jurnal</th>
                                                    <th>Cabang</th>
                                                    <th>Kode Akun</th>
                                                    <th>Nama Akun</th>
                                                    <th>Keterangan</th>
                                                    <th>ID Transaksi</th>
                                                    <th>Debet</th>
                                                    <th>Kredit</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <td style="background-color:rgb(238, 238, 238);font-weight:bold;"
                                                        colspan="7">Total
                                                    </td>
                                                    <td
                                                        style="background-color:rgb(238, 238, 238);text-align:right;font-weight:bold;">
                                                    </td>
                                                    <td
                                                        style="background-color:rgb(238, 238, 238);text-align:right;font-weight:bold;">
                                                    </td>
                                                    <td style="background-color:rgb(238, 238, 238);"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="col-md-12" id="table_static_div">
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table id="table_static_report" class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr style="border: 1px solid #f4f4f4;" id="head_row">
                                                            <!-- <th style="background-color: #ffffff;" width="40%"><span id="header_table">Buku Besar</span></th>
                                                            <th style="background-color: #ffffff;" width="15%">Saldo Awal</th>
                                                            <th style="background-color: #ffffff;" width="15%">Debet</th>
                                                            <th style="background-color: #ffffff;" width="15%">Kredit</th>
                                                            <th style="background-color: #ffffff;" width="15%">Saldo Akhir</th> -->
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
    <script src="{{ asset('assets/plugins/jquery-datatables-checkboxes-1.2.12/js/dataTables.checkboxes.min.js') }}">
    </script>
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
        var staticButton = document.getElementById("btn-static")
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        // Init data from controller
        var ctrlAkun = "<?php echo $id_akun; ?>"
        var ctrlStartDate = "<?php echo $startdate; ?>"
        var ctrlEndDate = "<?php echo $enddate; ?>"
        var ctrlCabang = "<?php echo $cabang; ?>"
        var ctrlType = "<?php echo $type; ?>"

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
                        let start = $("#start_date").val()
                        let end = $("#end_date").val()
                        let type = $("#type").val()
                        let coa = $("#coa").val()
                        let param = "?id_cabang=" + cabang + "&start_date=" + start + "&end_date=" + end +
                            "&type=" + type + "&coa=" + coa
                        let route = "{{ Route('dummy-ajax') }}"
                        route = route + param
                        switch (guid) {
                            case "view":
                                view(param, type)
                                break;
                            case "excel":
                                excel(param)
                                break;
                            case "print":
                                print(param)
                                break;
                            case "static":
                                static(param, type, start, end)
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

            // getCoa()
            let initType = (ctrlType) ? ctrlType : "recap"
            checkType(initType)
            $("#table_recap_div").hide()
            $("#table_detail_div").hide()
            $("#table_static_div").hide()

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

            $("#cabang_input").on("change", function() {
                getCoa()
            })

            $("#type").on("change", function() {
                let type = $(this).val()
                checkType(type)
            })

            $("#btn-view").on("click", function() {
                guid = 'view'
                $("#hidden-btn").click()
            })

            $("#btn-excel").on("click", function() {
                guid = 'excel'
                $("#hidden-btn").click()
            })

            $("#btn-print").on("click", function() {
                guid = 'print'
                $("#hidden-btn").click()
            })

            $("#btn-static").on("click", function() {
                guid = 'static'
                $("#hidden-btn").click()
            })

        })

        function checkType(type) {
            if (type == "recap") {
                getCoa()
                $("#coa").attr("disabled", true)
            } else {
                getCoa()
                $("#coa").attr("disabled", false)
            }
        }

        function view(param, type) {
            let route = "{{ Route('report-general-ledger-populate') }}"
            let routeRecap = "{{ Route('report-general-ledger-populate-recap') }}"
            let coa = $("#coa").val()
            // Prepare spinner on button
            viewButton.disabled = true;
            viewButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
            $(".comp-param").attr("disabled", true)
            if (type == "recap") {
                $("#table_detail_div").hide()
                $("#table_static_div").hide()
                $("#table_recap_div").hide()
                $('#table_recap').DataTable().destroy();
                $('#table_recap').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": false,
                    "bDestroy": true,
                    responsive: true,
                    "lengthMenu": [
                        [-1, 100, 50, 20, 10],
                        ["All", 100, 50, 20, 10]
                    ],
                    ajax: {
                        "url": routeRecap + param,
                        "type": "GET",
                        "dataType": "JSON",
                        "error": function(xhr, textStatus, ThrownException) {
                            alert("Error loading data. Exception: " + ThrownException + '\n' + textStatus)
                            viewButton.disabled = false
                            viewButton.innerHTML = '<i class="fa fa-eye"></i> View'
                            $(".comp-param").attr("disabled", false)
                            $("#table_recap_div").show()
                        }
                    },
                    "initComplete": function() {
                        console.log("init complete recap");
                        viewButton.disabled = false
                        viewButton.innerHTML = '<i class="fa fa-eye"></i> View'
                        $(".comp-param").attr("disabled", false)
                        $("#table_recap_div").show()
                    },
                    columns: [
                        {
                            data: 'kode_akun',
                            name: 'kode_akun',
                            width: '10%',
                            sType: 'string',
                            className: 'text-left',
                            responsivePriority: 1
                        }, 
                        {
                            data: 'nama_akun',
                            name: 'nama_akun',
                            width: '12%',
                            className: 'text-left',
                            responsivePriority: 2,
                            render: function(data, type, row) {
                                let cabang = $("#cabang_input").val()
                                let startdate = $("#start_date").val()
                                let enddate = $("#end_date").val()
                                let customRoute = "{{ route('report-general-ledger') }}"
                                customRoute += ((cabang == '')?'?kode_akun=' + row['kode_akun'] : '?id_akun=' + row["id_akun"]) + '&cabang=' + cabang +
                                    '&startdate=' + startdate + '&enddate=' + enddate + '&type=detail'
                                let namaAkun = '<a href="' + customRoute + '" target="__blank">' + data +
                                    '</a>'
                                return namaAkun
                            }
                        }, 
                        {
                            data: 'saldo_start',
                            name: 'saldo_start',
                            width: '10%',
                            className: 'text-right',
                            searchable: false,
                            orderable: false,
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data))
                            }
                        }, 
                        {
                            data: 'debet',
                            name: 'debet',
                            width: '10%',
                            className: 'text-right',
                            searchable: false,
                            orderable: false,
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data))
                            }
                        }, 
                        {
                            data: 'credit',
                            name: 'credit',
                            width: '10%',
                            className: 'text-right',
                            searchable: false,
                            orderable: false,
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data))
                            }
                        }, 
                        {
                            data: 'saldo_balance',
                            name: 'saldo_balance',
                            width: '10%',
                            className: 'text-right',
                            searchable: false,
                            orderable: false,
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data))
                            }
                        }
                    ],
                    "order": [[0, "asc"]]
                })
            } else {
                var runningCoa = ""
                var runningBalance = 0
                $("#table_recap_div").hide()
                $("#table_static_div").hide()
                $("#table_detail_div").hide()
                $('#table_detail').DataTable().destroy();
                $('#table_detail').DataTable({
                    processing: true,
                    serverSide: true,
                    "scrollX": false,
                    "bDestroy": true,
                    responsive: true,
                    "lengthMenu": [
                        [-1, 100, 50, 20, 10],
                        ["All", 100, 50, 20, 10]
                    ],
                    ajax: {
                        "url": route + param,
                        "type": "GET",
                        "dataType": "JSON",
                        "error": function(xhr, textStatus, ThrownException) {
                            alert("Error loading data. Exception: " + ThrownException + '\n' + textStatus)
                            viewButton.disabled = false
                            viewButton.innerHTML = '<i class="fa fa-eye"></i> View'
                            $(".comp-param").attr("disabled", false)
                            $("#table_detail_div").show()
                        }
                    },
                    "initComplete": function() {
                        console.log("init complete recap");
                        viewButton.disabled = false
                        viewButton.innerHTML = '<i class="fa fa-eye"></i> View'
                        $(".comp-param").attr("disabled", false)
                        $("#table_detail_div").show()
                    },
                    drawCallback: function(settings) {
                        $($('#table_detail').find('tbody tr')[0]).css('background-color', 'rgb(238, 238, 238)')
                    },
                    footerCallback: function(row, data, start, end, display) {
                        var api = this.api(),
                            data;
                        var totalDebet = api
                            .column(7)
                            .data()
                            .reduce(function(a, b, i) {
                                return i == 0 ? 0 : (a + parseFloat(b));
                            }, 0);

                        var totalCredit = api
                            .column(8)
                            .data()
                            .reduce(function(a, b, i) {
                                return i == 0 ? 0 : (a + parseFloat(b));
                            }, 0);

                        $(api.column(0).footer()).html('Total');
                        $(api.column(7).footer()).html(
                            formatCurr(formatNumberAsFloatFromDB(totalDebet.toFixed(2)))
                        );
                        $(api.column(8).footer()).html(
                            formatCurr(formatNumberAsFloatFromDB(totalCredit.toFixed(2)))
                        );
                    },
                    columns: [
                        {
                            data: 'tanggal_jurnal',
                            name: 'tanggal_jurnal',
                            width: '10%',
                            searchable: false,
                            orderable: false
                        }, 
                        {
                            data: 'kode_jurnal',
                            name: 'kode_jurnal',
                            width: '10%',
                            orderable: false,
                            render: function(data, type, row) {
                                let route = '';
                                let detail_route = (row["jenis_jurnal"] == "ME") ?
                                    "{{ route('transaction-adjustment-ledger-edit') }}" :
                                    "{{ route('transaction-general-ledger-edit') }}"

                                if (row.id_jurnal) {
                                    route = '<a href="' + detail_route + '/' + row.id_jurnal +
                                        '" target="_blank">' + data + '</a>'
                                } else {
                                    route = '';
                                }
                                return route
                            }
                        }, 
                        {
                            data: 'nama_cabang',
                            name: 'nama_cabang',
                            width: '10%',
                            searchable: false,
                            orderable: false,
                            visible: ($("#cabang_input").val() == "")?true:false
                        }, 
                        {
                            data: 'kode_akun',
                            name: 'kode_akun',
                            width: '10%',
                            orderable: false
                        }, 
                        {
                            data: 'nama_akun',
                            name: 'nama_akun',
                            width: '12%',
                            orderable: false
                        }, 
                        {
                            data: 'keterangan',
                            name: 'keterangan',
                            width: '10%',
                            orderable: false,
                            render: function(data, type, row) {
                                // if (data != '' && data != null) {
                                //     return data.substring(0, 15)
                                // } else {
                                //     return data;
                                // }
                                return data;
                            }
                        }, 
                        {
                            data: 'id_transaksi',
                            name: 'id_transaksi',
                            orderable: false,
                            width: '10%'
                        }, 
                        {
                            data: 'debet',
                            name: 'debet',
                            width: '10%',
                            searchable: false,
                            orderable: false,
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data))
                            }
                        }, 
                        {
                            data: 'credit',
                            name: 'credit',
                            width: '10%',
                            searchable: false,
                            orderable: false,
                            className: 'text-right',
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data))
                            }
                        }, 
                        {
                            data: 'saldo_balance',
                            name: 'saldo_balance',
                            width: '10%',
                            searchable: false,
                            orderable: false,
                            className: 'text-right',
                            visible: (coa != 'all') ? true : false,
                            render: function(data, type, row) {
                                return formatCurr(formatNumberAsFloatFromDB(data.toFixed(2)))
                            }
                        }
                    ],
                    "order": []
                })
            }
        }

        function excel(param) {
            // Prepare spinner on button
            excelButton.disabled = true;
            excelButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
            let route = "{{ Route('report-general-ledger-excel') }}"
            let base_url = "{{ url('') }}";
            window.open(route + param)
            excelButton.disabled = false
            excelButton.innerHTML = '<i class="fa fa-file-excel-o"></i> Excel'
        }

        function print(param) {
            // Prepare spinner on button
            printButton.disabled = true;
            printButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
            let route = "{{ Route('report-general-ledger-pdf') }}"
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
                    link.setAttribute('download', 'reportGeneralLedger.pdf');
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

        function static(param, type, start, end) {
            let route = "{{ Route('report-general-ledger-populate-static-recap') }}"
            let routeDetail = "{{ Route('report-general-ledger-populate-static-detail') }}"
            let coa = $("#coa").val()

            let cabangInput = $('#cabang_input').val();

            // Prepare spinner on button
            staticButton.disabled = true;
            staticButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
            if (type == "recap") {
                $("#table_detail_div").hide()
                $("#table_recap_div").hide()
                $("#table_static_div").show()
                $('#table_static_report').treetable('destroy');
                $('#table_detail').DataTable().destroy()
                $('#table_recap').DataTable().destroy()
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
                                // console.log("data from");
                                // console.log(data_coa);
                                let html_thead = '<th style="background-color: #ffffff;" width="40%"><span id="header_table">Buku Besar ' + formatDate(start) + ' s/d ' + formatDate(end) + '</span></th><th style="background-color: #ffffff;" width="15%">Saldo Awal</th><th style="background-color: #ffffff;" width="15%">Debet</th><th style="background-color: #ffffff;" width="15%">Kredit</th><th style="background-color: #ffffff;" width="15%">Saldo Akhir</th>';
                                $('#head_row').html('');
                                $('#head_row').html(html_thead);
                                getTreetable(data_coa, null, 13, 'detail', route_general_ledger);
                                // getTotal(data_total, route_general_ledger, cabangInput);

                                $('#coa_table').html(body_coa);
                                $('#coa_table').append(body_total);
                                $('#table_static_report').treetable({
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
                    },
                    complete: function(e){
                        staticButton.disabled = false
                        staticButton.innerHTML = '<i class="fa fa-list-ul"></i> Static View'
                    }
                })
            } else {
                var runningCoa = ""
                var runningBalance = 0
                $("#table_static_div").show()
                $('#table_static_report').treetable('destroy');
                $("#table_detail_div").hide()
                $("#table_recap_div").hide()
                $('#table_detail').DataTable().destroy()
                $('#table_recap').DataTable().destroy()
                
                $.ajax({
                    url: routeDetail + param,
                    type: "GET",
                    dataType: "JSON",
                    success: function(data) {
                        if (data.result) {
                            let data_coa = data.data;
                            let list_cabang = null;
                            let route_general_ledger = "{{ route('report-general-ledger') }}";
                            body_coa = '';
                            body_total = '';

                            let previousIdJurnal = null;
                            let nextIdJurnal = null;
                            let journalSummaries = {};

                            if (jQuery.isEmptyObject(data_coa) == false) {
                                // console.log("data from");
                                // console.log(data_coa);
                                let html_thead = '<th style="background-color: #ffffff;" width="20%"><span id="header_table">No Akun</span></th><th style="background-color: #ffffff;" width="50%">Nama Akun</th><th style="background-color: #ffffff;" width="15%">Debet</th><th style="background-color: #ffffff;" width="15%">Kredit</th>';
                                $('#head_row').html('');
                                $('#head_row').html(html_thead);      
                                let fontSize = 13;                          

                                for (let jurnal of data_coa) {  
                                    let route = '';
                                    let detail_route = (jurnal.jenis_jurnal == "ME") ?
                                        "{{ route('transaction-adjustment-ledger-edit') }}" :
                                        "{{ route('transaction-general-ledger-edit') }}"

                                    if (jurnal.id_jurnal) {
                                        route = '<a href="' + detail_route + '/' + jurnal.id_jurnal +
                                            '" target="_blank">' + jurnal.kode_jurnal + '</a>'
                                    } else {
                                        route = '';
                                    }

                                    if (nextIdJurnal !== null && jurnal.id_jurnal !== nextIdJurnal) {
                                        body_coa += '<tr data-tt-id="' + jurnal.id_jurnal + 'Total" data-tt-parent-id="' + jurnal.id_jurnal + '">';
                                        body_coa += '<td colspan = 2" style="font-size:' + fontSize + 'px" ><b>Total</b></td>';
                                        body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" ><b>' + formatCurr(formatNumberAsFloatFromDB(journalSummaries[previousIdJurnal].debet.toFixed(2))) + '</b></td>';
                                        body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" ><b>' + formatCurr(formatNumberAsFloatFromDB(journalSummaries[previousIdJurnal].debet.toFixed(2))) + '</b></td>';
                                        body_coa += '</tr>';
                                    }

                                    if (jurnal.id_jurnal !== previousIdJurnal) {  
                                        body_coa += '<tr data-tt-id="' + jurnal.id_jurnal + '">';    
                                        body_coa += '<td><b style="font-size:' + fontSize + 'px">' + jurnal.tanggal_jurnal + '</b></td>';                
                                        body_coa += '<td colspan="3"><b style="font-size:' + fontSize + 'px">' + route + ' </b></td>';         
                                        body_coa += '</tr>';
                                    }  

                                    body_coa += '<tr data-tt-id="' + jurnal.id_akun + '" data-tt-parent-id="' + jurnal.id_jurnal + '">';
                                    let debet = parseFloat(jurnal.debet);
                                    let credit = parseFloat(jurnal.credit);
                                    body_coa += '<td style="font-size:' + fontSize + 'px">' + jurnal.kode_akun + '</td>';
                                    body_coa += '<td style="font-size:' + fontSize + 'px">' + jurnal.nama_akun + '</td>';
                                    body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" >' + formatCurr(formatNumberAsFloatFromDB(debet.toFixed(2))) + '</td>';
                                    body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" >' + formatCurr(formatNumberAsFloatFromDB(credit.toFixed(2))) + '</td>';
                                    body_coa += '</tr>';

                                    if (!journalSummaries[jurnal.id_jurnal]) {
                                        journalSummaries[jurnal.id_jurnal] = { debet: 0, credit: 0 };
                                    }

                                    journalSummaries[jurnal.id_jurnal].debet += parseFloat(jurnal.debet) || 0;
                                    journalSummaries[jurnal.id_jurnal].credit += parseFloat(jurnal.credit) || 0;

                                    previousIdJurnal = jurnal.id_jurnal;
                                    nextIdJurnal = jurnal.id_jurnal;
                                }

                                $('#coa_table').html(body_coa);
                                $('#coa_table').append(body_total);
                                $('#table_static_report').treetable({
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
                    },
                    complete: function(e){
                        staticButton.disabled = false
                        staticButton.innerHTML = '<i class="fa fa-list-ul"></i> Static View'
                    }
                })
            }
        }

        function getTreetable(data, parent, fontSize, reportType, ledgerRoute) {
            Object.values(data).forEach(element => {
                if (parent == null) {
                    body_coa += '<tr data-tt-id="' + element.header + '">';
                } else {
                    body_coa += '<tr data-tt-id="' + element.header + '" data-tt-parent-id="' + parent + '">';
                }
                if (typeof(element.children) != "undefined") {
                    body_coa += '<td><b style="font-size:' + fontSize + 'px">' + element.header + '</b></td>';
                    body_coa += '<td colspan="4"></td>';
                } else {                    
                    let debet = parseFloat(element.debet);
                    let credit = parseFloat(element.credit);
                    body_coa += '<td style="font-size:' + fontSize + 'px">' + element.header + ' (Rp)</td>';
                    body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" >' + formatCurr(formatNumberAsFloatFromDB(element.saldo_awal.toFixed(2))) + '</td>';
                    body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" >' + formatCurr(formatNumberAsFloatFromDB(debet.toFixed(2))) + '</td>';
                    body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" >' + formatCurr(formatNumberAsFloatFromDB(credit.toFixed(2))) + '</td>';
                    if (reportType.includes("detail") && reportType.includes('awal') == false) {
                        body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px" ><a href="' + ledgerRoute + '?id_akun=' + element.akun + '&cabang=' + element.id_cabang + '&startdate=' + element.start_date + '&enddate=' + element.end_date + '&type=detail" target="_blank">' + formatCurr(formatNumberAsFloatFromDB(element.saldo_akhir.toFixed(2))) + '</a></td>';
                    } else {
                        if (element.saldo_akhir.toFixed(2) < 0) {
                            fontColor = '#FA0202'
                        } else {
                            fontColor = '#000000'
                        }
                        body_coa += '<td class="text-right" style="font-size:' + fontSize + 'px; color: ' + fontColor + '" >' + formatCurr(formatNumberAsFloatFromDB(element.saldo_akhir.toFixed(2))) + '</td>';
                    }
                }
                body_coa += '</tr>';
                if (typeof(element.children) != "undefined") {
                    getTreetable(element.children, element.header, fontSize, reportType, ledgerRoute);
                    if (parent == null) {
                        body_coa += '<tr>';
                    } else {
                        body_coa += '<tr data-tt-id="saldo_akhir-' + element.header + '" data-tt-parent-id="' + parent + '">';
                    }
                    if (element.saldo_akhir.toFixed(2) < 0) {
                        fontColor = '#FA0202'
                    } else {
                        fontColor = '#000000'
                    }
                    body_coa += '<td><b style="font-size:' + fontSize + 'px">Total ' + element.header + ' (Rp)</b></td><td colspan="3"></td>';
                    body_coa += '<td class="text-right"><b style="font-size:' + fontSize + 'px; color: ' + fontColor + '" >' + formatCurr(formatNumberAsFloatFromDB(element.saldo_akhir.toFixed(2))) + '</b></td>';
                    body_coa += '</tr>';
                }
            });
        }

        function getTotal(total, ledgerRoute, cabang) {
            body_total += '<tr><td><b style="font-size:13px">LABA(RUGI) BERSIH</b></td></td>'

            if (total['grand_total'].toFixed(2) < 0) {
                fontColor = '#FA0202'
            } else {
                fontColor = '#000000'
            }

            body_total += '<td class="text-right"><b style="font-size:13px; color: ' + fontColor + '" ">' + formatCurr(formatNumberAsFloatFromDB(total['grand_total'].toFixed(2))) + '</b></td>';

            body_total += '</tr>';
        }

        function getCoa() {
            let id_cabang = ($("#cabang_input").val() == "")?"all":$("#cabang_input").val()
            let current_coa_route = coa_by_cabang_route.replace(':id', id_cabang);

            $.getJSON(current_coa_route, function(data) {
                if (data.result) {
                    $('#coa').html('');

                    let data_akun = data.data;
                    let option_akun = '';

                    option_akun += `<option value="">Pilih Akun</option>`;
                    option_akun += `<option value="all">All</option>`;
                    data_akun.forEach(akun => {
                        option_akun +=
                            `<option value="${akun.id_akun}" data-nama="${akun.nama_akun}" data-kode="${akun.kode_akun}">${akun.kode_akun} - ${akun.nama_akun}</option>`;
                    });
                    $('#coa').append(option_akun);
                }
            }).done(function() {
                if (ctrlAkun != null) {
                    $("#coa").val(ctrlAkun).trigger("change")
                    $("#btn-view").click()
                }
                ctrlAkun = null
            })
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

        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = date.getDate();
            const month = date.getMonth();
            const year = date.getFullYear();
            return day + ' ' + monthNames[month] + ' ' + year;
        }
    </script>
@endsection
