@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
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
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            QC Penerimaan Pembelian
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">QC Penerimaan Pembelian</li>
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
                            <select name="id_cabang" class="form-control">
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch->id_cabang }}">{{ $branch->kode_cabang }} -
                                        {{ $branch->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        {{-- <span class="badge badge-default rounded-0 pull-right">
                            <input class="form-check-input" type="checkbox" id="void" name="show_void">
                            <label class="form-check-label" for="void">
                                Void
                            </label>
                        </span> --}}
                        <a href="{{ route('qc_receipt-entry') }}" class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah QC Penerimaan Pembelian
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kode Pembelian</th>
                                <th>Nama Barang</th>
                                <th>Total Qty</th>
                                <th>Satuan</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th>SG</th>
                                <th>BE</th>
                                <th>PH</th>
                                <th>Warna</th>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('qc_receipt') }}?c=" + $('[name="id_cabang"]').val(),
            columns: [{
                data: 'tanggal_qc',
                name: 'tanggal_qc'
            }, {
                data: 'id_pembelian',
                name: 'id_pembelian'
            }, {
                data: 'id_barang',
                name: 'id_barang',
            }, {
                data: 'jumlah_pembelian_detail',
                name: 'jumlah_pembelian_detail',
            }, {
                data: 'id_satuan_barang',
                name: 'id_satuan_barang',
            }, {
                data: 'status_qc',
                name: 'status_qc'
            }, {
                data: 'reason',
                name: 'reason',
            }, {
                data: '	sg_pembelian_detail',
                name: '	sg_pembelian_detail',
            }, {
                data: 'be_pembelian_detail',
                name: 'be_pembelian_detail',
            }, {
                data: 'ph_pembelian_detail',
                name: 'ph_pembelian_detail',
            }, {
                data: 'warna_pembelian_detail',
                name: 'warna_pembelian_detail',
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
    </script>
@endsection
