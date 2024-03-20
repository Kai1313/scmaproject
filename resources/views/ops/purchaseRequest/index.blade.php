@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        ul.horizontal-list {
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        ul.horizontal-list li {
            display: inline;
        }

        .mb-1 {
            margin-bottom: .25rem !important;
        }

        th {
            text-align: center;
        }

        .head-checkbox {
            padding-top: 30px;
        }

        .head-checkbox label {
            margin-right: 10px;
        }

        .rounded-0 {
            border-radius: 0;
        }

        .text-wrap {
            white-space: normal;
        }

        .width-200 {
            width: 400px;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Permintaan Pembelian
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Permintaan Pembelian</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-4">
                        <label>Cabang</label>
                        <div class="form-group">
                            <select name="id_cabang" class="form-control select2 change-filter">
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <span class="badge badge-default rounded-0 pull-right">
                            <input class="form-check-input" type="checkbox" id="void" name="show_void">
                            <label class="form-check-label" for="void">
                                Void
                            </label>
                        </span>
                        <a href="{{ route('purchase-request-entry') }}"
                            class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah Permintaan Pembelian
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>ID Permintaan</th>
                                <th>Tanggal</th>
                                <th>Estimasi</th>
                                <th>Gudang</th>
                                <th>Pemohon</th>
                                <th>Status</th>
                                <th>Otorisasi</th>
                                <th>Tanggal Otorisasi</th>
                                <th>Jumlah Terpakai</th>
                                <th>Catatan</th>
                                <th width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var defaultFilter = sessionStorage.getItem('purchase_request_filter') ? JSON.parse(sessionStorage.getItem(
            'purchase_request_filter')) : {};
        for (const key in defaultFilter) {
            $('[name="' + key + '"]').val(defaultFilter[key])
        }

        $('.select2').select2()
        var table = $('.data-table').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: "{{ route('purchase-request') }}?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $(
                '[name="show_void"]').is(':checked'),
            columnDefs: [{
                render: function(data, type, full, meta) {
                    return "<div class='text-wrap width-200'>" + data + "</div>";
                },
                targets: 9
            }],
            columns: [{
                data: 'purchase_request_code',
                name: 'prh.purchase_request_code'
            }, {
                data: 'purchase_request_date',
                name: 'prh.purchase_request_date'
            }, {
                data: 'purchase_request_estimation_date',
                name: 'prh.purchase_request_estimation_date',
            }, {
                data: 'nama_gudang',
                name: 'gudang.nama_gudang',
            }, {
                data: 'user',
                name: 'user.nama_pengguna',
            }, {
                data: 'approval_status',
                name: 'prh.approval_status',
                className: 'text-center'
            }, {
                data: 'approval_user',
                name: 'approval.nama_pengguna',
            }, {
                data: 'approval_date',
                name: 'prh.approval_date',
            }, {
                data: 'closed',
                name: 'closed'
            }, {
                data: 'catatan',
                name: 'prh.catatan',
            }, {
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false
            }, ]
        });

        $('[name="id_cabang"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $('[name="show_void"]').is(
                ':checked')).load()
            changeFilter()
        })

        $('[name="show_void"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&show_void=' + $('[name="show_void"]').is(
                ':checked')).load()
        })

        $('body').on('click', '.btn-change-status', function(e) {
            let self = $(this)
            e.preventDefault();
            Swal.fire({
                title: 'Anda yakin ingin ' + self.data('param') + ' data ini?',
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
                $('#cover-spin').hide()
                if (result.isConfirmed) {
                    changeData(self.prop('href'))
                }
            })
        })

        function changeData(url) {
            $('#cover-spin').show()
            $.ajax({
                url: url,
                type: "get",
                dataType: "JSON",
                success: function(data) {
                    $('#cover-spin').hide()
                    if (data.result) {
                        Swal.fire('Berhasil', data.message, 'success').then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = data.redirect;
                            }
                        })
                    } else {
                        Swal.fire("Gagal", data.message, 'error')
                    }
                },
                error: function(data) {
                    $('#cover-spin').hide()
                    Swal.fire("Gagal", data.responseJSON.message, 'error')
                }
            })
        }

        function changeFilter() {
            $('.change-filter').each(function(i, v) {
                defaultFilter[$(v).prop('name')] = $(v).val()
            })

            sessionStorage.setItem('purchase_request_filter', JSON.stringify(defaultFilter));
        }
    </script>
@endsection
