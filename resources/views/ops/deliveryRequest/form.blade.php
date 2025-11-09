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

        label>span {
            color: red;
        }

        .select2 {
            width: 100% !important;
        }

        .table-detail th {
            background-color: #f39c12;
            color: white;
            text-align: center;
        }

        .handle-number-4 {
            text-align: right;
        }

        .has-success .form-control {
            border-color: #5cb85c;
        }

        .success-border {
            border-color: #5cb85c !important;
        }

        .has-error .form-control {
            border-color: #dd4b39;
        }

        .error-border {
            border-color: #dd4b39 !important;
        }

        .help-block.text-red {
            color: #dd4b39;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        /* Required field indicator */
        label>span {
            color: #dd4b39;
            font-weight: bold;
        }

        /* Form group spacing */
        .form-group {
            margin-bottom: 20px;
        }

        /* Select2 error state */
        .select2-container--default .select2-selection--single.error-border {
            border-color: #dd4b39 !important;
        }

        .select2-container--default .select2-selection--single.success-border {
            border-color: #5cb85c !important;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Permintaan Pengiriman
            <small>| {{ $data ? 'Edit' : 'Tambah' }}</small>
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
        <form action="{{ route('delivery_request-save-entry', $data ? $data->id : 0) }}" method="post"
            id="deliveryRequestForm">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ $data ? 'Ubah' : 'Tambah' }} Permintaan Pengiriman</h3>
                    <a href="{{ route('delivery_request') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right">
                        <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                    </a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cabang Peminta <span>*</span></label>
                                <select name="branch_id" class="form-control select2">
                                    <option value="">Pilih Cabang</option>
                                    @if ($data && $data->branch_id)
                                        <option value="{{ $data->branch_id }}" selected>
                                            {{ $data->branch->nama_cabang }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <label>Cabang Tujuan <span>*</span></label>
                            <div class="form-group">
                                <select name="destination_branch_id" class="form-control select2"
                                    data-validation="[NOTEMPTY]" data-validation-message="Cabang tujuan tidak boleh kosong">
                                    <option value="">Pilih Cabang Tujuan</option>
                                    @if ($data && $data->destination_branch_id)
                                        <option value="{{ $data->destination_branch_id }}" selected>
                                            {{ $data->destinationBranch->nama_cabang }}
                                        </option>
                                    @endif
                                </select>
                            </div>

                            {{-- @if ($data)
                                <div class="row">
                                    <label class="col-md-3">Status</label>
                                    <div class="col-md-5 form-group">
                                        @if (isset($arrayStatus[$data->approval_status]))
                                            <label class="{{ $arrayStatus[$data->approval_status]['class'] }}">
                                                {{ $arrayStatus[$data->approval_status]['text'] }}
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endif --}}
                        </div>
                        <div class="col-md-4">
                            <label>Tanggal <span>*</span></label>
                            <div class="form-group">
                                <input type="date" name="date"
                                    value="{{ old('date', $data ? $data->date : date('Y-m-d')) }}" class="form-control">
                            </div>
                            <label>Estimasi Kedatangan <span>*</span></label>
                            <div class="form-group">
                                <input type="date" name="estimated_delivery_date"
                                    value="{{ old('estimated_delivery_date', $data ? $data->estimated_delivery_date : date('Y-m-d')) }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Kode Transaksi</label>
                            <div class="form-group">
                                <input type="text" name="delivery_request_code"
                                    value="{{ old('delivery_request_code', $data ? $data->delivery_request_code : '') }}"
                                    class="form-control" readonly placeholder="Otomatis">
                            </div>
                            <label>Keterangan </label>
                            <div class="form-group">
                                <textarea name="desc" class="form-control" rows="3">{{ old('desc', $data ? $data->desc : '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Detil Barang</h3>
                    {{-- @if (!$data || $data->approval_status == 0) --}}
                    <button class="btn btn-info add-entry btn-flat pull-right btn-sm" type="button">
                        <i class="glyphicon glyphicon-plus"></i> Tambah Barang
                    </button>
                    {{-- @endif --}}
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <input type="hidden" name="details" value="">
                        <input type="hidden" name="rm_details" value="">
                        <table id="table-detail" class="table table-bordered data-table display" width="100%">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th style="width:150px;">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-primary btn-flat pull-right" type="submit">
                        <i class="glyphicon glyphicon-floppy-saved"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>

        <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <form action="" id="formEntry" method="post">
                        <div class="modal-body">
                            <label>Nama Barang <span>*</span></label>
                            <div class="form-group">
                                <select name="item_id" class="form-control">
                                </select>
                            </div>
                            <label>Satuan <span>*</span></label>
                            <div class="form-group">
                                <select name="unit_id" class="form-control select2">
                                </select>
                            </div>
                            <label>Jumlah <span>*</span></label>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" name="qty" class="form-control handle-number-4"
                                        autocomplete="off">
                                    <span id="unit" class="input-group-addon"></span>
                                </div>
                            </div>
                            <label>Catatan </label>
                            <div class="form-group">
                                <textarea name="desc" class="form-control validate" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary save-entry btn-flat">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script> --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- <script src="{{ asset('js/fancybox.min.js') }}"></script> --}}
    <script src="{{ asset('js/custom.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        let branches = {!! json_encode($branches) !!}
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        let rmDetails = [];
        let detailSelect = []
        let statusModal = 'create'

        $('[name="details"]').val(JSON.stringify(details))
        $('[name="rm_details"]').val(JSON.stringify(rmDetails))

        $('[name="branch_id"]').select2({
            data: [{
                'id': '',
                'text': 'Pilih Cabang'
            }, ...branches],
            placeholder: 'Pilih Cabang',
        });

        $('[name="destination_branch_id"]').select2({
            data: [{
                'id': '',
                'text': 'Pilih Cabang Tujuan'
            }, ...branches],
            placeholder: 'Pilih Cabang Tujuan',
        });

        var resDataTable = $('#table-detail').DataTable({
            scrollX: true,
            paging: false,
            data: details,
            ordering: false,
            columns: [{
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
                data: 'desc',
                name: 'desc'
            }, {
                data: 'status',
                name: 'status',
                // render: function(data) {
                //     return statuses[data] ?? '';
                // },
                className: 'text-center'
            }, {
                data: 'index',
                className: 'text-center',
                name: 'index',
                searchable: false,
                render: function(data, type, row, meta) {
                    let btn = ''
                    btn +=
                        '<a href="javascript:void(0)" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a>';
                    btn +=
                        '<a href="javascript:void(0)" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1 delete-entry"><i class="glyphicon glyphicon-trash"></i></a>';
                    return btn;
                }
            }]
        });

        $('.add-entry').click(function() {
            detailSelect = []
            $('#modalEntry').find('input,select,textarea').each(function(i, v) {
                $(v).val('').trigger('change')
            })

            detailSelect['id'] = null;
            detailSelect['status'] = '1'

            statusModal = 'create'
            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            })

            $('[name="item_id"]').select2('open')
        })

        $('[name="item_id"]').select2({
            ajax: {
                url: '{{ route('delivery-request-auto_item') }}',
                dataType: 'json',
                data: function(params) {
                    return {
                        search: params.term
                    }
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            }
        }).on('select2:select', function(e) {
            let dataselect = e.params.data
            console.log(dataselect)
            $('#modalEntry').find('[name="nama_barang"]').val(dataselect.text)
            $('#modalEntry').find('[name="kode_barang"]').val(dataselect.kode_barang)
            $('[name="id_satuan_barang"]').html('')
            $('[name="qty"]').val('0')

            // Trigger validation when item is selected
            $(this).valid();

            getItemUnit(dataselect.id)

        });

        function getItemUnit(itemId) {
            return $.ajax({
                url: '{{ route('delivery-request-auto_item_unit') }}',
                dataType: 'json',
                data: {
                    id_barang: itemId
                },
                success: function(data) {
                    $('[name="unit_id"]').empty().select2({
                        data: data,
                        placeholder: 'Pilih Satuan'
                    });

                    if (data.length > 0) {
                        $('#unit').text(data[0].text)
                    }
                },
                error: function(xhr, status, error) {
                    alert('Terjadi kesalahan saat mengambil data satuan barang.')
                }
            });
        }

        $('[name="unit_id"]').on('select2:select', function(e) {
            console.log(e)
            let dataselect = e.params.data
            $('#unit').text(dataselect.text)

            // Trigger validation when unit is selected
            $(this).valid();
        })

        // Add custom validation methods
        $.validator.addMethod("select2Required", function(value, element) {
            return value && value !== "" && value !== null;
        }, "Field ini wajib diisi.");

        $.validator.addMethod("positiveNumber", function(value, element) {
            if (this.optional(element)) return true;

            // Normalize the number before validation
            let normalizedValue = normalizeNumber(value);
            return $.isNumeric(normalizedValue) && normalizedValue > 0;
        }, "Nilai harus berupa angka yang lebih besar dari 0.");

        $.validator.addMethod("normalizedRequired", function(value, element) {
            if (!value || value === '') return false;

            // Normalize the number and check if it's valid
            let normalizedValue = normalizeNumber(value);
            return normalizedValue > 0;
        }, "Field ini tidak boleh kosong.");

        // Initialize jQuery Validation 1.19.5
        let validator = $('#formEntry').validate({
            rules: {
                item_id: {
                    select2Required: true
                },
                unit_id: {
                    select2Required: true
                },
                qty: {
                    required: true,
                    normalizedRequired: true,
                    positiveNumber: true
                }
            },
            messages: {
                item_id: {
                    select2Required: "Nama barang tidak boleh kosong"
                },
                unit_id: {
                    select2Required: "Satuan tidak boleh kosong"
                },
                qty: {
                    required: "Jumlah tidak boleh kosong",
                    positiveNumber: "Jumlah harus berupa angka yang lebih besar dari 0",
                    normalizedRequired: "Jumlah tidak boleh kosong"
                }
            },
            errorElement: 'span',
            errorClass: 'help-block text-red',
            errorPlacement: function(error, element) {
                if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2-container'));
                } else if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).closest('.form-group').addClass('has-error');
                if ($(element).hasClass('select2-hidden-accessible')) {
                    $(element).next('.select2-container').find('.select2-selection').addClass('error-border');
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).closest('.form-group').removeClass('has-error');
                if ($(element).hasClass('select2-hidden-accessible')) {
                    $(element).next('.select2-container').find('.select2-selection').removeClass(
                        'error-border');
                }
            },
            submitHandler: function(form) {
                // Form is valid, save data
                saveEntryData();
                return false;
            },
            invalidHandler: function(event, validator) {
                // Handle validation errors
                console.log('Form validation failed. Errors:', validator.numberOfInvalids());
            }
        });

        // Function to handle saving entry data
        function saveEntryData() {
            let qtyValue = normalizeNumber($('[name="qty"]').val());
            // Get form data
            let formData = {
                item_id: $('[name="item_id"]').val(),
                unit_id: $('[name="unit_id"]').val(),
                qty: qtyValue,
                desc: $('#modalEntry [name="desc"]').val(),
                nama_barang: $('[name="item_id"] option:selected').text() || $('[name="item_id"]').select2('data')[0]
                    ?.text || '',
                nama_satuan_barang: $('[name="unit_id"] option:selected').text() || $('[name="unit_id"]').select2(
                    'data')[0]?.text || ''
            };


            console.log(formData);

            for (const key in formData) {
                detailSelect[key] = formData[key];
            }

            console.log(detailSelect);

            if (statusModal === 'create') {
                // Add new entry
                details.push(Object.assign({}, detailSelect));
            } else {
                // Update existing entry
                let index = detailSelect.id;
                details[index] = {
                    ...details[index],
                    ...formData
                };
            }

            // Update the details input and refresh table
            $('[name="details"]').val(JSON.stringify(details));
            resDataTable.clear().rows.add(details).draw();

            // Close modal
            $('#modalEntry').modal('hide');

            console.log(details);
            detailSelect = [];
        }

        // Handle edit entry
        $(document).on('click', '.edit-entry', function() {
            let rowData = resDataTable.row($(this).parents('tr')).data();
            let rowIndex = resDataTable.row($(this).parents('tr')).index();

            detailSelect['id'] = rowIndex;
            statusModal = 'edit';

            // Fill form with existing data
            $('[name="item_id"]').empty().append(new Option(rowData.nama_barang, rowData.item_id, true, true))
                .trigger('change');
            $('[name="qty"]').val(formatNumber(rowData.qty, 4));
            $('#modalEntry [name="desc"]').val(rowData.desc);

            // Load units for selected item
            getItemUnit(rowData.item_id).then(() => {
                $('[name="unit_id"]').val(rowData.unit_id).trigger('change');
            });

            $('#modalEntry').modal({
                backdrop: 'static',
                keyboard: false
            });
        });

        // Handle delete entry
        $(document).on('click', '.delete-entry', function() {
            let rowIndex = resDataTable.row($(this).parents('tr')).index();

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove from details array
                    rmDetails.push(details[rowIndex]);
                    $('[name="rm_details"]').val(JSON.stringify(rmDetails));

                    details.splice(rowIndex, 1);

                    // Update indices
                    details.forEach((item, index) => {
                        item.index = index;
                    });

                    // Update the details input and refresh table
                    $('[name="details"]').val(JSON.stringify(details));
                    resDataTable.clear().rows.add(details).draw();
                }
            });
        });

        // Reset validation when modal is closed
        $('#modalEntry').on('hidden.bs.modal', function() {
            // Reset jQuery Validation
            validator.resetForm();
            // Clear all error classes and messages
            $('#formEntry').find('.has-error').removeClass('has-error');
            $('#formEntry').find('.error-border').removeClass('error-border');
            $('#formEntry').find('.help-block.text-red').remove();
        });

        // Real-time validation for qty input
        $('[name="qty"]').on('blur keyup', function() {
            $(this).valid();
        });

        // Add Select2 validation triggers
        $('[name="item_id"], [name="unit_id"]').on('select2:select select2:unselect', function() {
            $(this).valid();
        });

        // Validate Select2 on change
        $('[name="item_id"], [name="unit_id"]').on('change', function() {
            $(this).valid();
        });

        // Show validation success feedback
        validator.settings.success = function(label, element) {
            $(element).closest('.form-group').addClass('has-success').removeClass('has-error');
            if ($(element).hasClass('select2-hidden-accessible')) {
                $(element).next('.select2-container').find('.select2-selection').removeClass('error-border').addClass(
                    'success-border');
            }
        };

        // Add validation for main delivery request form
        $('#deliveryRequestForm').validate({
            rules: {
                branch_id: {
                    required: true
                },
                destination_branch_id: {
                    required: true,
                    notEqualTo: '[name="branch_id"]'
                },
                date: {
                    required: true,
                    date: true
                },
                estimated_delivery_date: {
                    required: true,
                    date: true,
                    minDate: '[name="date"]'
                }
            },
            messages: {
                branch_id: {
                    required: "Cabang peminta tidak boleh kosong"
                },
                destination_branch_id: {
                    required: "Cabang tujuan tidak boleh kosong",
                    notEqualTo: "Cabang tujuan tidak boleh sama dengan cabang peminta"
                },
                date: {
                    required: "Tanggal tidak boleh kosong",
                    date: "Format tanggal tidak valid"
                },
                estimated_delivery_date: {
                    required: "Estimasi kedatangan tidak boleh kosong",
                    date: "Format tanggal tidak valid",
                    minDate: "Estimasi kedatangan tidak boleh kurang dari tanggal permintaan"
                }
            },
            errorElement: 'span',
            errorClass: 'help-block text-red',
            errorPlacement: function(error, element) {
                if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2-container'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).closest('.form-group').addClass('has-error');
                if ($(element).hasClass('select2-hidden-accessible')) {
                    $(element).next('.select2-container').find('.select2-selection').addClass('error-border');
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).closest('.form-group').removeClass('has-error');
                if ($(element).hasClass('select2-hidden-accessible')) {
                    $(element).next('.select2-container').find('.select2-selection').removeClass(
                        'error-border');
                }
            },
            submitHandler: function(form) {
                console.log('Main form is valid, submitting...');
                saveData($(form))
                return false;
            },
            invalidHandler: function(event, validator) {
                // Show error notification
                let errorCount = validator.numberOfInvalids();
                Swal.fire({
                    title: 'Validasi Gagal!',
                    text: `Terdapat ${errorCount} field yang belum diisi dengan benar. Silahkan periksa kembali.`,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });

        // Add custom validation methods for main form
        $.validator.addMethod("notEqualTo", function(value, element, param) {
            let target = $(param);
            if (this.settings.onfocusout) {
                target.off(".validate-notEqualTo").on("blur.validate-notEqualTo", function() {
                    $(element).valid();
                });
            }
            return value !== target.val();
        }, "Nilai tidak boleh sama dengan field yang lain.");

        $.validator.addMethod("minDate", function(value, element, param) {
            if (this.optional(element)) return true;

            let targetDate = new Date($(param).val());
            let currentDate = new Date(value);

            return currentDate >= targetDate;
        }, "Tanggal harus lebih besar atau sama dengan tanggal awal.");

        // Add real-time validation for Select2 fields in main form
        $('[name="branch_id"], [name="destination_branch_id"]').on('select2:select select2:unselect change', function() {
            // Trigger validation for both fields when either changes
            $('[name="branch_id"]').valid();
            $('[name="destination_branch_id"]').valid();
        });

        // Add real-time validation for date fields
        $('[name="date"], [name="estimated_delivery_date"]').on('change blur', function() {
            // Trigger validation for both date fields when either changes
            $('[name="date"]').valid();
            $('[name="estimated_delivery_date"]').valid();
        });

        // Validate branch selection to prevent same branch selection
        $('[name="destination_branch_id"]').on('select2:select', function(e) {
            let selectedValue = e.params.data.id;
            let branchValue = $('[name="branch_id"]').val();

            if (selectedValue && branchValue && selectedValue === branchValue) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Cabang tujuan tidak boleh sama dengan cabang peminta.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });

                // Clear the selection
                $(this).val(null).trigger('change');
            }
        });

        // Also check when branch_id changes
        $('[name="branch_id"]').on('select2:select', function(e) {
            let selectedValue = e.params.data.id;
            let destinationValue = $('[name="destination_branch_id"]').val();

            if (selectedValue && destinationValue && selectedValue === destinationValue) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Cabang peminta tidak boleh sama dengan cabang tujuan.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });

                // Clear the destination selection
                $('[name="destination_branch_id"]').val(null).trigger('change');
            }
        });

        // Add validation for minimum estimated delivery date
        $('[name="date"]').on('change', function() {
            let selectedDate = $(this).val();
            let estimatedDate = $('[name="estimated_delivery_date"]').val();

            if (selectedDate && estimatedDate && new Date(estimatedDate) < new Date(selectedDate)) {
                $('[name="estimated_delivery_date"]').val(selectedDate);

                Swal.fire({
                    title: 'Info',
                    text: 'Estimasi kedatangan telah disesuaikan dengan tanggal permintaan.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        // Add success feedback for main form validation
        $('#deliveryRequestForm').find('select, input').on('change blur', function() {
            if ($(this).valid()) {
                $(this).closest('.form-group').addClass('has-success').removeClass('has-error');
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).next('.select2-container').find('.select2-selection')
                        .removeClass('error-border').addClass('success-border');
                }
            }
        });
    </script>
@endsection
