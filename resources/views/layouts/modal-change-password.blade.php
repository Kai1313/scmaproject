<!-- Customs Modal Ganti Profil -->
<div class="modal" id="ganti_profil" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Ubah Profil</h4>
            </div>
            <div class="modal-body">
                <form id="submit_ganti_profil" data-toggle="validator" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama_pengguna2">Nama</label>
                        <input type="text" class="form-control input-sm" id="nama_pengguna2" name="nama_pengguna2"
                            data-minlength="1" maxlength="150" data-error="Wajib isi"
                            placeholder="Masukkan Nama Pengguna" required>
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Nama
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="alamat_pengguna2">Alamat</label>
                        <textarea class="form-control input-sm" id="alamat_pengguna2" name="alamat_pengguna2" rows="3" data-minlength="1"
                            data-error="Wajib isi" placeholder="Masukkan Alamat Pengguna" required></textarea>
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Alamat
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="telepon1_pengguna2">Telepon 1</label>
                        <input type="text" class="form-control input-sm" id="telepon1_pengguna2"
                            name="telepon1_pengguna2" data-minlength="1" maxlength="15"
                            data-error="Format inputan Telepon tidak sesuai" placeholder="Masukkan Telepon1 Pengguna"
                            required>
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Telepon1
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="telepon2_pengguna2">Telepon 2</label>
                        <input type="text" class="form-control input-sm" id="telepon2_pengguna2"
                            name="telepon2_pengguna2" data-minlength="" maxlength="15"
                            data-error="Format inputan Telepon tidak sesuai" placeholder="Masukkan Telepon2 Pengguna">
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Telepon2
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="email_pengguna2">Email</label>
                        <input type="email" class="form-control input-sm" id="email_pengguna2" name="email_pengguna2"
                            data-minlength="" maxlength="254" data-error="Format inputan Email tidak sesuai"
                            placeholder="Masukkan Email Pengguna">
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Email
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="foto_pengguna2">Foto</label>
                        <input type="file" accept=".jpg,.png,.gif,.jpeg" id="foto_pengguna2" name="foto_pengguna2"
                            class="sr-only" data-error="Pilih sebuah file">
                        <button type="button" class="btn btn-default"
                            onclick="document.getElementById('foto_pengguna2').click()"><span
                                class="glyphicon glyphicon-camera"></span> Pilih/Ambil Gambar</button><br />
                        <img id="preview_foto_pengguna2" style="padding-top: 5px" src="images/logo.png"
                            alt="-- BELUM ADA FILE YANG DIPILIH --" width="320" /><br />
                        <small class="form-text text-muted">Maksimal ukuran file 10 MB, Jenis file yang bisa di
                            Unggah hanya JPG, PNG, GIF & JPEG.</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="nomor_ktp_pengguna2">Nomor KTP</label>
                        <input type="text" class="form-control input-sm" id="nomor_ktp_pengguna2"
                            name="nomor_ktp_pengguna2" data-minlength="" maxlength="16" data-error="Wajib isi"
                            placeholder="Masukkan Nomor Ktp Pengguna">
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Nomor Ktp
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="tempat_lahir_pengguna2">Tempat Lahir</label>
                        <input type="text" class="form-control input-sm" id="tempat_lahir_pengguna2"
                            name="tempat_lahir_pengguna2" data-minlength="" maxlength="50" data-error="Wajib isi"
                            placeholder="Masukkan Tempat Lahir Pengguna">
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Tempat Lahir
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_lahir_pengguna2">Tanggal Lahir</label>
                        <input type="text" class="form-control input-sm tanggal" id="tanggal_lahir_pengguna2"
                            name="tanggal_lahir_pengguna2" data-minlength="" maxlength=""
                            data-error="Format inputan Tanggal tidak sesuai"
                            placeholder="Masukkan Tanggal Lahir Pengguna">
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Tanggal Lahir
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="jenis_kelamin_pengguna2">Jenis Kelamin</label>
                        <select class="form-control input-sm" id="jenis_kelamin_pengguna2"
                            name="jenis_kelamin_pengguna2" required>
                            <option value="1">Laki-Laki</option>
                            <option value="0">Perempuan</option>
                        </select>
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Grup
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <label for="keterangan_pengguna2">Keterangan</label>
                        <textarea class="form-control input-sm" id="keterangan_pengguna2" name="keterangan_pengguna2" rows="3"
                            data-minlength="" data-error="Wajib isi" placeholder="Masukkan Keterangan Pengguna"></textarea>
                        <small class="form-text text-muted sr-only">Keterangan tambahan untuk field Keterangan
                            Pengguna</small>
                        <div class="help-block with-errors"></div>
                    </div>
                    <div class="form-group">
                        <!--
                            <button type="reset" id="tombol_reset" class="btn btn-default pull-left"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Kosongkan</button>
                            -->
                        <button type="submit" id="tombol_ganti_profil" class="btn btn-primary pull-right"><span
                                class="glyphicon glyphicon-ok" aria-hidden="true"></span> Ganti Profil</button>
                    </div>
                    <br /><br />
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Customs Modal Ganti Password -->
<div class="modal" id="ganti_password" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Ubah Password</h4>
            </div>
            <div class="modal-body">
                <form id="submit_ganti_password" data-toggle="validator">
                    <div class="form-group">
                        <label for="password_ulangi2">Password</label>
                        <input type="password" class="form-control input-sm" id="password_ulangi2"
                            name="password_ulangi2" data-minlength="1" maxlength="40"
                            data-error="Minimal 1 Karakter" placeholder="Masukkan Password" required>
                    </div>
                    <div class="form-group">
                        <label for="password2">Ulangi Password</label>
                        <input type="password" class="form-control input-sm" id="password2" name="password2"
                            data-minlength="1" maxlength="40" data-match="#password_ulangi2"
                            data-match-error="Password Tidak Sama" placeholder="Masukkan Password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" id="tombol_ganti_password" class="btn btn-primary pull-right"><span
                                class="glyphicon glyphicon-ok" aria-hidden="true"></span> Ganti Password</button>
                    </div>
                    <br /><br />
                </form>
            </div>
        </div>
    </div>
</div>
