@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="{{ asset('css/fancybox.css') }}" />
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

        .select2 {
            width: 100% !important;
        }

        #recap-data>tr>td {
            border-bottom: 1px solid #777;
            padding: 5px;
        }

        .with-wrap {
            width: 200px;
            white-space: pre-wrap;
        }

        .no-wrap {
            white-space: nowrap;
        }

        p {
            margin: 0px !important;
        }

        ol {
            margin-bottom: 0px !important;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Laporan Kunjungan
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Kunjungan</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-2 filter-div">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="text" class="form-control trigger-change" name="date" />
                        </div>
                    </div>
                    {{-- @if ($groupUser != 6) --}}
                    <div class="col-md-2 filter-div">
                        <label>Sales</label>
                        <div class="form-group">
                            <select class="form-control select2 trigger-change" name="id_salesman">
                                <option value="">Semua Sales</option>
                                @foreach ($salesmans as $sales)
                                    <option value="{{ $sales->id }}">{{ $sales->text }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Pelanggan</label>
                        <div class="form-group">
                            <select class="form-control select2 trigger-change" name="id_pelanggan">
                                <option value="">Semua Pelanggan</option>

                            </select>
                        </div>
                    </div>
                    {{-- @else
                        <input type="hidden" name="id_salesman" value="{{ $idUser }}" class="trigger-change">
                    @endif --}}
                    <div class="col-md-2 filter-div">
                        <label>Jenis Laporan</label>
                        <div class="form-group">
                            <select class="form-control select2 trigger-change" name="report_type">
                                <option value="rekap">Rekap</option>
                                <option value="detail">Detail</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div" style="padding-top:27px;">
                        <a href="{{ route('visit_report_excel') }}" class="btn btn-success btn-action btn-sm btn-flat"
                            style="margin-bottom:5px;">
                            <i class="fa fa-file-excel-o"></i> Excel
                        </a>
                        <a href="javascript:void(0)" class="btn btn-default btn-action btn-view-action btn-sm btn-flat"
                            style="margin-bottom:5px;">
                            <i class="glyphicon glyphicon-eye-open"></i> View
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body target-recap" style="display:none;">
                @foreach ($initialActivities as $key => $init)
                    <span style="margin-right:10px;"><b>{{ $init }}</b> = {{ $activities[$key] }}</span>
                @endforeach
                <div class="table-responsive">
                    <table id="header-fixed-main-data" class="table table-bordered"></table>
                    <table class="table table-bordered" id="main-data">
                        <thead>
                            <tr>
                                <th rowspan="2">Sales</th>
                                <th rowspan="2">Date</th>
                                <th rowspan="2">Customer</th>
                                <th rowspan="2">Category</th>
                                <th colspan="{{ count($activities) }}">Activity</th>
                                <th rowspan="2" style="min-width:400px;max-width:800px;">Description</th>
                            </tr>
                            <tr>
                                @foreach ($initialActivities as $activity)
                                    <th width="40">{{ $activity }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="box-body target-detail" style="display:none;">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered data-table display" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:80px;">Tanggal</th>
                                    <th style="width:80px;">Sales</th>
                                    <th>Pelanggan</th>
                                    <th style="width:200px">Hasil Kunjungan</th>
                                    <th style="width:200px">Masalah</th>
                                    <th style="width:200px">Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row target-recap" style="display:none;">
            <div class="col-md-5">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Rekap</h3>
                    </div>
                    <div class="box-body">
                        <table id="recap-data"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-customer" class="modal fade" role="dialog">
        <div class="modal-dialog">
            {{-- <form action="" method="post" class="post-action"> --}}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Data Pelanggan</h4>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tr>
                            <td style="width:120px;">Pelanggan</td>
                            <td style="width:10px;">:</td>
                            <td id="target_nama_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td id="target_alamat_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Bidang Usaha</td>
                            <td>:</td>
                            <td id="target_bidang_usaha_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Kota</td>
                            <td>:</td>
                            <td id="target_kota_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Telepon</td>
                            <td>:</td>
                            <td id="target_telepon1_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Kapasitas Pelanggan</td>
                            <td>:</td>
                            <td id="target_kapasitas_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Orang yang dihubungi</td>
                            <td>:</td>
                            <td id="target_kontak_person_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Posisi orang yang dihubungi</td>
                            <td>:</td>
                            <td id="target_posisi_kontak_person_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Aset</td>
                            <td>:</td>
                            <td id="target_aset_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>:</td>
                            <td id="target_status_aktif_pelanggan"></td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td>:</td>
                            <td id="target_ketarangan_pelanggan"></td>
                        </tr>
                    </table>
                    <div id="target-maps">

                    </div>
                </div>
                <div class="modal-footer">
                    {{-- <button type="submit" class="btn btn-primary btn-sm btn-flat">Simpan</button> --}}
                    <button type="button" class="btn btn-default btn-sm btn-flat" data-dismiss="modal">Tutup</button>
                </div>
            </div>
            {{-- </form> --}}
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
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ asset('js/fancybox.min.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var salesman = {!! json_encode($salesmans) !!}
        var activities = {!! json_encode($activities) !!}
        let defaultUrlIndex = '{{ route('visit_report') }}'
        $('#daterangepicker').daterangepicker({
            timePicker: false,
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        function loadDatatable() {
            if ($('[name="report_type"]').val() == 'rekap') {
                $('.target-recap').show()
                $('.target-detail').hide()
                $('#cover-spin').show()
                $('#main-data').find('tbody').html('')
                $('#recap-data').html('')
                $.ajax({
                    url: defaultUrlIndex + param,
                    success: function(res) {
                        if (res.result) {
                            $('#main-data').find('tbody').html(res.htmlMainData)
                            $('#recap-data').html(res.htmlRecapData)
                            Fancybox.bind('[data-fancybox="gallery"]');
                        }

                        $('#cover-spin').hide()
                    },
                    error: function(error) {
                        $('#cover-spin').hide()
                        Swal.fire("Gagal Ambil Data. ", error.responseJSON.message, 'error')
                    }
                })
            } else {
                $('.target-recap').hide()
                $('.target-detail').show()
                $('.data-table').DataTable().destroy();
                $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 50,
                    scrollX: true,
                    ajax: {
                        url: "{{ route('visit_report') }}" + param,
                    },
                    language: {
                        processing: '<img src="{{ asset('images/833.gif') }}" alt="">',
                        paginate: {
                            'first': 'First',
                            'last': 'Last',
                            'next': '→',
                            'previous': '←'
                        },
                        emptyTable: "Data Tidak Ditemukan",
                    },
                    columns: [{
                        data: 'visit_date',
                        name: 'visit_date'
                    }, {
                        data: 'salesman.nama_salesman',
                        name: 'salesman.nama_salesman',
                    }, {
                        data: 'pelanggan.nama_pelanggan',
                        name: 'pelanggan.nama_pelanggan',
                    }, {
                        data: 'visit_title',
                        name: 'visit_title',
                    }, {
                        data: 'visit_desc',
                        name: 'visit_desc',
                    }, {
                        data: 'solusi',
                        name: 'solusi',
                    }],
                });
            }
        }
    </script>
    <script src="{{ asset('js/for-report.js') }}"></script>
    <script>
        $('[name="id_pelanggan"]').select2({
            ajax: {
                url: '{{ route('visit_report_customer') }}',
                dataType: 'json',
                data: function(params) {
                    return {
                        search: params.term
                    }
                },
                processResults: function(data) {
                    return {
                        results: [{
                            'id': '',
                            'text': 'Semua Pelanggan'
                        }, ...data.datas]
                    };
                }
            }
        })

        $('body').on('click', '.show-customer', function() {
            let id = $(this).data('id')
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('visit_report_find_customer') }}',
                type: 'get',
                data: {
                    'id': id
                },
                success: function(res) {
                    let statusActive = {
                        '0': '',
                        '1': 'Aktif',
                        '2': 'Pindah',
                        '3': 'Tutup'
                    }
                    Object.keys(res.data).forEach(key => {
                        if (key == 'status_aktif_pelanggan') {
                            $('#target_' + key).text(statusActive[res.data[key]])
                        } else {
                            $('#target_' + key).text(res.data[key])
                        }
                    });
                    $('#target-maps').html(res.map)
                    $('#cover-spin').hide()
                    $('#modal-customer').modal()
                },
                error: function(error) {
                    $('#cover-spin').hide()
                    Swal.fire("Gagal Menyimpan Data. ", data.responseJSON.message, 'error')
                }
            })
        })
    </script>
@endsection
