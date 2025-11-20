@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    {{-- <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('css/fancybox.css') }}" /> --}}
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

        .handle-number-4 {
            text-align: right;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Permintaan Pengiriman
            <small>| Lihat</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('delivery_request') }}">Permintaan Pengiriman</a></li>
            <li class="active">Form</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Lihat Permintaan Pengiriman</h3>
                <div class="pull-right">
                    @if (
                        $data &&
                            $data->approval_status == '1' &&
                            count($data->details) > 0 &&
                            $data->created_by != session()->get('user')['id_pengguna']
                    )
                        <a href="javascript:void(0)" class="btn btn-sm btn-default btn-flat " id="btn-approve">
                            <span class="glyphicon glyphicon-ok mr-1" aria-hidden="true"></span> Setujui Semua
                        </a>
                    @endif
                    <a href="{{ route('delivery_request') }}" class="btn bg-navy btn-sm btn-success btn-flat">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>

            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="row">
                            <label class="col-md-3">Kode Transaksi</label>
                            <div class="col-md-9">
                                : {{ $data ? $data->delivery_request_code : '' }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Cabang Peminta</label>
                            <div class="col-md-9">
                                : {{ $data ? $data->branch->nama_cabang : '' }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Cabang Tujuan</label>
                            <div class="col-md-9">
                                : {{ $data ? $data->destinationBranch->nama_cabang : '' }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3">Status</label>
                            <div class="col-md-9">
                                : {!! $data ? $statusOptions[$data->status]['label'] : '' !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <label class="col-md-4">Tanggal Permintaan</label>
                            <div class="col-md-8">
                                : {{ $data ? date('d/m/Y', strtotime($data->date)) : '' }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Estimasi Kedatangan</label>
                            <div class="col-md-8">
                                : {{ $data ? date('d/m/Y', strtotime($data->estimated_delivery_date)) : '' }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Persetujuan</label>
                            <div class="col-md-8">
                                :
                                {!! $data ? $approvalStatusOptions[$data->approval_status]['label'] ?? '' : '' !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <label class="col-md-4">Dibuat Oleh</label>
                            <div class="col-md-8">
                                : {{ $data && $data->createdBy ? $data->createdBy->nama_pengguna : '' }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Disetujui Oleh</label>
                            <div class="col-md-8">
                                : {{ $data && $data->approvedBy ? $data->approvedBy->nama_pengguna : '' }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Keterangan</label>
                            <div class="col-md-8">
                                : {{ $data ? $data->desc : '' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Detil Barang</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="table-detail" class="table table-bordered data-table display table-detail" width="100%">
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let statusOptions = {!! json_encode($statusOptions) !!};
        let approvalStatusOptions = {!! json_encode($approvalStatusOptions) !!};
        let statusDetailOptions = {!! json_encode($statusDetailOptions) !!};

        var resDataTable = $('#table-detail').DataTable({
            destroy: true,
            scrollX: true,
            paging: false,
            data: details,
            ordering: false,
            searching: false,
            info: false,
            language: {
                emptyTable: "Tidak ada data yang tersedia"
            },
            columns: [{
                data: 'nama_barang',
                name: 'nama_barang',
                title: 'Nama Barang'
            }, {
                data: 'qty',
                name: 'qty',
                title: 'Total Permintaan',
                render: function(data) {
                    return formatNumber(data, 4)
                },
                className: 'text-right'
            }, {
                data: 'delivery_qty',
                name: 'delivery_qty',
                title: 'Total Terkirim',
                render: function(data) {
                    return formatNumber(data, 4)
                },
                className: 'text-right'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang',
                title: 'Satuan'
            }, {
                data: 'desc',
                name: 'desc',
                title: 'Keterangan'
            }, {
                data: 'approval_status',
                name: 'approval_status',
                title: 'Persetujuan',
                className: 'text-center',
                render: function(data, type, row) {
                    return approvalStatusOptions[data] ? approvalStatusOptions[data]['label'] : '-';
                }
            }, {
                data: 'status',
                name: 'status',
                title: 'Status',
                className: 'text-center',
                render: function(data, type, row) {
                    return statusDetailOptions[data] ? statusDetailOptions[data]['label'] : '-';
                }
            }, {
                data: null,
                title: 'Action',
                className: 'text-center',
                searchable: false,
                orderable: false,
                width: '150px',
                render: function(data, type, row, meta) {
                    let btn = ''
                    if (row.approval_status == '1' && {{ $data->created_by }} !=
                        {{ session()->get('user')['id_pengguna'] }}) {
                        btn +=
                            '<a href="javascript:void(0)" class="btn btn-success btn-xs mr-1 mb-1 approval" data-status="2"><i class="glyphicon glyphicon-ok"></i> Setuju</a>';
                        btn +=
                            '<a href="javascript:void(0)" class="btn btn-danger btn-xs mr-1 mb-1 approval" data-status="0"><i class="glyphicon glyphicon-remove"></i> Tolak</a>';
                    }

                    return btn;
                }
            }]
        });

        $('body').on('click', '.approval', function() {
            let status = $(this).data('status');
            let rowData = resDataTable.row($(this).parents('tr')).data();
            let detailId = rowData.id;
            let statusText = status == 2 ? 'setuju' : 'tolak';
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Anda akan " + statusText + " permintaan ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, ' + statusText + '!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('delivery-request-approval', $data->id) }}",
                        type: "POST",
                        data: {
                            approval_status: status,
                            detail_id: detailId,
                            approved_qty: 0
                        },
                        success: function(response) {
                            Swal.fire(
                                'Berhasil!',
                                'Persetujuan telah disimpan.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat menyimpan persetujuan.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        $('body').on('click', '#btn-approve', function() {
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Anda akan menyetujui semua permintaan ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, setujui semua!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('delivery-request-approval', $data->id) }}",
                        type: "POST",
                        data: {
                            approval_status: 2,
                            detail_id: null,
                        },
                        success: function(response) {
                            Swal.fire(
                                'Berhasil!',
                                'Semua persetujuan telah disimpan.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat menyimpan persetujuan.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    </script>
@endsection
