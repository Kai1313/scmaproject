@extends('layouts.template')

@section('content')
    <div class="form-add">
        <div class="d-flex justify-content-between align-items-center">
            <h3 id="header_form">Buat Akun Baru</h3>
            <div class="">
                <button class="close-button btn btn-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Close</button>
            </div>
        </div>
        <br />
        <form id="submit" class="form-horizontal" data-toggle="validator" enctype="multipart/form-data">
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="kode_akun">Kode Akun*</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control input-sm" id="kode_akun"
                        name="kode_akun" data-minlength="1" data-error="Wajib isi"
                        placeholder="Masukkan Kode Akun" required>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Kode Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="nama_akun">Nama Akun*</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control input-sm" id="nama_akun"
                        name="nama_akun" data-minlength="1" maxlength="150" data-error="Wajib isi"
                        placeholder="Masukkan Nama Akun" required>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Nama Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="parent">Parent Akun*</label>
                <div class="col-sm-10">
                    <select name="parent" class="form-control input-sm select2" id="parent" data-error="Wajib isi" required>
                        <option value="">Pilih Parent Akun</option>
                    </select>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Parent Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="type_akun">Type Akun*</label>
                <div class="col-sm-10">
                    <select name="type_akun" class="form-control input-sm select2" id="type_akun" data-error="Wajib isi" required>
                        <option value="">Pilih Type Akun</option>
                    </select>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Type Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="catatan">Catatan</label>
                <div class="col-sm-10">
                    <textarea class="form-control input-sm" id="catatan"
                        name="catatan" rows="3" data-minlength="" data-error="Wajib isi"
                        placeholder="Masukkan Catatan Akun"></textarea>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Catatan Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="header_1">Header 1*</label>
                <div class="col-sm-10">
                    <select name="header_1" class="form-control input-sm select2" id="header_1" data-error="Wajib isi" required>
                        <option value="">Pilih Header 1</option>
                    </select>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Type Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="header_2">Header 2*</label>
                <div class="col-sm-10">
                    <select name="header_2" class="form-control input-sm select2" id="header_2" data-error="Wajib isi" required>
                        <option value="">Pilih Header 1</option>
                    </select>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Type Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="header_3">Header 3*</label>
                <div class="col-sm-10">
                    <select name="header_3" class="form-control input-sm select2" id="header_3" data-error="Wajib isi" required>
                        <option value="">Pilih Header 1</option>
                    </select>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Type Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="status_akun"></label>
                <div class="col-sm-10 checkbox">
                    <label>
                        <input type="checkbox" id="status_akun" name="status_akun"
                            checked> <strong>Status</strong>
                    </label>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Status Akun</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>

            <button type="button" id="tombol_reset" onclick="refresh()" class="btn btn-default pull-left"><span
                    class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Segarkan</button>
            <button type="button" id="tombol_refresh" onclick="refresh()" class="btn btn-default pull-left sr-only"><span
                    class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Ulangi</button>
            <button type="button" id="tombol_buat" onclick="tambah_data()" class="btn btn-primary pull-right"><span
                    class="glyphicon glyphicon-plus" aria-hidden="true"></span> Simpan Data</button>
            <button type="button" id="tombol_ubah" onclick="ubah_data()"
                class="btn btn-warning pull-right sr-only"><span class="glyphicon glyphicon-pencil"
                    aria-hidden="true"></span> Ubah Data</button>
        </form>
        <br /><br />
        <hr />
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <h3>Data Master Akun</h3>
        <div class="">
            <button class="add-button btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Tambah Data</button>
            <button class="add-button btn btn-success"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Export Data</button>
        </div>
    </div>
    <table id="tabel" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Header 1</th>
                <th>Header 2</th>
                <th>Header 3</th>
            </tr>
        </thead>
        <tfoot>
        </tfoot>
    </table>

    @push('js')
        <script type="text/javascript">
            $('#submit').validator();

            function refresh() {
                location.reload();
            }
        </script>
    @endpush
@endsection
