@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
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

        form label>span {
            color: red;
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
                            <select name="id_cabang" class="form-control select2">
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Awal</label>
                        <div class="form-group">
                            <input type="text" name="start_date" class="form-control datepicker"
                                value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Akhir</label>
                        <div class="form-group">
                            <input type="text" name="end_date" class="form-control datepicker"
                                value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('qc_receipt-entry') }}" class="btn btn-success pull-right btn-flat btn-sm mr-1">
                            <i class="glyphicon glyphicon-plus"></i> Tambah QC Penerimaan Pembelian
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered data-table display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Pembelian</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>SG</th>
                                <th>BE</th>
                                <th>PH</th>
                                <th>Warna</th>
                                <th>Bentuk</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Tanggal QC</th>
                                <th>Alasan QC</th>
                                <th>Otorisasi</th>
                                <th>Alasan Persetujuan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Perubahan Status QC Penerimaan Barang Menjadi <label
                            class="label label-success">PASSED</label></h4>
                </div>
                <form action="" class="post-action" method="post">
                    <div class="modal-body">
                        <div class="alert alert-danger" style="display:none;" id="alertModal">
                            asd
                        </div>
                        <table style="margin-bottom:10px;">
                            <tr>
                                <td width="150" style="vertical-align: top;font-weight:bold;">Kode Penerimaan</td>
                                <td width="20" style="vertical-align: top;">:</td>
                                <td id="kodePenerimaan" style="vertical-align: top;"></td>
                            </tr>
                            <tr>
                                <td width="150" style="vertical-align: top;font-weight:bold;">Nama Barang</td>
                                <td width="20" style="vertical-align: top;">:</td>
                                <td id="namaBarang" style="vertical-align: top;"></td>
                            </tr>
                            <tr>
                                <td width="150" style="vertical-align: top;font-weight:bold;">Jumlah</td>
                                <td width="20" style="vertical-align: top;">:</td>
                                <td id="jumlah" style="vertical-align: top;"></td>
                            </tr>
                        </table>
                        <label>Alasan <span>*</span></label>
                        <div class="form-group">
                            <textarea name="approval_reason" class="form-control" rows="3" placeholder="Masukkan alasan perubahan status"
                                data-validation="[NOTEMPTY]" data-validation-message="Alasan perubahan status tidak boleh kosong"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-flat">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script> --}}
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $('.select2').select2()
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
        });

        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('qc_receipt') }}?c=" + $('[name="id_cabang"]').val() + '&start_date=' + $(
                '[name="start_date"]').val() + '&end_date=' + $('[name="end_date"]').val(),
            columns: [{
                data: 'nama_pembelian',
                name: 'pembelian.nama_pembelian'
            }, {
                data: 'nama_barang',
                name: 'barang.nama_barang',
            }, {
                data: 'jumlah_pembelian_detail',
                name: 'jumlah_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'nama_satuan_barang',
                name: 'satuan_barang.nama_satuan_barang',
            }, {
                data: 'sg_pembelian_detail',
                name: 'pembelian_detail.sg_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'be_pembelian_detail',
                name: 'pembelian_detail.be_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'ph_pembelian_detail',
                name: 'pembelian_detail.ph_pembelian_detail',
                render: function(data) {
                    return data ? formatNumber(data, 4) : 0
                },
                className: 'text-right'
            }, {
                data: 'warna_pembelian_detail',
                name: 'pembelian_detail.warna_pembelian_detail',
            }, {
                data: 'bentuk_pembelian_detail',
                name: 'pembelian_detail.bentuk_pembelian_detail',
            }, {
                data: 'keterangan_pembelian_detail',
                name: 'pembelian_detail.keterangan_pembelian_detail',
            }, {
                data: 'status_qc',
                name: 'qc.status_qc',
                className: 'text-center'
            }, {
                data: 'tanggal_qc',
                name: 'qc.tanggal_qc'
            }, {
                data: 'reason',
                name: 'qc.reason',
            }, {
                data: 'nama_pengguna',
                name: 'pengguna.nama_pengguna',
            }, {
                data: 'approval_reason',
                name: 'qc.approval_reason',
            }, {
                data: 'action',
                name: 'action',
            }, ]
        });

        $('[name="id_cabang"],[name="start_date"],[name="end_date"]').change(function() {
            table.ajax.url("?c=" + $('[name="id_cabang"]').val() + '&start_date=' + $('[name="start_date"]').val() +
                '&end_date=' + $('[name="end_date"]').val()).load()
        })

        $('body').on('click', '.btn-revision', function() {
            $('#cover-spin').show()
            let id = $(this).data('id')
            $.ajax({
                url: "{{ route('qc_receipt-find-data-qc') }}?id=" + id,
                type: 'get',
                success: function(res) {
                    let modal = $('#modalEntry')
                    modal.find('form').attr('action', res.urlToChangeStatus)
                    modal.find('#kodePenerimaan').text(res.kodePenerimaan)
                    modal.find('#namaBarang').text(res.namaBarang)
                    modal.find('#jumlah').text(res.jumlah)
                    $('#cover-spin').hide()
                    $('#modalEntry').modal('show')
                },
                error: function(error) {
                    $('#cover-spin').hide()
                }
            })
        })
    </script>
@endsection
