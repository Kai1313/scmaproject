@extends('layouts.main')
@section('addedStyles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
<!-- bootstrap datepicker -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .dataTables_scrollHeadInner {
        width: 100% !important;
    }

    .table {
        width: 100% !important;
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

    .mb-1 {
        margin-bottom: .25rem !important;
    }

    .mr-1 {
        margin-right: 1rem !important;
    }

    .rounded-0 {
        border-radius: 0;
    }
</style>
@endsection
@section('header')
<section class="content-header">
    <h1>
        Report
        <small> | Slip</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Report Slip</li>
    </ol>
</section>
@endsection
@section('main-section')
<div class="content container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <form id="form_report" action="" method="post">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-12">
                                <h3 class="box-title">Report Slip</h3>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group" id="cabang-group">
                                    <label>Cabang</label>
                                    <select name="cabang" id="cabang" class="form-control select2" style="width: 100%;" data-validation="[NOTEMPTY]" data-validation-message="Cabang tidak boleh kosong">
                                        @foreach ($data_cabang as $cabang)
                                        <option value="{{ $cabang->id_cabang }}">
                                            {{ $cabang->kode_cabang . ' - ' . $cabang->nama_cabang }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" id="slip-group">
                                    <label>Slip</label>
                                    <select name="slip" id="slip" class="form-control select2" style="width: 100%;" data-validation="[NOTEMPTY]" data-validation-message="Slip tidak boleh kosong">
                                        @foreach ($data_slip as $slip)
                                        <option value="{{ $slip->id_slip }}">
                                            {{ $slip->kode_slip . ' - ' . $slip->nama_slip }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" id="start-group">
                                    <label>Awal Period</label>
                                    <input type="text" name="start_date" id="start_date" class="form-control datepicker" style="width: 100%;" data-validation="[NOTEMPTY]" data-validation-message="Awal Period tidak boleh kosong" value="{{date('Y-m-d')}}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" id="end-group">
                                    <label>Akhir Period</label>
                                    <input type="text" name="end_date" id="end_date" class="form-control datepicker" style="width: 100%;" data-validation="[NOTEMPTY]" data-validation-message="Akhir Period tidak boleh kosong" value="{{date('Y-m-d')}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" name="view" value="View" id="btn-view-report" class="btn btn-sm btn-default pull-right mr-1"><i class="fa fa-eye"></i> View</button>
                                <button type="button" name="excel" value="Excel" id="btn-excel-report" class="btn btn-sm btn-success pull-right mr-1"><i class="fa fa-file-excel-o"></i> Excel</button>
                                <button type="button" name="pdf" value="Pdf" id="btn-pdf-report" class="btn btn-sm btn-danger pull-right mr-1"><i class="fa fa-print"></i> Print</button>
                            </div>
                        </div>
                    </div>
                </form>
                @if (session('failed'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('failed') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                <div class="box-body">
                    <table id="table_report" style="display:none" class="table table-bordered table-striped display responsive nowrap" width="100%">
                        <thead width="100%">
                            <tr>
                                <th class="text-center" width="7%" data-priority="1">Tanggal Jurnal</th>
                                <th class="text-center" width="10%" data-priority="2">No Jurnal</th>
                                <th class="text-center" width="11%">Slip</th>
                                <th class="text-center" width="10%">Akun</th>
                                <th class="text-center" width="15%">Keterangan</th>
                                <th class="text-center" width="10%">ID Transaksi</th>
                                <th class="text-center" width="9%">Debet</th>
                                <th class="text-center" width="9%">Credit</th>
                                <th class="text-center" width="9%">Balance</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('addedScripts')
<!-- Select2 -->
<script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
<!-- DataTables -->
<script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
<!-- SlimScroll -->
<script src="{{ asset('assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ asset('assets/bower_components/fastclick/lib/fastclick.js') }}"></script>
<!-- SWAL -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
<!-- Validator -->
<script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
<!-- bootstrap datepicker -->
<script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
@endsection

@section('externalScripts')
<script>
    let excel_route = "{{ route('report-slip-excel') }}"

    $(function() {
        $('.select2').select2();

        $(".datepicker").datepicker({
            format: "yyyy-mm-dd"
        })

        $('#btn-view-report').on('click', function() {
            console.log('view');
            let form = validateFormValue();

            if (form.status) {
                populate_table(form.data)
            }
        });

        $('#btn-excel-report').on('click', function() {
            console.log('excel');
            let form = validateFormValue()

            if (form.status) {
                let base_url = "{{ url('') }}";

                window.open(base_url + '/report/slip/excel?cabang=' + form.data.cabang + '&slip=' + form.data.slip + '&start_date=' + form.data.start_date + '&end_date=' + form.data.end_date);
            }
        });

        $('#btn-pdf-report').on('click', function() {
            console.log('pdf');
            let form = validateFormValue()

            if (form.status) {
                let button = document.getElementById("btn-pdf-report");
                button.disabled = true;
                button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
                let base_url = "{{ url('') }}";

                let route = base_url + '/report/slip/pdf?cabang=' + form.data.cabang + '&slip=' + form.data.slip + '&start_date=' + form.data.start_date + '&end_date=' + form.data.end_date;

                $.ajax({
                    type: "GET",
                    url: route
                }).done(function(data) {
                    console.log(data)
                    if (data.result) {
                        // Create a new anchor element
                        var link = document.createElement('a');
                        // Set the PDF data as href attribute
                        link.href = 'data:application/pdf;base64,' + data.pdfData;
                        // Set the PDF headers as download attribute
                        link.setAttribute('download', 'ReportSlips.pdf');
                        link.setAttribute('target', '_blank');
                        // Append the anchor element to the document
                        document.body.appendChild(link);
                        // Trigger a click on the anchor element to download the PDF
                        link.click();
                        // Remove the anchor element from the document
                        document.body.removeChild(link);
                    } else {
                        Swal.fire("Gagal membuat report. ", data.message, 'error')
                    }
                    button.disabled = false;
                    button.innerHTML = '<i class="fa fa-print"></i> Print'
                })
            }
        });

        $('#cabang').on('change', function() {
            let base_url = "{{ url('') }}";

            let route = base_url + '/report/slip/getSlip?cabang=' + this.value;

            $.ajax({
                type: "GET",
                url: route
            }).done(function(data) {
                if (data.result) {
                    $('#slip').empty();

                    let option = ''
                    data.data.forEach(value => {
                        option += '<option value="' + value.id_slip + '">' + value.kode_slip + ' - ' + value.nama_slip + '</option>'
                    });

                    $('#slip').append(option);
                } else {
                    Swal.fire("Gagal get data slip. ", data.message, 'error')
                }
            })
        });
    })

    function validateFormValue() {
        let cabang = $('#cabang').val();
        let slip = $('#slip').val();
        let start_date = $('#start_date').val();
        let end_date = $('#end_date').val();

        let error = 0;

        let data = {
            cabang: cabang,
            slip: slip,
            start_date: start_date,
            end_date: end_date
        }

        let alert = '<div class="form-control-error" id="replace-id" data-error-list><ul><li>ReplaceHere tidak boleh kosong</li></ul></div>'

        if (cabang == '') {
            $('#cabang-group').addClass('has-error');
            let alertfix = alert.replace('ReplaceHere', 'Cabang')
            $('#cabang').after(alertfix);

            error += 1;
        }

        if (slip == '') {
            $('#slip-group').addClass('has-error');
            let alertfix = alert.replace('ReplaceHere', 'Slip')
            $('#slip').after(alertfix);

            error += 1;
        }

        if (start_date == '') {
            $('#start-group').addClass('has-error');
            let alertfix = alert.replace('ReplaceHere', 'Start Date')
            $('#start_date').after(alertfix);

            error += 1;
        }

        if (end_date == '') {
            $('#end-group').addClass('has-error');
            let alertfix = alert.replace('ReplaceHere', 'End Date')
            $('#end_date').after(alertfix);

            error += 1;
        }

        if (error > 0) {
            return {
                status: false,
                data: data
            }
        } else {
            $('#cabang-group').removeClass('has-error');
            $('#slip-group').removeClass('has-error');
            $('#start-group').removeClass('has-error');
            $('#end-group').removeClass('has-error');
            $('.form-control-error').remove();

            return {
                status: true,
                data: data
            }
        }

    }

    function populate_table(data) {
        let get_data_url = "{{ route('report-slip-populate') }}"
        get_data_url += '?cabang=' + data.cabang + '&slip=' + data.slip + '&start_date=' + data.start_date + '&end_date=' + data.end_date

        $('#table_report').css('display', '')

        $('#table_report').DataTable().destroy();
        $('#table_report').DataTable({
            processing: true,
            serverSide: true,
            "scrollX": true,
            "bDestroy": true,
            responsive: true,
            ajax: {
                "url": get_data_url,
                "type": "GET",
                "dataType": "JSON",
                "error": function(xhr, textStatus, ThrownException) {
                    alert("Error loading data. Exception: " + ThrownException + '\n' + textStatus)
                }
            },
            columns: [{
                    data: 'tanggal_jurnal',
                    name: 'tanggal_jurnal',
                    className: 'text-center',
                    width: '7%'
                },
                {
                    data: 'kode_jurnal',
                    name: 'kode_jurnal',
                    className: 'text-left',
                    width: '10%'
                },
                {
                    data: 'nama_slip',
                    name: 'nama_slip',
                    className: 'text-left',
                    width: '11%'
                },
                {
                    data: 'nama_akun',
                    name: 'nama_akun',
                    className: 'text-left',
                    width: '10%'
                },
                {
                    data: 'keterangan',
                    name: 'keterangan',
                    className: 'text-left',
                    width: '15%',
                    render: function(data, type, row) {
                        return data != null ? data.replace(/\n/g, '<br>') : data
                    }
                },
                {
                    data: 'id_transaksi',
                    name: 'id_transaksi',
                    className: 'text-left',
                    width: '10%'
                },
                {
                    data: 'debet',
                    name: 'debet',
                    width: '9%',
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
                    width: '9%',
                    searchable: false,
                    orderable: false,
                    className: 'text-right',
                    render: function(data, type, row) {
                        return formatCurr(formatNumberAsFloatFromDB(data))
                    }
                },
                {
                    data: 'balance',
                    name: 'balance',
                    width: '9%',
                    searchable: false,
                    orderable: false,
                    className: 'text-right',
                    render: function(data, type, row) {
                        return formatCurr(formatNumberAsFloatFromDB(data))
                    }
                }
            ],
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

    function formatNumberAsFloat(num) {
        num = String(num);
        num = num.split('.').join("");

        return num;
    }

    function formatNumberAsFloatFromDB(num) {
        num = String(num);
        num = num.replace('.', ',');

        return num;
    }
</script>
@endsection