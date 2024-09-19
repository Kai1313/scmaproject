<!-- Main Header -->
<header class="main-header">

    <!-- Logo -->
    <a href="{{ env('OLD_URL_ROOT') }}" target="_blank" class="logo" style="background-color: #fff;">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><img src="{{ asset('assets/img/logo.png') }}" alt="Logo"></span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg" style="height: 100%"><img src="{{ asset('assets/img/logo_full.png') }}"
                alt="Logo"></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- User Account Menu -->
                <li class="" style="margin-top:7px;">
                    <button type="button" style="height:35px;width:35px" id="modal-scanner-main">
                        <span class="glyphicon glyphicon-qrcode" style="font-size: 24px;"></span>
                    </button>
                </li>
                <li class="" style="margin-top:7px;width: 168px">
                    <input type="text" id="kotak_pencarian3" name="kotak_pencarian3"
                        class="form-control search-header" placeholder="SCAN QR Code Barang">
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                        aria-expanded="true">
                        <span class="glyphicon glyphicon-user"></span>
                        <span
                            id="patokan_nama_pengguna">{{ Session::get('user') != null ? Session::get('user')->nama_pengguna : 'Login First' }}</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        @if (session()->has('user'))
                            <li class="patokan_halaman_masuk">
                                <a href="javascript:void(0)" onclick="location.reload(true)">
                                    <span class="glyphicon glyphicon-refresh"></span> Reload
                                </a>
                            </li>
                            <li class="patokan_halaman_masuk">
                                <a href="javascript:void(0)" onclick="$('#ganti_profil').modal('show')">
                                    <span class="glyphicon glyphicon-pencil"></span> Ubah Profil
                                </a>
                            </li>
                            <li class="patokan_halaman_masuk">
                                <a href="javascript:void(0)" onclick="$('#ganti_password').modal('show')">
                                    <span class="glyphicon glyphicon-pencil"></span> Ubah Password
                                </a>
                            </li>
                            <li class="patokan_halaman_masuk">
                                <a href="{{ route('logout') }}">
                                    <span class="glyphicon glyphicon-log-out"></span> Keluar
                                </a>
                            </li>
                        @else
                            <li class="patokan_halaman_keluar">
                                <a href="{{ env('OLD_URL_ROOT') }}">
                                    <span class="glyphicon glyphicon-log-in"></span> Masuk
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
