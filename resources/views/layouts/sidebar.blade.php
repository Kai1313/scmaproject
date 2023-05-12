<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu" data-widget="tree">
            <li class="nav-item" data-alias="beranda">
                <a href="{{ env('OLD_URL_ROOT') }}">
                    <i class="glyphicon glyphicon-home"></i> <span>Beranda</span>
                </a>
            </li>
            @if (checkAccessMenu('administrasi'))
                <li class="treeview" data-alias="administrasi">
                    <a href="#"><i class="glyphicon glyphicon-lock"></i> <span>Administrasi</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if (checkAccessMenu('grup_pengguna'))
                            <li data-alias="grup_pengguna">
                                <a href="{{ env('OLD_URL_ROOT') }}#grup_pengguna">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Grup Pengguna
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('pengguna'))
                            <li data-alias="pengguna">
                                <a href="{{ env('OLD_URL_ROOT') }}#pengguna">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Pengguna
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('menu'))
                            <li data-alias="menu">
                                <a href="{{ env('OLD_URL_ROOT') }}#menu">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Menu
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('akses_menu'))
                            <li data-alias="akses_menu">
                                <a href="{{ env('OLD_URL_ROOT') }}#akses_menu">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Akses Menu
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('akses_gudang'))
                            <li data-alias="akses_gudang">
                                <a href="{{ env('OLD_URL_ROOT') }}#akses_gudang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Akses Gudang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('token_pengguna'))
                            <li data-alias="token_pegguna">
                                <a href="{{ env('OLD_URL_ROOT') }}#token_pengguna">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Token
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('lokasi_pengguna'))
                            <li data-alias="lokasi_pengguna">
                                <a href="{{ env('OLD_URL_ROOT') }}#lokasi_pengguna">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Lokasi
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('periode'))
                            <li data-alias="periode">
                                <a href="{{ env('OLD_URL_ROOT') }}#periode">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Periode
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('otorisasi_nota'))
                            <li data-alias="otorisasi_nota">
                                <a href="{{ env('OLD_URL_ROOT') }}#otorisasi_nota">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Otorisasi Nota
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('kategori_konigurasi'))
                            <li data-alias="kategori_konfigurasi">
                                <a href="{{ env('OLD_URL_ROOT') }}#kategori_konfigurasi">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Kategori Konfigurasi
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('konfigurasi'))
                            <li data-alias="konfigurasi">
                                <a href="{{ env('OLD_URL_ROOT') }}#konfigurasi">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Konfigurasi
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if (checkAccessMenu('master_data'))
                <li class="treeview {{ in_array(request()->segment(1), ['master_biaya', 'master_wrapper']) ? 'active' : null }}"
                    data-alias="master_data">
                    <a href="#"><i class="glyphicon glyphicon-folder-close"></i> <span>Master Data</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if (checkAccessMenu('metode_penyusutan_pajak'))
                            <li data-alias="metode_penyusutan_pajak">
                                <a href="{{ env('OLD_URL_ROOT') }}#metode_penyusutan_pajak">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Metode Penyusutan Pajak
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('tipe_aktiva_tetap_pajak'))
                            <li data-alias="tipe_aktiva_tetap_pajak">
                                <a href="{{ env('OLD_URL_ROOT') }}#tipe_aktiva_tetap_pajak">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Tipe Aktiva Tetap Pajak
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('tipe_aktiva_tetap'))
                            <li data-alias="tipe_aktiva_tetap">
                                <a href="{{ env('OLD_URL_ROOT') }}#tipe_aktiva_tetap">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Tipe Aktiva Tetap
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('aktiva_tetap'))
                            <li data-alias="aktiva_tetap">
                                <a href="{{ env('OLD_URL_ROOT') }}#aktiva_tetap">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Aktiva Tetap
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('beban_produksi'))
                            <li data-alias="beban_produksi">
                                <a href="{{ env('OLD_URL_ROOT') }}#beban_produksi">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Beban Produksi
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('cc_setting'))
                            <li data-alias="cc_setting">
                                <a href="{{ env('OLD_URL_ROOT') }}#cc_setting">
                                    <i class="glyphicon glyphicon-option-vertical"></i> CC Setting
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('cc_master_mesin'))
                            <li data-alias="cc_master_mesin">
                                <a href="{{ env('OLD_URL_ROOT') }}#cc_master_mesin">
                                    <i class="glyphicon glyphicon-option-vertical"></i> CC Master Mesin
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('cabang'))
                            <li data-alias="cabang">
                                <a href="{{ env('OLD_URL_ROOT') }}#cabang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Cabang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('kategori_perkiraan'))
                            <li data-alias="kategori_perkiraan">
                                <a href="{{ env('OLD_URL_ROOT') }}#kategori_perkiraan">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Kategori Perkiraan
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('perkiraan'))
                            <li data-alias="perkiraan">
                                <a href="{{ env('OLD_URL_ROOT') }}#perkiraan">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Perkiraan / Chart of Account
                                    (CoA)
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('gudang'))
                            <li data-alias="gudang">
                                <a href="{{ env('OLD_URL_ROOT') }}#gudang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Gudang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('pelanggan'))
                            <li data-alias="pelanggan">
                                <a href="{{ env('OLD_URL_ROOT') }}#pelanggan">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Pelanggan / Customer
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('wilayah_pelanggan'))
                            <li data-alias="wilayah_pelanggan">
                                <a href="{{ env('OLD_URL_ROOT') }}#wilayah_pelanggan">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Wilayah Pelanggan
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('kategori_pelanggan'))
                            <li data-alias="kategori_pelanggan">
                                <a href="{{ env('OLD_URL_ROOT') }}#kategori_pelanggan">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Kategori Pelanggan
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('pemasok'))
                            <li data-alias="pemasok">
                                <a href="{{ env('OLD_URL_ROOT') }}#pemasok">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Supplier
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('salesman'))
                            <li data-alias="salesman">
                                <a href="{{ env('OLD_URL_ROOT') }}#salesman">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Salesman
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('ekspedisi'))
                            <li data-alias="ekspedisi">
                                <a href="{{ env('OLD_URL_ROOT') }}#ekspedisi">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Ekspedisi
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('bom'))
                            <li data-alias="bom">
                                <a href="{{ env('OLD_URL_ROOT') }}#bom">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Struktur Produk / Bill of
                                    Materials
                                    (BOM) (GDG)
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('bom_hpp'))
                            <li data-alias="bom_hpp">
                                <a href="{{ env('OLD_URL_ROOT') }}#bom_hpp">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Struktur Produk / Bill of
                                    Materials
                                    (BOM) (Rp)
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('jenis_transaksi'))
                            <li data-alias="jenis_transaksi">
                                <a href="{{ env('OLD_URL_ROOT') }}#jenis_transaksi">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Jenis Transaksi
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('jenis_pembayaran'))
                            <li data-alias="jenis_pembayaran">
                                <a href="{{ env('OLD_URL_ROOT') }}#jenis_pembayaran">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Jenis Pembayaran
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('bank'))
                            <li data-alias="bank">
                                <a href="{{ env('OLD_URL_ROOT') }}#bank">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Bank
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('mata_uang'))
                            <li data-alias="mata_uang">
                                <a href="{{ env('OLD_URL_ROOT') }}#mata_uang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Mata Uang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('rak'))
                            <li data-alias="rak">
                                <a href="{{ env('OLD_URL_ROOT') }}#rak">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Rak
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('master_biaya'))
                            <li class="{{ request()->segment(1) == 'master_biaya' ? 'active' : null }}"
                                data-alias="master_biaya">
                                <a href="{{ route('master-biaya') }}">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Master Biaya
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('master_wrapper'))
                            <li class="{{ request()->segment(1) == 'master_wrapper' ? 'active' : null }}"
                                data-alias="master_wrapper">
                                <a href="{{ route('master-wrapper') }}">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Master Wrapper
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if (checkAccessMenu('master_obat'))
                <li class="treeview" data-alias="master_obat">
                    <a href="#"><i class="glyphicon glyphicon-glass"></i> <span>Master Barang</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if (checkAccessMenu('jenis_barang'))
                            <li data-alias="jenis_barang">
                                <a href="{{ env('OLD_URL_ROOT') }}#jenis_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Jenis Barang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('satuan_barang'))
                            <li data-alias="satuan_barang">
                                <a href="{{ env('OLD_URL_ROOT') }}#satuan_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Satuan Barang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('kategori_barang'))
                            <li data-alias="kategori_barang">
                                <a href="{{ env('OLD_URL_ROOT') }}#kategori_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Kategori Barang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('barang'))
                            <li data-alias="barang">
                                <a href="{{ env('OLD_URL_ROOT') }}#barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Barang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('cc_barang'))
                            <li data-alias="cc_barang">
                                <a href="{{ env('OLD_URL_ROOT') }}#cc_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> CC Barang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('isi_satuan_barang'))
                            <li data-alias="isi_satuan_barang">
                                <a href="{{ env('OLD_URL_ROOT') }}#isi_satuan_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Konversi Satuan Barang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('relasi_jenis_barang'))
                            <li data-alias="relasi_jenis_barang">
                                <a href="{{ env('OLD_URL_ROOT') }}#relasi_jenis_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Relasi Satuan Barang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('barang_pemasok'))
                            <li data-alias="barang_pemasok">
                                <a href="{{ env('OLD_URL_ROOT') }}#barang_pemasok">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Harga Beli Per Supplier
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('barang_pelanggan'))
                            <li data-alias="barang_pelanggan">
                                <a href="{{ env('OLD_URL_ROOT') }}#barang_pelanggan">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Harga Jual Per Pelanggan
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('stok_minimal_barang_gudang'))
                            <li data-alias="stok_minimal_barang_gudang">
                                <a href="{{ env('OLD_URL_ROOT') }}#stok_minimal_barang_gudang">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Stok Minimal Barang
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('promo_barang_kategori_pelanggan'))
                            <li data-alias="promo_barang_kategori_pelanggan">
                                <a href="{{ env('OLD_URL_ROOT') }}#promo_barang_kategori_pelanggan">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Promo Jual Per Pelanggan
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('obat'))
                            <li data-alias="obat">
                                <a href="{{ env('OLD_URL_ROOT') }}#obat">
                                    <i class="glyphicon glyphicon-option-vertical"></i> Master Barang
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if (checkAccessMenu('transaksi'))
                <li class="treeview {{ in_array(request()->segment(1), ['purchase_requisitions', 'uang_muka_pembelian', 'qc_penerimaan_barang', 'kirim_ke_cabang', 'terima_dari_cabang', 'uang_muka_penjualan', 'terima_dari_gudang', 'pemakaian']) ? 'active' : null }}"
                    data-alias="transaksi">
                    <a href="#"><i class="glyphicon glyphicon-list-alt"></i> <span>Transaksi</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if (checkAccessMenu('pembelian_kepala'))
                            <li class="treeview {{ in_array(request()->segment(1), ['purchase_requisitions', 'uang_muka_pembelian', 'qc_penerimaan_barang']) ? 'active' : null }}"
                                data-alias="pembelian_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i>
                                    <span>Pembelian</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('purchase_requisitions'))
                                        <li class="nav-item {{ request()->segment(1) == 'purchase_requisitions' ? 'active' : null }}"
                                            data-alias="purchase_requisitions">
                                            <a href="{{ route('purchase-request') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i> Purchase
                                                Requisitions (PR)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('permintaan_pembelian'))
                                        <li data-alias="permintaan_pembelian">
                                            <a href="{{ env('OLD_URL_ROOT') }}#permintaan_pembelian">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Purchase Order (PO)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('pembelian'))
                                        <li data-alias="pembelian">
                                            <a href="{{ env('OLD_URL_ROOT') }}#pembelian">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Penerimaan Barang
                                                (PB)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('qc_penerimaan_barang'))
                                        <li
                                            class="nav-item {{ request()->segment(1) == 'qc_penerimaan_barang' ? 'active' : null }}">
                                            <a href="{{ route('qc_receipt') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>QC Penerimaan Barang
                                                (QC)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('qc_per_qr_code'))
                                        <li data-alias="qc_per_qr_code">
                                            <a href="{{ env('OLD_URL_ROOT') }}#qc_per_qr_code">
                                                <i class="glyphicon glyphicon-option-vertical"></i>QC Per QR Code (QCP)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('pembelian_invoice'))
                                        <li data-alias="pembelian_invoice">
                                            <a href="{{ env('OLD_URL_ROOT') }}#pembelian_invoice">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Invoice Pembelian
                                                (INV)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('retur_pembelian'))
                                        <li data-alias="retur_pembelian">
                                            <a href="{{ env('OLD_URL_ROOT') }}#retur_pembelian">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Retur Pembelian (RB)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('uang_muka_pembelian'))
                                        <li class="nav-item {{ request()->segment(1) == 'uang_muka_pembelian' ? 'active' : null }}"
                                            data-alias="uang_muka_pembelian">
                                            <a href="{{ route('purchase-down-payment') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i> Uang Muka
                                                Beli (UMB)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('dd_pembayaran_pembelian'))
                                        <li data-alias="dd_pembayaran_pembelian">
                                            <a href="{{ env('OLD_URL_ROOT') }}#dd_pembayaran_pembelian">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Pembayaran Pembelian
                                                (PP)
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (checkAccessMenu('persediaan_kepala'))
                            <li class="treeview {{ in_array(request()->segment(1), ['kirim_ke_cabang', 'terima_dari_cabang', 'terima_dari_gudang', 'pemakaian']) ? 'active' : null }}"
                                data-alias="persediaan_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i>
                                    <span>Persediaan</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('pindah_gudang'))
                                        <li data-alias="pindah_gudang">
                                            <a href="{{ env('OLD_URL_ROOT') }}#pindah_gudang">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Pisah Barang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('pindah_gudang2'))
                                        <li data-alias="pindah_gudang2">
                                            <a href="{{ env('OLD_URL_ROOT') }}#pindah_gudang2">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Pisah Barang (Qnt)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('cc_pisah_barang'))
                                        <li data-alias="cc_pisah_barang">
                                            <a href="{{ env('OLD_URL_ROOT') }}#cc_pisah_barang">
                                                <i class="glyphicon glyphicon-option-vertical"></i>CC Pisah Barang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('koreksi_stok'))
                                        <li data-alias="koreksi_stok">
                                            <a href="{{ env('OLD_URL_ROOT') }}#koreksi_stok">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Koreksi Stok
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('kirim_ke_cabang'))
                                        <li
                                            class="nav-item {{ request()->segment(1) == 'kirim_ke_cabang' ? 'active' : null }}">
                                            <a href="{{ route('send_to_branch') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Kirim Ke Cabang
                                                (TC-OUT)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('terima_dari_cabang'))
                                        <li
                                            class="nav-item {{ request()->segment(1) == 'terima_dari_cabang' ? 'active' : null }}">
                                            <a href="{{ route('received_from_branch') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Terima Dari Cabang
                                                (TC-IN)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('kirim_ke_gudang'))
                                        <li data-alias="terima_dari_gudang" class="nav-item">
                                            <a href="{{ env('OLD_URL_ROOT') }}#kirim_ke_gudang">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Kirim Ke Gudang
                                                (TG-OUT)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('terima_dari_gudang'))
                                        <li data-alias="terima_dari_gudang"
                                            class="nav-item {{ request()->segment(1) == 'terima_dari_gudang' ? 'active' : null }}">
                                            <a href="{{ route('received_from_warehouse') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Terima Dari Gudang
                                                (TG-IN)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('pemakaian_header'))
                                        <li data-alias="terima_dari_gudang"
                                            class="nav-item {{ request()->segment(1) == 'pemakaian' ? 'active' : null }}">
                                            <a href="{{ route('material_usage') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Pemakaian
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('stok_opname'))
                                        <li data-alias="stok_opname">
                                            <a href="{{ env('OLD_URL_ROOT') }}#stok_opname">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Stok Opname
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('rak_masuk'))
                                        <li data-alias="rak_masuk">
                                            <a href="{{ env('OLD_URL_ROOT') }}#rak_masuk">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Rak Masuk
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('rak_keluar'))
                                        <li data-alias="rak_keluar">
                                            <a href="{{ env('OLD_URL_ROOT') }}#rak_keluar">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Rak Keluar
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('gabung_barang'))
                                        <li data-alias="gabung_barang">
                                            <a href="{{ env('OLD_URL_ROOT') }}#gabung_barang">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Gabung Barang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('rak_masuk2'))
                                        <li data-alias="rak_masuk2">
                                            <a href="{{ env('OLD_URL_ROOT') }}#rak_masuk2">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Rak Masuk (Cepat)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('rak_keluar2'))
                                        <li data-alias="rak_keluar2">
                                            <a href="{{ env('OLD_URL_ROOT') }}#rak_keluar2">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Rak Keluar (Cepat)
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (checkAccessMenu('produksi_kepala'))
                            <li class="treeview" data-alias="produksi_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i>
                                    <span>Produksi</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('produksi'))
                                        <li data-alias="produksi">
                                            <a href="{{ env('OLD_URL_ROOT') }}#produksi">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Produksi
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('produksi2'))
                                        <li data-alias="produksi2">
                                            <a href="{{ env('OLD_URL_ROOT') }}#produksi2">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Produksi (Qnt)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('cc_produksi'))
                                        <li data-alias="cc_produksi">
                                            <a href="{{ env('OLD_URL_ROOT') }}#cc_produksi">
                                                <i class="glyphicon glyphicon-option-vertical"></i>CC Produksi
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('produksi_timbang_akhir'))
                                        <li data-alias="produksi_timbang_akhir">
                                            <a href="{{ env('OLD_URL_ROOT') }}#produksi_timbang_akhir">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Timbang Akhir
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (checkAccessMenu('penjualan_kepala'))
                            <li class="treeview {{ in_array(request()->segment(1), ['uang_muka_penjualan']) ? 'active' : null }}"
                                data-alias="penjualan_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i>
                                    <span>Penjualan</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('permintaan_penjualan'))
                                        <li data-alias="permintaan_penjualan">
                                            <a href="{{ env('OLD_URL_ROOT') }}#permintaan_penjualan">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Sales Order (SO)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('penjualan'))
                                        <li data-alias="penjualan">
                                            <a href="{{ env('OLD_URL_ROOT') }}#penjualan">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Delivery Order (DO)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('penjualan_faktur'))
                                        <li data-alias="penjualan_faktur">
                                            <a href="{{ env('OLD_URL_ROOT') }}#penjualan_faktur">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Faktur Penjualan
                                                (Rp)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('retur_penjualan'))
                                        <li data-alias="retur_penjualan">
                                            <a href="{{ env('OLD_URL_ROOT') }}#retur_penjualan">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Retur Penjualan
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('uang_muka_penjualan'))
                                        <li class="nav-item {{ request()->segment(1) == 'uang_muka_penjualan' ? 'active' : null }}"
                                            data-alias="uang_muka_penjualan">
                                            <a href="{{ route('sales-down-payment') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i> Uang Muka
                                                Jual (UMJ)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('dd_penerimaan_penjualan'))
                                        <li data-alias="dd_penerimaan_penjualan">
                                            <a href="{{ env('OLD_URL_ROOT') }}#dd_penerimaan_penjualan">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Penerimaan Penjualan
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (checkAccessMenu('kas_bank_kepala'))
                            <li class="treeview" data-alias="kas_bank_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i> <span>Kas &
                                        Bank</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('dd_kas_bank'))
                                        <li data-alias="dd_kas_bank">
                                            <a href="{{ env('OLD_URL_ROOT') }}#dd_kas_bank">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Penerimaan &
                                                Pembayaran
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('jurnal_voucher2'))
                                        <li data-alias="jurnal_voucher2">
                                            <a href="{{ env('OLD_URL_ROOT') }}#jurnal_voucher2">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Jurnal Voucher
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if (checkAccessMenu('laporan_1'))
                <li class="treeview {{ in_array(request()->segment(1), ['laporan_uang_muka_pembelian', 'laporan_qc_penerimaan', 'laporan_kirim_ke_cabang', 'laporan_terima_dari_cabang', 'laporan_kirim_ke_gudang', 'laporan_terima_dari_gudang', 'laporan_pemakaian', 'laporan_uang_muka_penjualan']) ? 'active' : null }}"
                    data-alias="laporan_1">
                    <a href="#"><i class="glyphicon glyphicon-stats"></i> <span>Laporan</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if (checkAccessMenu('laporan_pembelian_kepala'))
                            <li class="treeview {{ in_array(request()->segment(1), ['laporan_uang_muka_pembelian', 'laporan_qc_penerimaan']) ? 'active' : null }}"
                                data-alias="laporan_pembelian_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i>
                                    <span>Pembelian</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('laporan_po'))
                                        <li data-alias="laporan_po">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_po">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Purchase Order (PO)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_outstanding_po'))
                                        <li data-alias="laporan_outstanding_po">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_outstanding_po">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Outstanding PO
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_pembelian'))
                                        <li data-alias="laporan_pembelian">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_pembelian">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Pembelian
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_retur_pembelian'))
                                        <li data-alias="laporan_retur_pembelian">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_retur_pembelian">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Retur Pembelian
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_qc_penerimaan'))
                                        <li data-alias=""
                                            class="{{ request()->segment(1) == 'laporan_qc_penerimaan' ? 'active' : null }}">
                                            <a href="{{ route('report_qc-index') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>QC Penerimaan Barang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_uang_muka_pembelian'))
                                        <li data-alias=""
                                            class="{{ request()->segment(1) == 'laporan_uang_muka_pembelian' ? 'active' : null }}">
                                            <a href="{{ route('report_purchase_down_payment-index') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Uang Muka Pembelian
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (checkAccessMenu('laporan_persediaan_kepala'))
                            <li class="treeview {{ in_array(request()->segment(1), ['laporan_kirim_ke_cabang', 'laporan_terima_dari_cabang', 'laporan_kirim_ke_gudang', 'laporan_terima_dari_gudang', 'laporan_pemakaian']) ? 'active' : null }}"
                                data-alias="laporan_persediaan_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i>
                                    <span>Persediaan</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('laporan_pindah_gudang'))
                                        <li data-alias="laporan_pindah_gudang">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_pindah_gudang">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Pisah Barang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_koreksi_stok'))
                                        <li data-alias="laporan_koreksi_stok">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_koreksi_stok">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Koreksi Stok
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_stok'))
                                        <li data-alias="laporan_stok">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_stok">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Stok
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_kartu_stok'))
                                        <li data-alias="laporan_kartu_stok">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_stok">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Kartu Stok Per
                                                Barang Per
                                                Gudang
                                                [1]
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_kartu_stok2'))
                                        <li data-alias="laporan_kartu_stok2">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_stok2">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Kartu Stok Per
                                                Barang Per
                                                Gudang
                                                [2]
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_kartu_stok_all'))
                                        <li data-alias="laporan_kartu_stok_all">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_stok_all">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Lacak QR Code
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_outstanding_barang'))
                                        <li data-alias="laporan_outstanding_barang">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_outstanding_barang">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Outstanding QR Code
                                                Barang
                                                Per
                                                Gudang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_outstanding_qr_code_barang'))
                                        <li data-alias="laporan_outstanding_qr_code_barang">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_outstanding_qr_code_barang">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Outstanding QR Code
                                                Barang
                                                Per
                                                Gudang (Tanggal Akhir)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('outstanding_qr_code'))
                                        <li data-alias="outstanding_qr_code">
                                            <a href="{{ env('OLD_URL_ROOT') }}#outstanding_qr_code">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Outstanding QR Code
                                                barang
                                                (Gedangan)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_stok_minimal'))
                                        <li data-alias="laporan_stok_minimal">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_stok_minimal">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Stok Minimal
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_kesalahan'))
                                        <li data-alias="laporan_kesalahan">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_kesalahan">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Barang Belum Masuk
                                                Rak
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_persediaan'))
                                        <li data-alias="laporan_persediaan">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_persediaan">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Rincian Valuasi
                                                Persediaan
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_kirim_ke_cabang'))
                                        <li data-alias=""
                                            class="{{ request()->segment(1) == 'laporan_kirim_ke_cabang' ? 'active' : null }}">
                                            <a href="{{ route('report_send_to_branch-index') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Kirim Ke Cabang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_terima_dari_cabang'))
                                        <li data-alias=""
                                            class="{{ request()->segment(1) == 'laporan_terima_dari_cabang' ? 'active' : null }}">
                                            <a href="{{ route('report_received_from_branch-index') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Terima Dari Cabang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_kirim_ke_gudang'))
                                        <li data-alias=""
                                            class="{{ request()->segment(1) == 'laporan_kirim_ke_gudang' ? 'active' : null }}">
                                            <a href="{{ route('report_send_to_warehouse-index') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Kirim Ke Gudang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_terima_dari_gudang'))
                                        <li data-alias=""
                                            class="{{ request()->segment(1) == 'laporan_terima_dari_gudang' ? 'active' : null }}">
                                            <a href="{{ route('report_received_from_warehouse-index') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Terima Dari Gudang
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_pemakaian'))
                                        <li data-alias=""
                                            class="{{ request()->segment(1) == 'laporan_pemakaian' ? 'active' : null }}">
                                            <a href="{{ route('report_material_usage-index') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Pemakaian
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (checkAccessMenu('laporan_produksi_kepala'))
                            <li class="treeview" data-alias="laporan_produksi_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i>
                                    <span>Produksi</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('laporan_produksi'))
                                        <li data-alias="laporan_produksi">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_produksi">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Produksi
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_produksi_hpp'))
                                        <li data-alias="laporan_produksi_hpp">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_produksi_hpp">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Produksi (Rekap)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_produksi_beban'))
                                        <li data-alias="laporan_produksi_beban">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_produksi_beban">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Produksi (Beban &
                                                Tenaga
                                                Kerja)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_produksi_rekap'))
                                        <li data-alias="laporan_produksi_rekap">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_produksi_rekap">
                                                <i class="glyphicon glyphicon-option-vertical"></i>HPP Bahan Baku
                                                Produksi
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (checkAccessMenu('laporan_penjualan_kepala'))
                            <li class="treeview {{ in_array(request()->segment(1), ['laporan_uang_muka_penjualan']) ? 'active' : null }}"
                                data-alias="laporan_penjualan_kepala">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i>
                                    <span>Penjualan</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('laporan_so'))
                                        <li data-alias="laporan_so">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_so">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Sales Order (SO)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_outstanding_so'))
                                        <li data-alias="laporan_outstanding_so">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_outstanding_so">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Outstanding SO
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_penjualan_gdg'))
                                        <li data-alias="laporan_penjualan_gdg">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_penjualan_gdg">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Penjualan (GDG)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_penjualan'))
                                        <li data-alias="laporan_penjualan">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_penjualan">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Penjualan (Rp)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_retur_penjualan'))
                                        <li data-alias="laporan_retur_penjualan">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_retur_penjualan">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Retur Penjualan
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_kartu_piutang_rekap'))
                                        <li data-alias="laporan_kartu_piutang_rekap">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_piutang_rekap">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Sisa Piutang (Rekap)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_kartu_piutang'))
                                        <li data-alias="laporan_kartu_piutang">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_piutang">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Sisa Piutang
                                                (Detail)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_uang_muka_penjualan'))
                                        <li data-alias=""
                                            class="{{ request()->segment(1) == 'laporan_uang_muka_penjualan' ? 'active' : null }}">
                                            <a href="{{ route('report_sales_down_payment-index') }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Uang Muka Penjualan
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (checkAccessMenu('laporan_kas_bank'))
                            <li class="treeview" data-alias="laporan_kas_bank">
                                <a href="#"><i class="glyphicon glyphicon-arrow-right"></i> <span>Kas &
                                        Bank</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (checkAccessMenu('bb_laporan_buku_besar3'))
                                        <li data-alias="bb_laporan_buku_besar3">
                                            <a href="{{ env('OLD_URL_ROOT') }}#bb_laporan_buku_besar3">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Buku Besar
                                                (Accurate)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('bb_laporan_neraca'))
                                        <li data-alias="bb_laporan_neraca">
                                            <a href="{{ env('OLD_URL_ROOT') }}#bb_laporan_neraca">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Neraca (Accurate)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('bb_laporan_neraca_saldo'))
                                        <li data-alias="bb_laporan_neraca_saldo">
                                            <a href="{{ env('OLD_URL_ROOT') }}#bb_laporan_neraca_saldo">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Neraca Saldo
                                                (Accurate)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('bb_laporan_laba_rugi'))
                                        <li data-alias="bb_laporan_laba_rugi">
                                            <a href="{{ env('OLD_URL_ROOT') }}#bb_laporan_laba_rugi">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Laba - Rugi
                                                (Accurate)
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('mutasi_akun'))
                                        <li data-alias="mutasi_akun">
                                            <a href="{{ env('OLD_URL_ROOT') }}#mutasi_akun">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Mutasi Akun
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('buku_besar2'))
                                        <li data-alias="buku_besar2">
                                            <a href="{{ env('OLD_URL_ROOT') }}#buku_besar2">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Buku Besar 2
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('laporan_buku_besar_rinci'))
                                        <li data-alias="laporan_buku_besar_rinci">
                                            <a href="{{ env('OLD_URL_ROOT') }}#laporan_buku_besar_rinci">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Buku Besar - Rinci
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('giro'))
                                        <li data-alias="giro">
                                            <a href="{{ env('OLD_URL_ROOT') }}#giro">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Giro
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('jurnal_umum'))
                                        <li data-alias="jurnal_umum">
                                            <a href="{{ env('OLD_URL_ROOT') }}#jurnal_umum">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Jurnal Umum
                                            </a>
                                        </li>
                                    @endif
                                    @if (checkAccessMenu('jurnal_penyesuaian'))
                                        <li data-alias="jurnal_penyesuaian">
                                            <a href="{{ env('OLD_URL_ROOT') }}#jurnal_penyesuaian">
                                                <i class="glyphicon glyphicon-option-vertical"></i>Jurnal Penyesuaian
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if (checkAccessMenu('audit_audit'))
                <li class="treeview" data-alias="audit_audit">
                    <a href="#"><i class="glyphicon glyphicon-book"></i> <span>Audit</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if (checkAccessMenu('audit_konfigurasi'))
                            <li data-alias="audit_konfigurasi">
                                <a href="{{ env('OLD_URL_ROOT') }}#audit_konfigurasi">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Konfigurasi
                                </a>
                            </li>
                        @endif
                        @if (checkAccessMenu('audit_kartu_stok'))
                            <li data-alias="audit_kartu_stok">
                                <a href="{{ env('OLD_URL_ROOT') }}#audit_kartu_stok">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Kartu Stok
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            <li class="header">ACCOUNTING</li>
            <li class="treeview {{ request()->segment(1) == 'master' ? 'active' : null }}">
                <a href="#"><i class="fa fa-link"></i> <span>Master</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->segment(2) == 'coa' ? 'active' : null }}">
                        <a href="{{ route('master-coa') }}">Master CoA</a>
                    </li>
                    <li
                        class="{{ request()->segment(1) == 'master' && request()->segment(2) == 'slip' ? 'active' : null }}">
                        <a href="{{ route('master-slip') }}">Master Slip</a>
                    </li>
                </ul>
            </li>
            <li class="treeview {{ request()->segment(1) == 'transaction' ? 'active' : null }}">
                <a href="#"><i class="fa fa-link"></i> <span>Transaction</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li
                        class="{{ request()->segment(1) == 'transaction' && request()->segment(2) == 'slip' ? 'active' : null }}">
                        <a href="{{ route('transaction-general-ledger') }}">Jurnal Umum</a>
                    </li>
                    <li class="{{ request()->segment(2) == 'adjustment_ledger' ? 'active' : null }}">
                        <a href="{{ route('transaction-adjustment-ledger') }}">Jurnal Penyesuaian</a>
                    </li>
                    <li class="{{ request()->segment(2) == 'closing_journal' ? 'active' : null }}">
                        <a href="{{ route('transaction-closing-journal') }}">Jurnal Closing</a>
                    </li>
                </ul>
            </li>
            <li class="treeview {{ request()->segment(1) == 'report' ? 'active' : null }}">
                <a href="#"><i class="fa fa-link"></i> <span>Report</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li
                        class="{{ request()->segment(1) == 'report' && request()->segment(2) == 'slip' ? 'active' : null }}">
                        <a href="{{ route('report-slip') }}">Slip</a>
                    </li>
                    <li
                        class="{{ request()->segment(1) == 'report' && request()->segment(2) == 'giro' ? 'active' : null }}">
                        <a href="{{ route('report-giro') }}">Giro</a>
                    </li>
                    <li
                        class="{{ request()->segment(1) == 'report' && request()->segment(2) == 'general_ledger' ? 'active' : null }}">
                        <a href="{{ route('report-general-ledger') }}">Buku Besar</a>
                    </li>
                    <li
                        class="{{ request()->segment(1) == 'report' && request()->segment(2) == 'profit_loss' ? 'active' : null }}">
                        <a href="{{ route('report-profit-loss') }}">Laba Rugi</a>
                    </li>
                    <li
                        class="{{ request()->segment(1) == 'report' && request()->segment(2) == 'balance' ? 'active' : null }}">
                        <a href="{{ route('report-balance') }}">Neraca</a>
                    </li>
                </ul>
            </li>
        </ul>
    </section>
</aside>
