<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html translate="no">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
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

        .skin-yellow .sidebar-menu .treeview-menu>li.active>a,
        .skin-yellow .sidebar-menu .treeview-menu>li>a:hover {
            background-color: #7d6129;
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

            .navbar-custom-menu>.navbar-nav>li>.dropdown-menu {
                right: 0 !important;
            }

            .skin-yellow .main-header .navbar .dropdown-menu li a {
                color: #333 !important;
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

        .disabled {
            pointer-events: none;
        }

        .w-full {
            width: 100%;
        }

        .mb-3 {
            margin-bottom: 1.25rem;
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
        <button style="display:none;" class="play-voice"></button>
    </div>
    <div id="cover-spin" style="display: none;"><img src="{{ asset('images/833.gif') }}" alt=""></div>
    @include('layouts.modal-change-password')
    @include('includes.scripts')

    <div class="modal" id="modal_camera" tabindex="-1" role="dialog" data-backdrop="static"
        style="overflow-x: hidden;overflow-y: auto;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button"
                        onclick="$('#html5-qrcode-button-camera-stop').click();$('#modal_camera').modal('hide');"
                        class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        Scan QR Code
                    </h4>
                </div>
                <div class="modal-body" id="modal_body_text">
                    <div id="qr-reader" style="width:100%"></div>
                    <div id="qr-reader-results"></div>
                </div>
                <div class="modal-footer" id="modal_footer_text">
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="modal_custom" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>

    @yield('addedScripts')
    <!-- AdminLTE App -->
    <script src="{{ asset('assets/dist/js/adminlte.js') }}"></script>
    <script src="{{ asset('assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    @if (env('FIREBASE_STATUS') == true)
        <script src="https://www.gstatic.com/firebasejs/8.4.3/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/8.4.0/firebase-messaging.js"></script>
        <script>
            let siteMain = '{{ url('/') }}';
        </script>
        <script src="{{ asset('js/firebaseinit.js') }}"></script>
        <script src="https://code.responsivevoice.org/responsivevoice.js?key=Od43k81C"></script>
    @endif
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
    <script>
        $('#modal-scanner-main').click(function() {
            $('#modal_camera').modal('show')
            jalankan_scanner()
        })

        function docReady(fn) {
            // see if DOM is already available
            if (document.readyState === "complete" ||
                document.readyState === "interactive") {
                // call on next available tick
                setTimeout(fn, 1);
            } else {
                document.addEventListener("DOMContentLoaded", fn);
            }
        }

        function jalankan_scanner() {
            docReady(function() {
                var resultContainer = document.getElementById('qr-reader-results');
                var lastResult, countResults = 0;

                function onScanSuccess(decodedText, decodedResult) {
                    if (decodedText !== lastResult) {
                        ++countResults;
                        lastResult = decodedText;
                        // Handle on success condition with the decoded message.
                        //console.log(`Scan result ${decodedText}`, decodedResult);
                        $('#kotak_pencarian3').val(`${decodedText}`);
                        $('#html5-qrcode-button-camera-stop').click();
                        $('#modal_camera').modal('hide');
                        $('#kotak_pencarian3').keyup();
                        /*
                        html5QrCode.stop().then((ignore) => {
                            // QR Code scanning is stopped.
                            $('#modal_camera').modal('hide');
                        }).catch((err) => {
                            // Stop failed, handle it.
                            alert('Gagal Stop Kamera');
                        });
                        */
                    }
                }

                var html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader", {
                        fps: 10,
                        qrbox: 250
                    });
                html5QrcodeScanner.render(onScanSuccess);
            });
        }

        $('#kotak_pencarian3').on('paste', function() {
            $('#kotak_pencarian3').keyup();
        });

        $('#kotak_pencarian3').keyup(delay(function(e) {
            $('#cover-spin').show()
            if (this.value != "") {
                if (this.value.length == 5) {
                    $.ajax({
                        url: '{{ env('OLD_ASSET_ROOT') }}actions/cek_rak.php?id=' +
                            this.value.replace(/^0+/, '') +
                            "&token_pengguna={{ session()->get('token') }}&nc=" + (new Date())
                            .getTime(),
                        type: "GET",
                        success: function(pesan) {
                            var hasil = "";
                            var data_combobox = "";
                            var data_rak = "";
                            $.each(pesan, function(i, n) {
                                hasil = n["hasil"];
                                nama_gudangX = n["nama_gudangX"];
                                nama_gudang2X = n["nama_gudang2X"];
                                nama_gudang = n["nama_gudang"];
                                nama_gudang2 = n["nama_gudang2"];
                                nama_barang = n["nama_barang"];
                                nama_barang2 = n["nama_barang2"];
                                nama_satuan_barang = n["nama_satuan_barang"];
                                nama_satuan_barang2 = n["nama_satuan_barang2"];
                                debit_kartu_stok = n["debit_kartu_stok"];
                                debit_kartu_stok2 = n["debit_kartu_stok2"];
                                kredit_kartu_stok = n["kredit_kartu_stok"];
                                kredit_kartu_stok2 = n["kredit_kartu_stok2"];
                                total_kartu_stok = n["total_kartu_stok"];
                                total_kartu_stok2 = n["total_kartu_stok2"];
                                rak_kartu_stok = n["rak_kartu_stok"];
                                rak_kartu_stok2 = n["rak_kartu_stok2"];
                                //mtotal_debit_kartu_stok = n["mtotal_debit_kartu_stok"];
                                //mtotal_debit_kartu_stok2 = n["mtotal_debit_kartu_stok2"];
                                //mtotal_kredit_kartu_stok = n["mtotal_kredit_kartu_stok"];
                                //mtotal_kredit_kartu_stok2 = n["mtotal_kredit_kartu_stok2"];
                                sg_kartu_stok = n["sg_kartu_stok"];
                                sg_kartu_stok2 = n["sg_kartu_stok2"];
                                batch_kartu_stok = n["batch_kartu_stok"];
                                batch_kartu_stok2 = n["batch_kartu_stok2"];
                                //alert(nama_gudang + nama_gudang2 + '\n' + nama_barang + nama_barang2 + '\n' + nama_satuan_barang + nama_satuan_barang2 + '\n' + debit_kartu_stok + debit_kartu_stok2 + '\n' + kredit_kartu_stok + kredit_kartu_stok2 + '\n' + total_kartu_stok + total_kartu_stok2 + '\n' + rak_kartu_stok + rak_kartu_stok2 + '\n' + mtotal_debit_kartu_stok + mtotal_debit_kartu_stok2 + '\n' + mtotal_kredit_kartu_stok + mtotal_kredit_kartu_stok2 + '\n' + sg_kartu_stok + sg_kartu_stok2);

                                //data_rak = data_rak + nama_gudangX + nama_gudang2X + '<br />' + nama_gudang + nama_gudang2 + '<br />' + nama_barang + nama_barang2 + '<br />' + nama_satuan_barang + nama_satuan_barang2 + '<br />' + debit_kartu_stok + debit_kartu_stok2 + '<br />' + kredit_kartu_stok + kredit_kartu_stok2 + '<br />' + total_kartu_stok + total_kartu_stok2 + '<br />' + rak_kartu_stok + rak_kartu_stok2 + '<br />' + mtotal_debit_kartu_stok + mtotal_debit_kartu_stok2 + '<br />' + mtotal_kredit_kartu_stok + mtotal_kredit_kartu_stok2 + '<br />' + sg_kartu_stok + sg_kartu_stok2 + '<hr />';
                                //data_rak = data_rak + nama_gudang + nama_gudang2 + '<br />' + nama_barang + nama_barang2 + '<br />' + nama_satuan_barang + nama_satuan_barang2 + '<br />' + debit_kartu_stok + debit_kartu_stok2 + '<br />' + kredit_kartu_stok + kredit_kartu_stok2 + '<br />' + total_kartu_stok + total_kartu_stok2 + '<br />' + rak_kartu_stok + rak_kartu_stok2 + '<br />' + mtotal_debit_kartu_stok + mtotal_debit_kartu_stok2 + '<br />' + mtotal_kredit_kartu_stok + mtotal_kredit_kartu_stok2 + '<br />' + sg_kartu_stok + sg_kartu_stok2 + '<hr />';
                                data_rak = data_rak + nama_gudang + nama_gudang2 +
                                    '<br />' + nama_barang + nama_barang2 + '<br />' +
                                    nama_satuan_barang + nama_satuan_barang2 + '<br />' +
                                    debit_kartu_stok + debit_kartu_stok2 + '<br />' +
                                    kredit_kartu_stok + kredit_kartu_stok2 + '<br />' +
                                    total_kartu_stok + total_kartu_stok2 + '<br />' +
                                    rak_kartu_stok + rak_kartu_stok2 + '<br />' +
                                    sg_kartu_stok + sg_kartu_stok2 + '<hr />';
                            });

                            \
                            'laporan_kartu_stok_all\', \'patokan_pencarian_qr_code\', \'' +
                            nama_gudang2 + '\', \'' + nama_barang2 + ' ' + nama_gudang2 + '\'

                            let modal = $("#modal_custom")
                            modal.modal("show");
                            modal.find(".modal-title").html("Cek Rak");
                            if (hasil == 0) {
                                modal.find(".modal-body").html("Tidak Barang Pada Rak");
                            } else {
                                modal.find(".modal-body").html(data_rak);
                            }

                            modal.find(".modal-footer").html(
                                "<button type=\"button\" class=\"btn btn-primary\" data-dismiss=\"modal\">OK!</button>"
                            );

                            $('#cover-spin').hide()
                        },
                        error: function(error) {
                            console.log(error)
                            $('#cover-spin').hide()
                        }
                    });
                    //} else if (this.value.length == 10 && !isNaN(this.value)) {
                } else if (this.value.length == 10) {
                    $.ajax({
                        url: "{{ env('OLD_ASSET_ROOT') }}actions/cek_barang.php?id=" +
                            this.value.replace(/^0+/, '') +
                            "&token_pengguna={{ session()->get('token') }}&nc=" + (new Date())
                            .getTime(),
                        type: "GET",
                        success: function(pesan) {
                            var hasil = "";
                            var data_combobox = "";
                            var data_barang = "";
                            $.each(pesan, function(i, n) {
                                nama_gudangX = n["nama_gudangX"];
                                nama_gudang2X = n["nama_gudang2X"];
                                nama_gudang = n["nama_gudang"];
                                nama_gudang2 = n["nama_gudang2"];
                                nama_barang = n["nama_barang"];
                                nama_barang2 = n["nama_barang2"];
                                nama_satuan_barang = n["nama_satuan_barang"];
                                nama_satuan_barang2 = n["nama_satuan_barang2"];
                                debit_kartu_stok = n["debit_kartu_stok"];
                                debit_kartu_stok2 = n["debit_kartu_stok2"];
                                kredit_kartu_stok = n["kredit_kartu_stok"];
                                kredit_kartu_stok2 = n["kredit_kartu_stok2"];
                                total_kartu_stok = n["total_kartu_stok"];
                                total_kartu_stok2 = n["total_kartu_stok2"];
                                rak_kartu_stok = n["rak_kartu_stok"];
                                rak_kartu_stok2 = n["rak_kartu_stok2"];

                                //mtotal_debit_kartu_stok = n["mtotal_debit_kartu_stok"];
                                //mtotal_debit_kartu_stok2 = n["mtotal_debit_kartu_stok2"];
                                //mtotal_kredit_kartu_stok = n["mtotal_kredit_kartu_stok"];
                                //mtotal_kredit_kartu_stok2 = n["mtotal_kredit_kartu_stok2"];
                                //mtotal_kartu_stok = n["mtotal_kartu_stok"];
                                //mtotal_kartu_stok2 = n["mtotal_kartu_stok2"];

                                batch_kartu_stok = n["batch_kartu_stok"];
                                batch_kartu_stok2 = n["batch_kartu_stok2"];
                                sg_kartu_stok = n["sg_kartu_stok"];
                                sg_kartu_stok2 = n["sg_kartu_stok2"];
                                be_kartu_stok = n["be_kartu_stok"];
                                be_kartu_stok2 = n["be_kartu_stok2"];
                                ph_kartu_stok = n["ph_kartu_stok"];
                                ph_kartu_stok2 = n["ph_kartu_stok2"];
                                warna_kartu_stok = n["warna_kartu_stok"];
                                warna_kartu_stok2 = n["warna_kartu_stok2"];
                                bentuk_master_qr_code = n["bentuk_master_qr_code"];
                                bentuk_master_qr_code2 = n["bentuk_master_qr_code2"];

                                status_qc_master_qr_code = n["status_qc_master_qr_code"];
                                status_qc_master_qr_code2 = n["status_qc_master_qr_code2"];

                                weight = n["weight"];
                                weight2 = n["weight2"];
                                zak = n["zak"];
                                zak2 = n["zak2"];
                                weight_zak = n["weight_zak"];
                                weight_zak2 = n["weight_zak2"];

                                data_barang = data_barang + nama_gudangX + nama_gudang2X +
                                    '<br />' + nama_gudang + nama_gudang2 + '<br />' +
                                    nama_barang + nama_barang2 + '<br />' +
                                    nama_satuan_barang + nama_satuan_barang2 + '<br />' +
                                    debit_kartu_stok + debit_kartu_stok2 + '<br />' +
                                    kredit_kartu_stok + kredit_kartu_stok2 + '<br />' +
                                    total_kartu_stok + total_kartu_stok2 + '<br />' +
                                    rak_kartu_stok + rak_kartu_stok2 + '<br />' +
                                    sg_kartu_stok + sg_kartu_stok2 + '<br />' +
                                    batch_kartu_stok + batch_kartu_stok2 + '<br />' +
                                    be_kartu_stok + be_kartu_stok2 + '<br />' +
                                    ph_kartu_stok + ph_kartu_stok2 + '<br />' +
                                    warna_kartu_stok + warna_kartu_stok2 + '<br />' +
                                    bentuk_master_qr_code + bentuk_master_qr_code2 +
                                    '<br />' + status_qc_master_qr_code +
                                    status_qc_master_qr_code2 + '<br />' + weight +
                                    weight2 + '<br />' + zak + zak2 + '<br />' +
                                    weight_zak + weight_zak2 +
                                    '<br /><br /><a href="javascript:void(null)" data-dismiss=\"modal\" onclick="data_master2_umum_new_tab(\'laporan_kartu_stok_all\', \'patokan_pencarian_qr_code\', \'' +
                                    nama_gudang2 + '\', \'' + nama_barang2 + ' ' +
                                    nama_gudang2 + '\');">Lacak QR Code</a>';
                            });

                            let modal = $("#modal_custom")
                            modal.modal("show");
                            modal.find(".modal-title").html("Cek Barang");
                            modal.find(".modal-body").html(data_barang);
                            modal.find(".modal-footer").html(
                                "<button type=\"button\" class=\"btn btn-primary\" data-dismiss=\"modal\">OK!</button>"
                            );
                            $('#cover-spin').hide()
                        },
                        error: function(error) {
                            console.log(error)
                            $('#cover-spin').hide()
                        }
                    });
                    //cetak_kartu_stok(this.value.replace(/^0+/, ''));
                }
                //$('#kotak_pencarian').val("");
                //$('#kotak_pencarian3').val('');
                //$('#kotak_pencarian3').focus();
            }
        }, 500));

        function delay(callback, ms) {
            var timer = 0;
            return function() {
                var context = this,
                    args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function() {
                    callback.apply(context, args);
                }, ms || 0);
            };
        }

        function data_master2_umum_new_tab(detail_view, primary_key, id, title_text) {
            //var win = window.open(protocol + '://' + host + '/' + directory + '/', '_blank');
            var win = window.open('{{ env('OLD_ASSET_ROOT') }}v2/#laporan_kartu_stok_all', '_blank');
            win.test = function() {
                win.focus();
                setTimeout(function() {
                    win.data_master2_umum(detail_view, primary_key, id, title_text);
                }, 2000);

            }
            win.test();
        }

        function data_master2_umum(detail_view, primary_key, id, title_text) {
            //kursor_buka();
            document.title = title_text;
            $.ajax({
                url: "{{ env('OLD_ASSET_ROOT') }}views/" + detail_view + ".html?nc=" + (
                    new Date()).getTime(),
                success: function(pesan) {
                    $("#content").html(pesan + "<script type=\"text/javascript\">var " + primary_key + "='" +
                        id + "';$('#" + primary_key + "').append(new Option('PROTECTED', '" + id +
                        "'));$('#" + primary_key + "').val('" + id + "');<\/script>");
                },
                error: function(pesan) {
                    alert("ERROR redirect ke data_master2_umum");
                }
            });
        }
    </script>
    @yield('externalScripts')
</body>

</html>
