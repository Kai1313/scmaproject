@extends('layouts.main')
@section('addedStyles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
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
        Transaksi Jurnal Closing
        <small></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Transaksi Jurnal Closing</li>
    </ol>
</section>
@endsection
@section('main-section')
<div class="content container-fluid">

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cabang</label>
                                <select name="cabang_table" id="cabang_table" class="form-control select2" style="width: 100%;">
                                    @foreach ($data_cabang as $cabang)
                                    <option value="{{ $cabang->id_cabang }}" {{ isset($data_slip->id_cabang) ? ($data_slip->id_cabang == $cabang->id_cabang ? 'selected' : '') : '' }}>
                                        {{ $cabang->kode_cabang . ' - ' . $cabang->nama_cabang }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <span class="badge badge-default rounded-0 pull-right">
                                <input class="form-check-input" type="checkbox" id="void">
                                <label class="form-check-label" for="void">
                                    Void
                                </label>
                            </span>
                            <a href="{{ route('transaction-closing-journal-create') }}" class="btn btn-sm btn-success btn-flat pull-right mr-1"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Tambah Jurnal
                                Closing</a>
                        </div>
                    </div>
                </div>
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
                    <div class="row">
                        <div class="col-md-12">
                            <table id="table_general_ledger" class="table table-bordered table-striped display responsive nowrap" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="40%">Bulan</th>
                                        <th class="text-center" width="40%">Tahun</th>
                                        <th class="text-center" width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
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
@endsection

@section('externalScripts')
<script>
    $(function() {
        $('.select2').select2();
        populate_table(0)

        $("#cabang_table").on("change", function() {
            populate_table(0)
        })
    })

    function populate_table(status) {
        $('#table_general_ledger').DataTable().destroy();
        let get_data_url = "{{ route('transaction-closing-journal-populate') }}"
        get_data_url += '?cabang=' + $("#cabang_table").val()
        $('#table_general_ledger').DataTable({
            processing: true,
            serverSide: true,
            "scrollX": true,
            "bDestroy": true,
            responsive: true,
            ajax: {
                'url': get_data_url,
                'type': 'GET',
                'dataType': 'JSON',
                'error': function(xhr, textStatus, ThrownException) {
                    alert('Error loading data. Exception: ' + ThrownException + '\n' + textStatus);
                }
            },
            columns: [{
                    data: 'month',
                    name: 'month',
                    width: '40%',
                    className: 'text-center',
                    responsivePriority: 1
                },
                {
                    data: 'year',
                    name: 'year',
                    width: '40%',
                    className: 'text-center',
                    responsivePriority: 2
                },
                {
                    data: 'id_closing',
                    width: '7%',
                    className: 'text-center',
                    render: function(data, type, row) {
                        return getActions(data, row);
                    },
                    orderable: false
                }
            ],
            responsive: {
                details: {
                    type: 'column'
                }
            },
        })
    }

    window.getActions = function(data, row) {
        let base_url = "{{ url('') }}"
        var action_btn = '<ul id="horizontal-list">';
        action_btn += '<li><button type="button" id="void-btn" data-ids="' + data + '" onclick="destroy(' +
            data +
            ')" class="btn btn-xs mr-1 mb-1 btn-danger delete-btn"><span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> Delete</button></li>';
        return action_btn;
    }

    function destroy(id) {
        let url = "{{ route('transaction-closing-journal-destroy', ':id') }}"
        url = url.replace(':id', id)
        Swal.fire({
            title: 'Anda yakin ingin delete closing ini?',
            icon: 'info',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No',
            reverseButtons: true,
            customClass: {
                actions: 'my-actions',
                confirmButton: 'order-1',
                denyButton: 'order-3',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function(data) {
                        if (data.result) {
                            Swal.fire('Data Jurnal Closing terhapus!', data.message, 'success').then((
                                result) => {
                                if (result.isConfirmed) {
                                    populate_table(0)
                                }
                            })
                        } else {
                            Swal.fire("Gagal delete data. ", data.message, 'error')
                        }

                    },
                    error: function(data) {
                        Swal.fire("Gagal delete data. ", data.message, 'error')
                    }
                });
            } else if (result.isDenied) {
                Swal.fire('Batal delete data', '', 'info')
            }
        })
    }

    function void_jurnal(id) {
        let url = "{{ route('transaction-adjustment-ledger-void', ':id') }}"
        url = url.replace(':id', id)
        Swal.fire({
            title: 'Anda yakin ingin void data ini?',
            icon: 'info',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No',
            reverseButtons: true,
            customClass: {
                actions: 'my-actions',
                confirmButton: 'order-1',
                denyButton: 'order-3',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function(data) {
                        if (data.result) {
                            Swal.fire('Data Jurnal void!', data.message, 'success').then((
                                result) => {
                                if (result.isConfirmed) {
                                    populate_table(0)
                                }
                            })
                        } else {
                            Swal.fire("Gagal void data. ", data.message, 'error')
                        }

                    },
                    error: function(data) {
                        Swal.fire("Gagal void data. ", data.message, 'error')
                    }
                });
            } else if (result.isDenied) {
                Swal.fire('Batal void data', '', 'info')
            }
        })
    }

    function active_jurnal(id) {
        let url = "{{ route('transaction-adjustment-ledger-active', ':id') }}"
        url = url.replace(':id', id)
        Swal.fire({
            title: 'Anda yakin ingin mengaktifkan data ini?',
            icon: 'info',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No',
            reverseButtons: true,
            customClass: {
                actions: 'my-actions',
                confirmButton: 'order-1',
                denyButton: 'order-3',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function(data) {
                        if (data.result) {
                            Swal.fire('Data Jurnal active!', data.message, 'success').then((
                                result) => {
                                if (result.isConfirmed) {
                                    populate_table(1)
                                }
                            })
                        } else {
                            Swal.fire("Gagal mengaktifkan data. ", data.message, 'error')
                        }

                    },
                    error: function(data) {
                        Swal.fire("Gagal mengaktifkan data. ", data.message, 'error')
                    }
                });
            } else if (result.isDenied) {
                Swal.fire('Batal mengaktifkan data', '', 'info')
            }
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