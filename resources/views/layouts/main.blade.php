<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ asset('assets/img/logo.png') }}">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('includes.styles')
    @yield('addedStyles')
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dist/css/html5-form-validation.css') }}">
    <style>
        .wrapper-cabang {
            margin-top: 1rem;
        }

        #cover-spin {
            position: fixed;
            width: 100%;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            display: none;
        }

        #cover-spin>img {
            display: block;
            position: absolute;
            left: 48%;
            top: 40%;
        }

        .skin-yellow .main-header .navbar {
            background-color: #EBA925;
        }

        .skin-yellow .main-header .logo {
            background-color: white;
        }

        .skin-yellow .main-header .logo:hover {
            background-color: white;
        }

        .skin-yellow .sidebar-menu>li>.treeview-menu {
            background-color: #161f22 !important;
        }

        .skin-yellow .sidebar-menu>li>.treeview-menu>li:hover {
            background-color: #7d6129;
        }

        .skin-yellow .sidebar-menu>li>.treeview-menu>li>a {
            background-color: #161f22;
            padding: 10px 5px 10px 15px;
        }

        .skin-yellow .sidebar-menu>li>.treeview-menu>.menu-open {
            background-color: #161f22;
        }

        .skin-yellow .sidebar-menu>li>.treeview-menu>.treeview>.treeview-menu {
            background-color: #161f22;
        }

        .skin-yellow .sidebar-menu>li>.treeview-menu .skin-yellow .sidebar-menu>li>.treeview-menu>.treeview>.treeview-menu>li:hover {
            background-color: #7d6129;
        }

        .skin-yellow .sidebar-menu>li>.treeview-menu>.treeview>.treeview-menu>li>a {
            padding: 10px 5px 10px 15px;
        }

        .container-custom {
            background-color: white;
            margin: 15px 15px 0px 15px;
            padding: 15px;
        }

        .skin-yellow .main-header .navbar .sidebar-toggle:hover {
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
            .skin-yellow .main-header .navbar .dropdown-menu li a {
                color: black;
            }

            .skin-yellow .main-header .navbar .dropdown-menu li a:hover {
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
    </style>
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
            page. However, you can choose any other skin. Make sure you
            apply the skin class to the body tag so the changes take effect. -->
    <!-- <link rel="stylesheet" href="{{ asset('assets/dist/css/skins/skin-blue.min.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/skins/skin-yellow.min.css') }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-yellow fixed sidebar-mini">
    <div class="wrapper">
        @include('layouts.navbar')
        @include('layouts.sidebar')
        <div class="content-wrapper">
            @yield('header')

            @yield('main-section')
        </div>

        @include('layouts.footer')
        @include('layouts.control_sidebar')
        @yield('modal-section')
        <div class="control-sidebar-bg"></div>
    </div>
    <div id="cover-spin" style="display: none;"><img src="{{ asset('images/833.gif') }}" alt=""></div>
    @include('includes.scripts')
    @yield('addedScripts')
    <!-- AdminLTE App -->
    <script src="{{ asset('assets/dist/js/adminlte.js') }}"></script>
    <script src="{{ asset('assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    @yield('externalScripts')
</body>

</html>
