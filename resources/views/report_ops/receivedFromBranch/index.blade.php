@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}" />
    <style>
        th {
            text-align: center;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Laporan Terima Dari Cabang
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Terima Dari Cabang</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Cabang</label>
                                <div class="form-group">
                                    <select name="id_cabang" class="form-control select2 trigger-change">
                                        @foreach (getCabangForReport() as $branch)
                                            <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>Gudang</label>
                                <div class="form-group">
                                    <select name="id_gudang" class="form-control select2 trigger-change">
                                        <option value="all">Semua Gudang</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>Tanggal</label>
                                <div class="form-group">
                                    <input type="text" name="date" class="form-control trigger-change">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>Status Terima Dari Cabang</label>
                                <div class="form-group">
                                    <select name="status" class="form-control select2 trigger-change">
                                        <option value="all">Semua Status</option>
                                        @foreach ($arrayStatus as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>Jenis Laporan</label>
                                <div class="form-group">
                                    <select name="type" class="form-control select2 trigger-change">
                                        @foreach ($typeReport as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
                                <label>Kode Transaksi</label>
                                <div class="form-group">
                                    <input type="text" class="form-control trigger-change" name="kode_pindah_barang">
                                </div>
                            </div> --}}
                            {{-- <div class="col-md-3">
                                <label>Nama Barang</label>
                                <div class="form-group">
                                    <input type="text" class="form-control trigger-change" name="nama_barang">
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('report_received_from_branch-print') }}"
                            class="btn btn-primary btn-sm btn-flat pull-right btn-action" style="margin-top:26px;"
                            target="_blank">
                            <i class="glyphicon glyphicon-print"></i> Print
                        </a>
                    </div>
                </div>

            </div>
        </div>
        <div class="box">
            <div class="box-body" id="target-html">

            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    {{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script> --}}
    <script type="text/javascript" src="{{ asset('assets/bower_components/moment/moment.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let defaultUrlIndex = '{{ route('report_received_from_branch-index') }}'
        let defaultUrlPrint = $('.btn-action').prop('href')
        let param = ''
        let branch = {!! json_encode(session()->get('access_cabang')) !!}
        let gArray = [];

        $('.select2').select2()
        $('[name="date"]').daterangepicker({
            timePicker: true,
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        $('.btn-action').prop('href', defaultUrlPrint + param)
        $(document).ready(function() {
            getData()
        })

        function getParam() {
            param = ''
            $('.trigger-change').each(function(i, v) {
                param += (i == 0) ? '?' : '&'
                param += $(v).prop('name') + '=' + $(v).val()
            })

            $('.btn-action').prop('href', defaultUrlPrint + param)
        }

        function getData() {
            $('#cover-spin').show()
            setTimeout(() => {
                getParam()
                $.ajax({
                    url: defaultUrlIndex + param,
                    success: function(res) {
                        $('#target-html').html(res.html)
                        $('#cover-spin').hide()
                    },
                    error: function(error) {
                        let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON
                            .message : error
                            .statusText
                        Swal.fire("Gagal Mengambil Data. ", textError, 'error')
                        $('#cover-spin').hide()
                    }
                })
            }, 100);
        }

        $('.trigger-change').change(function() {
            getData()
        })

        $('[name="id_cabang"]').select2().on('select2:select', function(e) {
            let dataselect = e.params.data
            clearWarehouse()
            for (let i = 0; i < branch.length; i++) {
                if (branch[i].id == dataselect.id) {
                    getWarehouse(branch[i].gudang)
                    break
                }
            }
        });

        function getWarehouse(arrayGudang) {
            gArray = []
            if (arrayGudang.length > 0) {
                gArray.push({
                    'id': arrayGudang.map(s => s.id).join(','),
                    'text': 'Semua Gudang'
                })
            }

            for (let a = 0; a < arrayGudang.length; a++) {
                gArray.push({
                    'id': arrayGudang[a].id,
                    'text': arrayGudang[a].text
                })
            }

            $('[name="id_gudang"]').empty()
            $('[name="id_gudang"]').select2({
                data: gArray
            })
        }

        function clearWarehouse() {
            console.log('asd')
            $('[name="id_gudang"]').empty()
            $('[name="id_gudang"]').select2({
                data: [{
                    'id': 'all',
                    'text': 'Semua Gudang'
                }]
            })
        }
    </script>
@endsection
