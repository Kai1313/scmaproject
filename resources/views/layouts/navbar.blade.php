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
                <li class="dropdown user user-menu">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-user-circle"></i>
                        <!-- hidden-xs hides the username on small devices so only the image appears. -->
                        <span
                            class="hidden-xs">{{ Session::get('user') != null ? Session::get('user')->nama_pengguna : 'Login First' }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left" style="padding-top: 7px;">
                                <i class="fa fa-user-circle"></i>
                                <span
                                    class="">{{ Session::get('user') != null ? Session::get('user')->nama_pengguna : 'Login First' }}</span>
                            </div>
                            <div class="pull-right">
                                @if (Session::get('user') != null)
                                    <a href="{{ route('logout') }}" class="btn btn-default btn-flat">Sign out</a>
                                @endif
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
