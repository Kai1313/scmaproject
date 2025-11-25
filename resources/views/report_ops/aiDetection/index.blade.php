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

        table.dataTable tr.dtrg-group th {
            background-color: #e0e0e0;
            text-align: left;
        }

        tfoot>tr>td {
            background-color: #e0e0e0;
        }

        /* Modal preview image styles */
        .image-preview-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .image-preview-content {
            position: relative;
            margin: 2% auto;
            padding: 0;
            width: 90%;
            max-width: 1000px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .image-preview-header {
            background-color: #f5f5f5;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
        }

        .image-preview-header h4 {
            margin: 0;
            color: #333;
        }

        .close-preview {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            line-height: 1;
        }

        .close-preview:hover {
            color: #000;
        }

        .image-preview-body {
            padding: 20px;
            text-align: center;
        }

        .preview-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Deteksi AI
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Deteksi AI </li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-2">
                        <label>Tanggal Awal</label>
                        <div class="form-group">
                            <input type="date" name="start_date" class="form-control trigger-change"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Akhir</label>
                        <div class="form-group">
                            <input type="date" name="end_date" class="form-control trigger-change"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label style="width:100%;"> &nbsp</label>
                        <div class="form-group ">
                            {{-- <a href="{{ route('ai_detection-print') }}" target="_blank"
                                class="btn btn-danger btn-sm btn-flat btn-action">
                                <i class="glyphicon glyphicon-print"></i> Print
                            </a> --}}
                            {{-- <a href="{{ route('ai_detection-excel') }}"
                                class="btn btn-success btn-sm btn-flat btn-action">
                                <i class="fa fa-file-excel-o"></i> Excel
                            </a> --}}
                            <a href="javascript:void(0)" class="btn btn-default btn-sm btn-flat btn-view-action">
                                <i class="glyphicon glyphicon-eye-open"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive" id="target-table" style="display:none;">
                    <table class="table table-bordered data-table display responsive" width="100%">
                        <thead>
                            <tr>
                                <th>Nama CCTV</th>
                                <th>Tanggal</th>
                                <th>Nama Terdeteksi</th>
                                <th>Gambar</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" class="image-preview-modal">
        <div class="image-preview-content">
            <div class="image-preview-header">
                <span class="close-preview">&times;</span>
                <h4>Preview Gambar Deteksi AI</h4>
            </div>
            <div class="image-preview-body">
                <img id="previewImage" src="" alt="Preview" class="preview-image">
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
        let defaultUrlIndex = '{{ route('ai_detection-index') }}'

        function loadDatatable() {
            $('#target-table').show()
            table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: defaultUrlIndex + param,
                pageLength: 50,
                lengthMenu: [
                    [10, 25, 50, -1], // Opsi yang tersedia
                    [10, 25, 50, 'All'] // Label untuk opsi
                ],
                columns: [{
                    data: 'cctv_name',
                    name: 'cctv_name',
                }, {
                    data: 'created_at',
                    name: 'created_at',
                }, {
                    data: 'detected_name',
                    name: 'detected_name',
                }, {
                    data: 'path',
                    name: 'path',
                }],
            });
        }

        // Image preview functionality
        $(document).ready(function() {
            const modal = $('#imagePreviewModal');
            const previewImage = $('#previewImage');
            const closeBtn = $('.close-preview');

            // Event delegation untuk tombol gambar
            $(document).on('click', '.btn-preview-image', function(e) {
                e.preventDefault();

                const imageUrl = $(this).data('image-url');
                // Set image source
                previewImage.attr('src', imageUrl);

                // Show modal
                modal.show();
            });

            // Close modal events
            closeBtn.click(function() {
                modal.hide();
            });

            // Close when clicking outside the modal content
            $(window).click(function(e) {
                if (e.target == modal[0]) {
                    modal.hide();
                }
            });

            // Close with ESC key
            $(document).keydown(function(e) {
                if (e.keyCode === 27) { // ESC key
                    modal.hide();
                }
            });
        });
    </script>
    <script src="{{ asset('js/for-report.js') }}"></script>
    <script>
        $('.btn-view-action').click()
    </script>
@endsection
