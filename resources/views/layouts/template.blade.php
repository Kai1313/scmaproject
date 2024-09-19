<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SiSCA V2 - SCMA</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    {{-- <link rel="stylesheet" href="/assets/bower_components/jquery-ui/jquery-ui.min.css"> --}}
    <link rel="stylesheet" href="/assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="/assets/dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link rel="stylesheet" href="/assets/bower_components/select2/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="/assets/bower_components/datatables.net/jquery-timepicker-addon-1.6.3/jquery-ui-timepicker-addon.min.css">
    <link rel="stylesheet" href="/assets/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="/assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link href="/assets/bower_components/datatables.net/Buttons-1.5.6/css/buttons.dataTables.min.css" rel="stylesheet">
    <style>
        .skin-blue .main-header .navbar {
            background-color: #EBA925;
        }

        .skin-blue .main-header .logo {
            background-color: white;
        }

        .skin-blue .main-header .logo:hover {
            background-color: white;
        }

        .skin-blue .sidebar-menu>li>.treeview-menu {
            background-color: #1e282c;
        }

        .skin-blue .sidebar-menu>li>.treeview-menu>li:hover {
            background-color: #7d6129;
        }

        .skin-blue .sidebar-menu>li>.treeview-menu>li>a {
            padding: 10px 5px 10px 15px;
        }

        .skin-blue .sidebar-menu>li>.treeview-menu>.menu-open {
            background-color: #161f22;
        }

        .skin-blue .sidebar-menu>li>.treeview-menu>.treeview>.treeview-menu {
            background-color: #161f22;
        }

        .skin-blue .sidebar-menu>li>.treeview-menu>.treeview>.treeview-menu>li:hover {
            background-color: #7d6129;
        }

        .skin-blue .sidebar-menu>li>.treeview-menu>.treeview>.treeview-menu>li>a {
            padding: 10px 5px 10px 15px;
        }

        .container-custom {
            background-color: white;
            margin: 15px 15px 0px 15px;
            padding: 15px;
        }

        .skin-blue .main-header .navbar .sidebar-toggle:hover {
            background-color: #f0b94a;
        }

        .search-header {
            max-width: 400px;
            min-width: 200px;
            margin-right: 10px;
        }

        .treeview-menu {
            transition: none;
        }

        .sidebar-menu,
        .main-sidebar .user-panel,
        .sidebar-menu>li.header {
            white-space: normal !important;
        }

        .treeview-menu li a {
            display: flex;
        }

        th {
            background-color: #fce7bc;
        }

        @media only screen and (max-width: 767px) {
            .skin-blue .main-header .navbar .dropdown-menu li a {
                color: black;
            }

            .skin-blue .main-header .navbar .dropdown-menu li a:hover {
                background-color: #e2e3e8;
            }

        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: black;
        }

        li .active {
            background-color: #7d6129;
        }

        .treeview-menu .treeview-menu {
            padding-left: 5px;
        }

        .position-left {
            margin-right: 5px;
            font-size: 20px;
        }

        .btn-index {
            display: inline-block;
            margin: 5px;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .select2 {
            width: 100% !important;
        }
    </style>
    <link rel="icon" href="../images/favicon.ico">
</head>

<body class="hold-transition skin-blue fixed sidebar-mini">
    <div class="wrapper">
        <header class="main-header">
            <a href="" class="logo" target="_blank">
                <img src="../images/logo.png" alt="" style="width:233%;" class="logo-mini">
                <img src="../images/logo_full.png" alt="" style="height:100%;" class="logo-lg">
            </a>
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <div class="navbar-custom-menu sr-only" id="menu_beranda_kanan">
                    <ul class="nav navbar-nav">
                        <li class="" style="margin-top:7px;">
                            <input type="text" id="kotak_pencarian3" name="kotak_pencarian3"
                                class="form-control search-header" placeholder="SCAN QR Code Barang">
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-user"></span>
                                <span id="patokan_nama_pengguna">Akun</span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="patokan_halaman_keluar">
                                    <a href="javascript:void(null)" onclick="tampil_masuk()">
                                        <span class="glyphicon glyphicon-log-in"></span> Masuk
                                    </a>
                                </li>
                                <li class="patokan_halaman_masuk">
                                    <a href="javascript:void(null)" onclick="$('#ganti_profil').modal('show')">
                                        <span class="glyphicon glyphicon-pencil"></span> Ubah Profil
                                    </a>
                                </li>
                                <li class="patokan_halaman_masuk">
                                    <a href="javascript:void(null)" onclick="$('#ganti_password').modal('show')">
                                        <span class="glyphicon glyphicon-pencil"></span> Ubah Password
                                    </a>
                                </li>
                                <li class="patokan_halaman_masuk"><a href="javascript:void(null)" onclick="keluar()">
                                        <span class="glyphicon glyphicon-log-out"></span> Keluar
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <aside class="main-sidebar">
            <section class="sidebar">
                <ul class="sidebar-menu" data-widget="tree" id="target-menu">

                </ul>
            </section>
        </aside>
        <div class="content-wrapper">
            <div class="container-custom" id="content">
                @yield('content')
            </div>
        </div>
        <footer class="main-footer">
            <div class="pull-right hidden-xs">v2</div>
            <strong>
                Copyright &copy; <span id="copyright_year"></span>
                <a href="https://sinarcemaramasabadi.co.id" target="_blank">PT Sinar Cemaramas Abadi</a>
                .
            </strong> All rights
            reserved.
        </footer>
        <div class="modal" id="modal_custom" tabindex="-1" role="dialog" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal_header_text">
                        </h4>
                    </div>
                    <div class="modal-body" id="modal_body_text">
                    </div>
                    <div class="modal-footer" id="modal_footer_text">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="masuk" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Halaman Masuk</h4>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username"
                                    placeholder="Masukkan Username Anda" value="">
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password"
                                    placeholder="Masukkan Password / Kata Sandi / Kata Kunci Anda" value="">
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="ingatkan_aku"> Ingatkan Aku
                                </label>
                            </div>
                            <div class="form-group">
                                <button type="reset" id="tombol_reset" class="btn btn-default pull-left">
                                    <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Kosongkan
                                </button>
                                <button type="submit" id="tombol_masuk" class="btn btn-primary pull-right">
                                    <span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Masuk
                                </button>
                            </div>
                            <br /><br />
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="ganti_profil" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Ubah Profil</h4>
                    </div>
                    <div class="modal-body">
                        <form id="submit_ganti_profil" data-toggle="validator" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="nama_pengguna2">Nama</label>
                                <input type="text" class="form-control input-sm" id="nama_pengguna2"
                                    name="nama_pengguna2" data-minlength="1" maxlength="150" data-error="Wajib isi"
                                    placeholder="Masukkan Nama Pengguna" required>
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Nama Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="alamat_pengguna2">Alamat</label>
                                <textarea class="form-control input-sm" id="alamat_pengguna2" name="alamat_pengguna2" rows="3"
                                    data-minlength="1" data-error="Wajib isi" placeholder="Masukkan Alamat Pengguna" required></textarea>
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Alamat Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="telepon1_pengguna2">Telepon 1</label>
                                <input type="text" class="form-control input-sm" id="telepon1_pengguna2"
                                    name="telepon1_pengguna2" data-minlength="1" maxlength="15"
                                    data-error="Format inputan Telepon tidak sesuai"
                                    placeholder="Masukkan Telepon1 Pengguna" required>
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Telepon1 Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="telepon2_pengguna2">Telepon 2</label>
                                <input type="text" class="form-control input-sm" id="telepon2_pengguna2"
                                    name="telepon2_pengguna2" data-minlength="" maxlength="15"
                                    data-error="Format inputan Telepon tidak sesuai"
                                    placeholder="Masukkan Telepon2 Pengguna">
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Telepon2 Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="email_pengguna2">Email</label>
                                <input type="email" class="form-control input-sm" id="email_pengguna2"
                                    name="email_pengguna2" data-minlength="" maxlength="254"
                                    data-error="Format inputan Email tidak sesuai"
                                    placeholder="Masukkan Email Pengguna">
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Email Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="foto_pengguna2">Foto</label>
                                <input type="file" accept=".jpg,.png,.gif,.jpeg" id="foto_pengguna2"
                                    name="foto_pengguna2" class="sr-only" data-error="Pilih sebuah file">
                                <button type="button" class="btn btn-default"
                                    onclick="document.getElementById('foto_pengguna2').click()">
                                    <span class="glyphicon glyphicon-camera"></span> Pilih/Ambil Gambar
                                </button>
                                <br />
                                <img id="preview_foto_pengguna2" style="padding-top: 5px" src="../images/logo.png"
                                    alt="-- BELUM ADA FILE YANG DIPILIH --" width="320" /><br />
                                <small class="form-text text-muted">
                                    Maksimal ukuran file 10 MB, Jenis file yang bisa di Unggah hanya JPG, PNG, GIF &
                                    JPEG.
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="nomor_ktp_pengguna2">Nomor KTP</label>
                                <input type="text" class="form-control input-sm" id="nomor_ktp_pengguna2"
                                    name="nomor_ktp_pengguna2" data-minlength="" maxlength="16"
                                    data-error="Wajib isi" placeholder="Masukkan Nomor Ktp Pengguna">
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Nomor Ktp Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="tempat_lahir_pengguna2">Tempat Lahir</label>
                                <input type="text" class="form-control input-sm" id="tempat_lahir_pengguna2"
                                    name="tempat_lahir_pengguna2" data-minlength="" maxlength="50"
                                    data-error="Wajib isi" placeholder="Masukkan Tempat Lahir Pengguna">
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Tempat Lahir Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_lahir_pengguna2">Tanggal Lahir</label>
                                <input type="text" class="form-control input-sm tanggal"
                                    id="tanggal_lahir_pengguna2" name="tanggal_lahir_pengguna2" data-minlength=""
                                    maxlength="" data-error="Format inputan Tanggal tidak sesuai"
                                    placeholder="Masukkan Tanggal Lahir Pengguna">
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Tanggal Lahir Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="jenis_kelamin_pengguna2">Jenis Kelamin</label>
                                <select class="form-control input-sm" id="jenis_kelamin_pengguna2"
                                    name="jenis_kelamin_pengguna2" required>
                                    <option value="1">Laki-Laki</option>
                                    <option value="0">Perempuan</option>
                                </select>
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Grup Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <label for="keterangan_pengguna2">Keterangan</label>
                                <textarea class="form-control input-sm" id="keterangan_pengguna2" name="keterangan_pengguna2" rows="3"
                                    data-minlength="" data-error="Wajib isi" placeholder="Masukkan Keterangan Pengguna"></textarea>
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Keterangan Pengguna
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="form-group">
                                <!--
                                <button type="reset" id="tombol_reset" class="btn btn-default pull-left"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Kosongkan</button>
                                -->
                                <button type="submit" id="tombol_ganti_profil" class="btn btn-primary pull-right">
                                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Ganti Profil
                                </button>
                            </div>
                            <br /><br />
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="ganti_password" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Ubah Password</h4>
                    </div>
                    <div class="modal-body">
                        <form id="submit_ganti_password" data-toggle="validator">
                            <div class="form-group">
                                <label for="username2">Username</label>
                                <input type="text" class="form-control input-sm" id="username2" name="username2"
                                    data-minlength="1" maxlength="20" data-error="Wajib isi"
                                    placeholder="Masukkan Username" required="" disabled="">
                                <small class="form-text text-muted sr-only">
                                    Keterangan tambahan untuk field Username
                                </small>
                                <div class="help-block with-errors"></div>
                            </div>
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
                                <!--
                                <button type="reset" id="tombol_reset" class="btn btn-default pull-left"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Kosongkan</button>
                                -->
                                <button type="submit" id="tombol_ganti_password" class="btn btn-primary pull-right">
                                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Ganti Password
                                </button>
                            </div>
                            <br /><br />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/assets/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="/assets/bower_components/jquery-ui/jquery-ui.min.js"></script>
    <script src="/assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <script src="/assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="/assets/dist/js/adminlte.js"></script>
    <script src="/assets/bower_components/bootstrap-validator-0.11.9/validator.min.js"></script>
    <script src="/assets/bower_components/datatables.net/jquery-timepicker-addon-1.6.3/jquery-ui-timepicker-addon.min.js">
    </script>
    <script src="/assets/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
    <script src="/assets/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/assets/bower_components/select2/dist/js/select2.full.min.js"></script>
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=Od43k81C"></script>
    <script src="/custom/menu.js"></script>
    <script src="/assets/dist/js/demo.js"></script>

    <script>
        let url_get_menu = "{{ url('get-menu') }}";

        $(document).ready(function() {
            getDataMenu(1);
            $('.select2').select2();
            $('.form-add').hide();
        });

        $('.add-button').click(function() {
            $('.form-add').slideDown();
        });
        $('.close-button').click(function() {
            $('.form-add').slideUp();
        });
    </script>

    @stack('js')
</body>

</html>
