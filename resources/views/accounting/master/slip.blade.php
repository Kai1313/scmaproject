@extends('layouts.template')

@section('content')
    <div class="form-add">
        <div class="d-flex justify-content-between align-items-center">
            <h3 id="header_form">Buat Slip Baru</h3>
            <div class="">
                <button class="close-button btn btn-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Close</button>
            </div>
        </div>
        <br />
        <form id="submit" class="form-horizontal" data-toggle="validator" enctype="multipart/form-data">
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="kode_slip">Kode Slip*</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control input-sm" id="kode_slip"
                        name="kode_slip" data-minlength="1" data-error="Wajib isi"
                        placeholder="Masukkan Kode Slip" required>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Kode Slip</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="nama_slip">Nama Slip*</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control input-sm" id="nama_slip"
                        name="nama_slip" data-minlength="1" maxlength="150" data-error="Wajib isi"
                        placeholder="Masukkan Nama Slip" required>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Nama Slip</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="jenis_slip">Jenis Slip*</label>
                <div class="col-sm-10">
                    <select name="jenis_slip" class="form-control input-sm select2" id="jenis_slip" data-error="Wajib isi" required>
                        <option value="">Pilih Slip</option>
                    </select>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Slip</small>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="form-group form-group-sm">
                <label class="col-sm-2" for="akun_id">Akun*</label>
                <div class="col-sm-10">
                    <select name="akun_id" class="form-control input-sm select2" id="akun_id" data-error="Wajib isi" required>
                        <option value="">Pilih Akun</option>
                    </select>
                    <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Akun</small>
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
            <button class="add-button btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Tambah Slip</button>
            <button class="add-button btn btn-success"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Export Slip</button>
        </div>
    </div>
    <table id="tabel" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th>Kode Slip</th>
                <th>Nama Slip</th>
                <th>Jenis Slip</th>
                <th>Akun</th>
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
