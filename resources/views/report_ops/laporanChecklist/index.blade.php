@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
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
            Laporan Checklist Pekerjaan
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Checklist Pekerjaan</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-3">
                        <label>Lokasi</label>
                        <div class="form-group">
                            <select name="location" class="form-control select2 trigger-change change-filter"
                                style="width:100%;">
                                <option value="">Pilih Lokasi</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->alamat_objek_kerja }}">{{ $location->alamat_objek_kerja }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="date" name="date" class="form-control trigger-change change-filter"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Grup Pengguna</label>
                        <div class="form-group">
                            <select name="user_group" class="form-control select2 trigger-change change-filter"
                                style="width:100%;">
                                <option value="">Pilih Pengguna</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user['id'] }}">{{ $user['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="pull-right">
                    {{-- <a href="{{ route('checklist-print') }}" target="_blank"
                        class="btn btn-danger btn-sm btn-flat btn-action" style="display: none">
                        <i class="glyphicon glyphicon-print"></i> Print
                    </a> --}}
                    <a href="{{ route('checklist-excel') }}" class="btn btn-success btn-sm btn-flat btn-action"
                        style="display: none;">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <button class="btn btn-default btn-sm btn-flat btn-view-action" disabled>
                        <i class="glyphicon glyphicon-eye-open"></i> View
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive" id="target-table" style="display:none;">
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Detail Lokasi</th>
                                <th width="100"></th>
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
    <script type="text/javascript" src="{{ asset('assets/bower_components/moment/moment.js') }}"></script>
    <script type="text/javascript"
        src="{{ asset('assets/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let defaultUrlIndex = '{{ route('report_checklist') }}'
        let urlGetLocation = '{{ route('checklist-location') }}'
        let urlGetUserGroup = '{{ route('checklist-user-group') }}'
        let defaultUrlPrint = []
        let param = ''
        let table = ''
        var defaultFilter = sessionStorage.getItem('report_checklist_filter') ? JSON.parse(sessionStorage.getItem(
            'report_checklist_filter')) : {};
        for (const key in defaultFilter) {
            $('[name="' + key + '"]').val(defaultFilter[key])
        }

        $('.select2').select2()
        $('.btn-action').each(function(i, v) {
            defaultUrlPrint.push($(v).prop('href'))
        })

        getParam()
        $('.trigger-change').change(function() {
            getParam()
            changeFilter()
        })

        function getParam() {
            param = ''
            let s = 0
            $('.trigger-change').each(function(i, v) {
                param += (i == 0) ? '?' : '&'
                param += $(v).prop('name') + '=' + $(v).val()
                if ($(v).val() == '') {
                    s++;
                    return false
                }
            })

            if (s == 0) {
                $('.btn-action').show()
                $('.btn-view-action').attr('disabled', false)
                setTimeout(() => {
                    $('.btn-view-action').click()
                }, 100);
            } else {
                $('.btn-action').hide()
                $('.btn-view-action').attr('disabled', true)
            }

            $('.btn-action').each(function(i, v) {
                $(v).prop('href', defaultUrlPrint[i] + param)
            })
        }

        $('.btn-action').each(function(i, v) {
            $(v).prop('href', defaultUrlPrint[i] + param)
        })

        $('.btn-view-action').click(function() {
            if ($('[name="type"]').length > 0) {
                if (table && reportType == $('[name="type"]').val()) {
                    table.ajax.url(param).load()
                } else {
                    loadDatatable()
                }
            } else {
                if (table) {
                    table.ajax.url(param).load()
                } else {
                    loadDatatable()
                }
            }
        })

        function loadDatatable() {
            $('#target-table').show()
            table = $('.data-table').DataTable({
                searching: false,
                scrollX: true,
                processing: true,
                serverSide: true,
                paging: false,
                ajax: defaultUrlIndex + param,
                columns: [{
                    data: 'nama_objek_kerja',
                    name: 'nama_objek_kerja',
                    render: function(data, type, row) {
                        let split = defaultUrlIndex.split('/index')
                        return '<a href="' + split[0] + '/view/' + row.id_objek_kerja +
                            '?date=' + $('[name="date"]').val() +
                            '&grup=' + $('[name="user_group"]').val() + '">' +
                            data + '</a>'
                    }
                }, {
                    data: 'id_jawaban_checklist_pekerjaan',
                    name: 'id_jawaban_checklist_pekerjaan',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (!data) {
                            return 'Belum dikerjakan'
                        }

                        let countTanya = 0
                        let countCheck = 0
                        for (let i = 1; i <= 25; i++) {
                            if (row['pekerjaan' + i + '_jawaban_checklist_pekerjaan']) {
                                countTanya++;
                            }

                            if (row['jawaban' + i + '_jawaban_checklist_pekerjaan'] && row['jawaban' +
                                    i + '_jawaban_checklist_pekerjaan'] != 0) {
                                countCheck++;
                            }
                        }

                        if (countTanya == countCheck) {
                            return 'Semua sudah dikerjalan'
                        } else {
                            return countCheck + ' dari ' + countTanya + ' sudah dikerjakan'
                        }
                    }
                }]
            });
        }

        function changeFilter() {
            $('.change-filter').each(function(i, v) {
                defaultFilter[$(v).prop('name')] = $(v).val()
            })

            sessionStorage.setItem('report_checklist_filter', JSON.stringify(defaultFilter));
        }
    </script>
@endsection
