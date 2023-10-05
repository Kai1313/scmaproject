@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <style>
        th {
            text-align: center;
        }

        ul.horizontal-list {
            min-width: 0px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        ul.horizontal-list li {
            display: inline;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Permintaan Pembelian
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('purchase-request') }}">Permintaan Pembelian</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Permintaan Pembelian <span class="text-muted"></span></h3>
                <a href="{{ route('purchase-request-print-data', $data->purchase_request_id) }}" target="_blank"
                    class="btn btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-print mr-1"></span> Cetak
                </a>
                <a href="{{ route('purchase-request') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"
                    style="margin-right:10px;">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Cabang</label>
                            <div class="col-md-8">
                                : {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Kode Permintaan</label>
                            <div class="col-md-8">
                                : {{ $data->purchase_request_code }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">
                                : {{ $data->purchase_request_date }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Estimasi</label>
                            <div class="col-md-8">
                                : {{ $data->purchase_request_estimation_date }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Status</label>
                            <div class="col-md-8">
                                : <label class="{{ $status[$data->approval_status]['class'] }}">
                                    {{ $status[$data->approval_status]['text'] }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Gudang</label>
                            <div class="col-md-8">
                                : {{ $data->gudang->nama_gudang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Pemohon</label>
                            <div class="col-md-8">
                                : {{ $data->pengguna->nama_pengguna }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Catatan</label>
                            <div class="col-md-8">
                                : {{ $data->catatan }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detil Permintaan Barang</h3>
                @if (in_array(session()->get('user')['id_grup_pengguna'], $arrayAccess) && $data->approval_status == 0)
                    <a href="{{ route('purchase-request-change-status', [$data->purchase_request_id, 'reject']) }}"
                        class="btn btn-default btn-change-status btn-flat btn-sm pull-right" data-param="reject">
                        <i class="fa fa-times"></i> Tolak Semua
                    </a>
                    <a href="{{ route('purchase-request-change-status', [$data->purchase_request_id, 'approval']) }}"
                        class="btn btn-success btn-change-status btn-flat btn-sm pull-right" data-param="approval"
                        style="margin-right:10px;">
                        <i class="glyphicon glyphicon-check"></i> Setujui Semua
                    </a>
                @endif
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="table-detail" class="table table-bordered data-table display responsive nowrap"
                        width="100%">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Catatan</th>
                                <th>Stok</th>
                                <th>Catatan Persetujuan</th>
                                <th>Persetujuan</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalChangeStatus" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content ">
                <div class="modal-body">
                    <h3><span id="item_name"></span> di<span id="type_item"></span></h3>
                    <label>Ubah Qty</label>
                    <div class="form-group">
                        <input type="text" name="revised_qty" class="form-control handle-number-4 text-right">
                    </div>
                    <label>Alasan</label>
                    <div class="form-group">
                        <textarea name="approval_notes" class="form-control clear-input" rows="4"
                            placeholder="Harus di isi jika status ditolak"></textarea>
                    </div>
                    <input type="hidden" name="approval_status" class="clear-input">
                    <input type="hidden" name="index" class="clear-input">
                    <input type="hidden" name="purchase_request_id" value="{{ $data->purchase_request_id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary save-change-status-detail btn-flat">Proses</button>
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
        console.log(details)
        let approval_header = {{ $data->approval_status }};
        let arrayAccess = {!! json_encode($arrayAccess) !!}
        let idUser = '{{ $idUser }}'
        let changeStatusDetail = '{{ route('purchase-request-change-status-detail') }}';
        var resDataTable = $('#table-detail').DataTable({
            data: details,
            ordering: false,
            columns: [{
                data: 'kode_barang',
                name: 'kode_barang'
            }, {
                data: 'nama_barang',
                name: 'nama_barang'
            }, {
                data: 'qty',
                name: 'qty',
                render: function(data) {
                    return formatNumber(data, 4)
                },
                className: 'text-right'
            }, {
                data: 'nama_satuan_barang',
                name: 'nama_satuan_barang'
            }, {
                data: 'notes',
                name: 'notes'
            }, {
                data: 'stok',
                name: 'stok',
                render: function(data) {
                    return formatNumber(data, 4)
                },
                className: 'text-right'
            }, {
                data: 'approval_notes',
                name: 'approval_notes'
            }, {
                data: 'approval_status',
                name: 'approval_status',
                render: function(data, type, row, meta) {
                    let status = '';
                    if (data == '1') {
                        status = 'Setuju';
                    } else if (data == '2') {
                        status = 'Tolak'
                    }

                    return status;
                }
            }, {
                data: 'status_data',
                name: 'status_data',
                className: 'text-center'
            }, {
                data: 'index',
                className: 'text-center',
                name: 'index',
                searchable: false,
                render: function(data, type, row, meta) {
                    let btn = '<ul class="horizontal-list">';
                    if (approval_header == 0 && (row.approval_status == 0 || row.approval_status ==
                            null)) {
                        if (arrayAccess.includes(idUser)) {
                            btn +=
                                '<li><a href="' + changeStatusDetail +
                                '" class="btn btn-success btn-xs mr-1 mb-1 btn-change-status-modal" data-item="' +
                                row.nama_barang +
                                '" data-type="1" data-index="' + data +
                                '"><i class="glyphicon glyphicon-check"></i> Setuju</a></li>';
                            btn +=
                                '<li><a href="' + changeStatusDetail +
                                '" class="btn btn-default btn-xs mr-1 mb-1 btn-change-status-modal" data-item="' +
                                row.nama_barang +
                                '" data-type="2" data-index="' + data +
                                '"><i class="fa fa-times"></i> Tolak</a></a></li>';
                        }
                    }
                    console.log(row)
                    if (row.approval_status == 1 && row.closed != 1) {
                        btn +=
                            '<li><a href="' + changeStatusDetail +
                            '" class="btn btn-default btn-xs mr-1 mb-1 btn-change-status-modal" data-item="' +
                            row.nama_barang +
                            '" data-type="2" data-index="' + data +
                            '"><i class="fa fa-times"></i> Tolak</a></a></li>';
                    }

                    btn += '</ul>';
                    return btn;
                }
            }]
        });

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

        $('body').on('click', '.btn-change-status-modal', function(e) {
            e.preventDefault()
            let url = $(this).prop('href')
            let type = $(this).data('type')
            let tempData = details[$(this).data('index') - 1]
            $('#item_name').text(tempData.nama_barang)
            $('#type_item').text(type == 1 ? 'terima' : 'tolak')
            $('[name="index"]').val($(this).data('index'))
            $('[name="approval_status"]').val(type)
            $('[name="revised_qty"]').val(formatRupiah(tempData.qty))
            $('.save-change-status-detail').attr('data-url', url)

            $('#modalChangeStatus').modal()
        })

        $('.save-change-status-detail').click(function() {
            if ($('[name="revised_qty"]').val() == '' || $('[name="revised_qty  "]').val() == '0') {
                Swal.fire("Gagal", "Qty tidak boleh kosong", 'error')
                return false;
            }

            if ($('[name="approval_status"]').val() == 2) {
                if ($('[name="approval_notes"]').val().trim() == '') {
                    Swal.fire("Gagal", "Alasan tidak boleh kosong", 'error')
                    return false;
                }
            }

            let url = $(this).data('url')
            $('#cover-spin').show()
            $.ajax({
                url: url,
                type: 'post',
                data: {
                    'index': $('[name="index"]').val(),
                    'purchase_request_id': $('[name="purchase_request_id"]').val(),
                    'approval_notes': $('[name="approval_notes"]').val().trim(),
                    'approval_status': $('[name="approval_status"]').val(),
                    'qty': normalizeNumber($('[name="revised_qty"]').val())
                },
                success: function(data) {
                    $('#cover-spin').hide()
                    Swal.fire('Tersimpan!', data.message, 'success').then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = data.redirect;
                        }
                    })
                },
                error: function(error) {
                    $('#cover-spin').hide()
                    Swal.fire("Gagal", error.responseJSON.message, 'error')
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
    </script>
@endsection
