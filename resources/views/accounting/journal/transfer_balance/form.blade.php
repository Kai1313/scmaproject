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
        Transaksi Transfer Saldo
        <small>| Tambah</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="#">Transaksi Transfer Saldo</a></li>
        <!-- <li class="active">Form</li> -->
    </ol>
</section>
@endsection

@section('main-section')
<div class="content container-fluid">
    <form id="form" data-toggle="validator" enctype="multipart/form-data">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-12">
                                <h3 class="box-title">Tambah Transfer Saldo</h3>
                                <!-- <a href="{{ route('transaction-transfer-balance') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"><span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span>
                                    Kembali</a> -->
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <form id="form_ledger" data-toggle="validator" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
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
                                        <label>Bulan Awal</label>
                                        <select name="start_month" id="start_month" class="form-control select2">
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
                                        <label>Bulan Akhir</label>
                                        <select name="end_month" id="end_month" class="form-control select2">
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
                                        <label>Tahun</label>
                                        <select name="year" id="year" class="form-control select2">
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <button id="btn-process" type="button" class="btn btn-flat btn-success mr-1 mb-1 pull-left">Proses</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div id="response1"></div><br>
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
    let routeClosingStore = "{{ Route('transaction-transfer-balance-store') }}"
    let routeSaldoTransfer = "{{ Route('transaction-transfer-balance-saldo-transfer') }}"
    let piutang_dagang
    let hutang_dagang
    var myButton = document.getElementById("btn-process")

    var validateForm = {
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
                    console.log('test');
                    console.log(formData);
                    let cabang = $("#cabang_input").val()
                    let start_month = $("#start_month").val()
                    let end_month = $("#end_month").val()
                    let year = $("#year").val()
                    let param = "?id_cabang=" + cabang + "&month=" + month + "&year=" + year
                    let route = "{{ Route('dummy-ajax') }}"

                    if (start_month <= end_month) {
                        // Start ajax chain
                        save_data(routeSaldoTransfer, param, "1")
                    } else {
                        Swal.fire("Sorry, Can't save data. ", "Bulan Awal tidak bisa lebih besar dari Bulan Akhir", 'error')
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
        $.validate(validateForm)

        $('.select2').select2({
            width: '100%'
        })

        $("#btn-process").on("click", function() {
            // console.log("Clicked")
            // Init data
            let cabang = $("#cabang_input").val()
            let start_month = $("#start_month").val()
            let end_month = $("#end_month").val()
            let year = $("#year").val()
            let param = "?id_cabang=" + cabang + "&start_month=" + start_month + "&end_month=" + end_month + "&year=" + year
            // console.log(param);
            let route = "{{ Route('dummy-ajax') }}"
            route = route + param
            // console.log(route)

            // Prepare spinner on button
            myButton.disabled = true;
            myButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'

            // Cleanse response div
            $("#response1").empty()

            // Start ajax chain
            if (parseInt(start_month) <= parseInt(end_month)) {
                // Start ajax chain
                save_data(routeSaldoTransfer, param, "1")
            } else {
                Swal.fire("Sorry, Can't save data. ", "Bulan Awal tidak bisa lebih besar dari Bulan Akhir", 'error')
                myButton.disabled = false
                myButton.innerHTML = "Proses"
            }
        })

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

    })

    function save_data(route, param, step) {
        $.ajax({
            type: "GET",
            url: route + param,
        }).done(function(data) {
            console.log("ajax response " + step)
            console.log(data)
            let res = '<span><i class="fa fa-check-circle" style="color: green;"></i>' + data.message + '</span>'
            let fail = '<span><i class="fa fa-times-circle" style="color: red;"></i> ' + data.message + '</span>'
            if (data.result) {
                console.log("succeed")
                $("#response" + step).append(res)
                alert('Successfully store transfer saldo data')
                myButton.disabled = false
                myButton.innerHTML = "Proses"
                // location.reload()
            } else {
                console.log("ajax response false " + step)
                $("#response" + step).append(fail)
                myButton.disabled = false
                myButton.innerHTML = "Proses"
            }
        })
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
</script>
@endsection