@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <style>
        .mb-1 {
            margin-bottom: .25rem !important;
        }

        .list-head {
            font-weight: bold;
            width: 140px;
            color: black !important;
            vertical-align: top;
            padding-bottom: 10px;
        }

        .list-sparator {
            width: 10px;
            vertical-align: top;
        }

        .list-value {
            padding-bottom: 10px;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Kunjungan
            <small>| Lihat</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('visit') }}">Kunjungan</a></li>
            <li class="active">Lihat</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Lihat Kunjungan</h3>
                <a href="{{ route('visit') }}" class="btn bg-navy btn-sm btn-default pull-right">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <table>
                            <tr>
                                <td class="list-head">Cabang</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->cabang->nama_cabang }}</td>
                            </tr>
                            <tr>
                                <td class="list-head">Tanggal Kunjungan</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->visit_date }}</td>
                            </tr>
                            <tr>
                                <td class="list-head">Kode Kunjungan</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->visit_code }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <table>
                            <tr>
                                <td class="list-head">Salesman</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->salesman->nama_salesman }}</td>
                            </tr>
                            <tr>
                                <td class="list-head">Catatan</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->pre_visit_desc }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <table>
                            <tr>
                                <td class="list-head">Pelanggan</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->id_pelanggan }}{{ $data->pelanggan->nama_pelanggan }}
                                </td>
                            </tr>
                            <tr>
                                <td class="list-head">Alamat</td>
                                <td class="list-sparator">:</td>
                                <td class="list-value">{{ $data->pelanggan->alamat_pelanggan }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="pull-right">
            <a href="{{ route('visit-report-entry', $data->id) }}" class="btn btn-info">
                <i class="fa fa-pencil mr-1"></i> Update Hasil Kunjungan
            </a>
            <a href="{{ route('visit-entry', $data->id) }}" class="btn btn-warning">
                <i class="fa fa-pencil mr-1"></i> Edit Jadwal
            </a>
            <a href="{{ env('OLD_URL_ROOT') }}" class="btn btn-default show-customer-edit-visit">
                <i class="fa fa-group mr-1"></i> Perbarui Data pelanggan
            </a>
            <a href="{{ route('cancel-visit', $data->id) }}" class="btn btn-danger show-cancel-visit">
                <i class="fa fa-close mr-1"></i> Batal Kunjungan
            </a>
        </div>
    </div>

    <div id="modal-cancel-visit" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="" method="post" class="post-action">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Batalkan Kunjungan</h4>
                    </div>
                    <div class="modal-body">
                        <label for="">Alasan Pembatalan</label>
                        <div class="form-group">
                            <textarea name="alasan_pembatalan" class="form-control" data-validation="[NOTEMPTY]"
                                data-validation-message="Alasan pembatalan tidak boleh kosong"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-customer-edit-visit" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="" method="post" class="post-action">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Pelanggan</h4>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/plugins/jquery-form-validation-1.5.3/dist/jquery.validation.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        $('.select2').select2()

        $('[name="id_pelanggan"]').select2({
            ajax: {
                url: "{{ route('visit-customer') }}",
                data: function(params) {
                    return {
                        search: params.term,
                    }
                },
                processResults: function(data) {
                    return {
                        results: [{
                            'id': '',
                            'text': 'Pilih Pelanggan'
                        }, ...data]
                    };
                }
            }
        })

        $('.show-cancel-visit').click(function(e) {
            e.preventDefault()
            let url = $(this).prop('href')
            let modal = $('#modal-cancel-visit')
            modal.find('form').attr('action', url)
            modal.modal()
        })

        $('.show-customer-edit-visit').click(function(e) {
            e.preventDefault()
            let url = $(this).prop('href')
            let modal = $('#modal-customer-edit-visit')
            modal.find('form').attr('action', url)
            modal.modal()
        })
    </script>
@endsection
