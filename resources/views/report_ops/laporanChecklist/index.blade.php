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
                            <select name="location" class="form-control select2 trigger-change" style="width:100%;">
                               
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
                        <label>Grup Pengguna</label>
                        <div class="form-group">
                            <select name="user_group" class="form-control select2 trigger-change" style="width:100%;">
                               
                            </select>
                        </div>
                    </div>
                </div>
                <div class="pull-right">
                    <a href="{{ route('checklist-print') }}" target="_blank"
                        class="btn btn-danger btn-sm btn-flat btn-action">
                        <i class="glyphicon glyphicon-print"></i> Print
                    </a>
                    <a href="{{ route('checklist-excel') }}"
                        class="btn btn-success btn-sm btn-flat btn-action">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                    <a href="javascript:void(0)" class="btn btn-default btn-sm btn-flat btn-view-action">
                        <i class="glyphicon glyphicon-eye-open"></i> View
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive" id="target-table" style="display:none;">
                    <table class="table table-bordered data-table display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                {{-- <th>Qrcode</th> --}}
                                <th>Lokasi</th>
                                <th>Departemen</th>
                                <th>Pengguna</th>
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
        let urlGetLocation = '{{route('checklist-location')}}'
        let urlGetUserGroup = '{{route('checklist-user-group')}}'
        let defaultUrlPrint = []
        let param = ''
        let table = ''

        $('.select2').select2()
        $('[name="date"]').daterangepicker({
            timePicker: false,
            startDate: moment().subtract(1, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        $('.btn-action').each(function (i, v) {
            defaultUrlPrint.push($(v).prop('href'))
        })

        console.log(defaultUrlIndex)

        $('[name="location"]').select2({
            placeholder: 'Pilih Lokasi',
            ajax: {
                url: urlGetLocation,
                dataType: 'json',
                data: function (params) {
                    return {search: params.term}
                },
                processResults: function (data) {
                    return {
                        results: data.datas
                    };
                }
            }
        }).on('select2:select', function (e) {
            let dataselect = e.params.data
            if($('[name="date"]').val()){
                getUserGroup()
            }
        });

        function getUserGroup(){
            $('#cover-spin').show()
            $.ajax({
                url: urlGetUserGroup,
                type:'get',
                data:{
                    date:$('[name="date"]').val(),
                    location:$('[name="location"]').val()
                },
                success: function(res){
                    $('#cover-spin').hide()
                    $('[name="user_group"]').empty()
                    $('[name="user_group"]').select2({
                        data: [
                            {'id':'','text' : 'Pilih Grup'},
                            ...res.datas
                        ]
                    })
                },
                error: function(data){
                    $('#cover-spin').hide()
                    Swal.fire("Gagal Proses Data.", data.responseJSON.message, 'error')
                }
            })
        }

        $('[name="date"]').change(function(){
            getUserGroup()
        })

        $('.trigger-change').change(function () {
            getParam()
        })

        function getParam() {
            param = ''
            $('.trigger-change').each(function (i, v) {
                param += (i == 0) ? '?' : '&'
                param += $(v).prop('name') + '=' + $(v).val()
            })

            $('.btn-action').each(function (i, v) {
                $(v).prop('href', defaultUrlPrint[i] + param)
            })
        }

        $('.btn-action').each(function (i, v) {
            $(v).prop('href', defaultUrlPrint[i] + param)
        })

        $('.btn-view-action').click(function () {
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
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: defaultUrlIndex + param,
                columns: [{
                    data: 'tanggal_jawaban_checklist_pekerjaan',
                    name: 'jcp.tanggal_jawaban_checklist_pekerjaan'
                }, 
                // {
                //     data: 'kode_jawaban_checklist_pekerjaan',
                //     name: 'jcp.kode_jawaban_checklist_pekerjaan'
                // },
                 {
                    data: 'nama_objek_kerja',
                    name: 'ok.nama_objek_kerja',
                    render: function(data,type,row){
                        let split = defaultUrlIndex.split('/index')
                        return '<a href="'+split[0]+'/view/'+row.id_jawaban_checklist_pekerjaan+'" >'+data+'</a>'
                    }
                },{
                    data: 'nama_grup_pengguna',
                    name: 'gp.nama_grup_pengguna',
                }, {
                    data: 'nama_pengguna',
                    name: 'p.nama_pengguna',
                } ]
            });
        }
    </script>
    {{-- <script src="{{ asset('js/for-report.js') }}"></script> --}}
@endsection
