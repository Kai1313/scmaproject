@extends('layouts.main')

@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fancybox.css') }}" />
    <style>
        th {
            text-align: center;
        }

        ul.horizontal-list {
            min-width: 200px;
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
            Kirim Ke Cabang
            <small>| Detail</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('send_to_branch') }}">Kirim Ke Cabang</a></li>
            <li class="active">Detail</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Detail Kirim Ke Cabang <span class="text-muted"></span></h3>
                <a href="{{ route('send_to_branch-print-data', $data->id_pindah_barang) }}" target="_blank"
                    class="btn btn-sm btn-default btn-flat pull-right">
                    <span class="glyphicon glyphicon-print mr-1"></span> Cetak
                </a>
                <a href="javascript:void(0)" class="btn btn-default btn-flat btn-sm pull-right mr-1 show-media">
                    <i class="fa fa-image mr-1"></i> Dokumentasi
                </a>
                <a href="{{ route('send_to_branch') }}" class="btn bg-navy btn-sm btn-default btn-flat pull-right"
                    style="margin-right:10px;">
                    <span class="glyphicon glyphicon-arrow-left mr-1" aria-hidden="true"></span> Kembali
                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Cabang Asal</label>
                            <div class="col-md-8">
                                : {{ $data->cabang->kode_cabang }} - {{ $data->cabang->nama_cabang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Gudang Asal</label>
                            <div class="col-md-8">
                                : {{ $data->gudang->nama_gudang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Kode Pindah Cabang</label>
                            <div class="col-md-8">
                                : {{ $data->kode_pindah_barang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Tanggal</label>
                            <div class="col-md-8">
                                : {{ $data->tanggal_pindah_barang }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="col-md-4">Cabang Tujuan</label>
                            <div class="col-md-8">
                                : {{ $data->cabang2->nama_cabang }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Jasa Pengiriman</label>
                            <div class="col-md-8">
                                : {{ $data->transporter }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">No Polisi Kendaraan</label>
                            <div class="col-md-8">
                                : {{ $data->nomor_polisi }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-4">Keterangan</label>
                            <div class="col-md-8">
                                : {{ $data->keterangan_pindah_barang }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-body box-table">
                <h4>Detil Barang</h4>
                <div class="table-responsive">
                    <table id="table-detail" class="table table-bordered data-table nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>QR Code</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Batch</th>
                                <th>Kadaluarsa</th>
                                <th>SG</th>
                                <th>BE</th>
                                <th>PH</th>
                                <th>Bentuk</th>
                                <th>Warna</th>
                                <th>Catatan</th>
                                <th>Keterangan SJ</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEntryEdit" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Barang</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="index">
                    <label>Catatan</label>
                    <div class="form-group">
                        <textarea name="keterangan" class="form-control" rows="3"></textarea>
                    </div>
                    <label>Keterangan Surat Jalan</label>
                    <div class="form-group">
                        <textarea name="keterangan_sj" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary save-entry-edit btn-flat">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEntryCamera" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Dokumentasi</h4>
                </div>
                <div class="modal-body">
                    <div class="show-res-camera" style="overflow-x: scroll;overflow-y: hidden;white-space: nowrap;">
                        @if ($data)
                            @foreach ($data->medias as $media)
                                <div style="display:inline-block;margin:5px;">
                                    <div style="margin-bottom:10px;">
                                        <a data-fancybox="lightbox" href="{{ asset($media->lokasi_media) }}">
                                            <img src="{{ asset($media->lokasi_media) }}" alt=""
                                                style="width:100px;height:100px;object-fit:cover;border-radius:5px;"
                                                loading="lazy">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('addedScripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/fancybox.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('externalScripts')
    <script>
        let details = {!! $data ? $data->formatdetail : '[]' !!};
        var resDataTable = $('#table-detail').DataTable({
            scrollX: true,
            paging: false,
            data: details,
            ordering: false,
            drawCallback: function() {
                var allData = this.api().column(0).data().toArray();
                var toFindDuplicates = allData => allData.filter((item, index) => allData.indexOf(item) !==
                    index)
                var duplicateElementa = toFindDuplicates(allData);
                var indexs = []
                for (let i = 0; i < duplicateElementa.length; i++) {
                    let indexDuplicate = allData.indexOf(duplicateElementa[i])
                    $($('#table-detail tbody tr:eq(' + indexDuplicate + ')')).css('color', 'red')
                }
            },
            columns: [{
                    data: 'qr_code',
                    name: 'qr_code'
                }, {
                    data: 'nama_barang',
                    name: 'nama_barang'
                }, {
                    data: 'qty',
                    name: 'qty',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'nama_satuan_barang',
                    name: 'nama_satuan_barang'
                }, {
                    data: 'batch',
                    name: 'batch',
                    className: 'text-right'
                }, {
                    data: 'tanggal_kadaluarsa',
                    name: 'tanggal_kadaluarsa',
                }, {
                    data: 'sg',
                    name: 'sg',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'be',
                    name: 'be',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'ph',
                    name: 'ph',
                    render: function(data) {
                        return data ? formatNumber(data, 4) : 0
                    },
                    className: 'text-right'
                }, {
                    data: 'bentuk',
                    name: 'bentuk',
                },
                {
                    data: 'warna',
                    name: 'warna',
                }, {
                    data: 'keterangan',
                    name: 'keterangan',
                }, {
                    data: 'keterangan_sj',
                    name: 'keterangan_sj',
                }, {
                    data: 'status_akhir',
                    name: 'status_akhir',
                }, {
                    data: 'id_pindah_barang_detail',
                    className: 'text-center',
                    name: 'id_pindah_barang_detail',
                    width: 40,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        let btn = ''
                        if ('{{ $isEdit }}') {
                            btn +=
                                '<a href="javascript:void(0)" class="btn btn-warning btn-xs mr-1 mb-1 edit-entry"><i class="glyphicon glyphicon-pencil"></i></a>';
                        }
                        return btn;
                    }
                }
            ]
        });

        $('body').on('click', '.edit-entry', function() {
            let index = $(this).parents('tr').index()
            let modal = $('#modalEntryEdit')
            modal.find('[name="index"]').val(index)
            modal.find('[name="keterangan"]').val(details[index].keterangan)
            modal.find('[name="keterangan_sj"]').val(details[index].keterangan_sj)
            modal.modal('show')
        })

        $('.save-entry-edit').click(function() {
            let modal = $('#modalEntryEdit')
            let index = modal.find('[name="index"]').val()
            let object = {
                id_pindah_barang_detail: details[index].id_pindah_barang_detail,
                keterangan: modal.find('[name="keterangan"]').val().trim(),
                keterangan_sj: modal.find('[name="keterangan_sj"]').val().trim()
            }
            saveDetail(object)
        })

        function saveDetail(object) {
            let modal = $('#modalEntryEdit')
            $('#cover-spin').show()
            $.ajax({
                url: '{{ route('send_to_branch-save-entry-detail') }}',
                type: 'post',
                data: object,
                success: function(res) {
                    if (res.redirect) {
                        window.location.href = res.redirect
                    }

                    modal.modal('hide')
                    $('#cover-spin').hide()
                },
                error: function(error) {
                    let textError = error.hasOwnProperty('responseJSON') ? error.responseJSON.message : error
                        .statusText
                    Swal.fire("Gagal Mengambil Data. ", textError, 'error')
                    modal.modal('hide')
                    $('#cover-spin').hide()
                }
            })
        }

        $('.show-media').click(function() {
            $('#modalEntryCamera').modal('show')
        })

        Fancybox.bind('[data-fancybox="lightbox"]');
    </script>
@endsection
