<aside class="main-sidebar">
    <section class="sidebar">>
        <ul class="sidebar-menu" data-widget="tree">
            <li class="nav-item">
                <a href="https://test1.scasda.my.id/development/v2/v2/#akses_menu">
                    <i class="fa fa-briefcase"></i> <span>Akses Menu</span>
                </a>
            </li>
            <li class="header">OPS</li>
            <li class="treeview {{ request()->segment(1) == 'master-ops' ? 'active' : null }}">
                <a href="#"><i class="fa fa-link"></i> <span>Master</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->segment(2) == 'biaya' ? 'active' : null }}">
                        <a href="{{ route('master-biaya') }}">Master Biaya</a>
                    </li>
                    <li class="{{ request()->segment(2) == 'wrapper' ? 'active' : null }}">
                        <a href="{{ route('master-wrapper') }}">Master Wrapper</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item {{ request()->segment(1) == 'permintaan-pembelian' ? 'active' : null }}">
                <a href="{{ route('purchase-request') }}">
                    <i class="fa fa-briefcase"></i> <span>Permintaan Pembelian</span>
                </a>
            </li>
            <li class="nav-item {{ request()->segment(1) == 'uang-muka-pembelian' ? 'active' : null }}">
                <a href="{{ route('purchase-down-payment') }}">
                    <i class="fa fa-briefcase"></i> <span>Uang Muka Pembelian</span>
                </a>
            </li>
            <li class="header">ACCOUNTING</li>
            <li class="nav-item {{ request()->segment(1) == '' ? 'active' : null }}">
                <a href="{{ route('welcome') }}"><i class="fa fa-briefcase"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="treeview {{ request()->segment(1) == 'master' ? 'active' : null }}">
                <a href="#"><i class="fa fa-link"></i> <span>Master</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->segment(2) == 'coa' ? 'active' : null }}">
                        <a href="{{ route('master-coa', 1) }}">Master CoA</a>
                    </li>
                    <li class="{{ request()->segment(2) == 'slip' ? 'active' : null }}">
                        <a href="{{ route('master-slip', 1) }}">Master Slip</a>
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
                    <li class="{{ request()->segment(2) == 'general_ledger' ? 'active' : null }}">
                        <a href="{{ route('transaction-general-ledger') }}">Jurnal Umum</a>
                    </li>
                    <li class="{{ request()->segment(2) == 'adjustment_ledger' ? 'active' : null }}">
                        <a href="{{ route('transaction-adjustment-ledger') }}">Jurnal Penyesuaian</a>
                    </li>
                </ul>
            </li>
            <li class="treeview ">
                <a href="#"><i class="fa fa-link"></i> <span>Report</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class=""><a href="#">Link in level 2</a></li>
                    <li><a href="#">Link in level 2</a></li>
                </ul>
            </li>
            <li class="header">Merge Menu</li>
            <li class="nav-item">
                <a href="{{ env('OLD_URL_ROOT') }}">
                    <i class="fa fa-briefcase"></i> <span>Beranda</span>
                </a>
            </li>
            <li class="treeview">
                <a href="#"><i class="fa fa-link"></i> <span>Administrasi</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#grup_pengguna">
                            <i class="glyphicon glyphicon-option-vertical"></i>Grup Pengguna
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#pengguna">
                            <i class="glyphicon glyphicon-option-vertical"></i>Pengguna
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#menu">
                            <i class="glyphicon glyphicon-option-vertical"></i> Menu
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#akses_menu">
                            <i class="glyphicon glyphicon-option-vertical"></i> Akses Menu
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#akses_gudang">
                            <i class="glyphicon glyphicon-option-vertical"></i>Akses Gudang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#token_pengguna">
                            <i class="glyphicon glyphicon-option-vertical"></i> Token
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#lokasi_pengguna">
                            <i class="glyphicon glyphicon-option-vertical"></i> Lokasi
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#periode">
                            <i class="glyphicon glyphicon-option-vertical"></i> Periode
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#otorisasi_nota">
                            <i class="glyphicon glyphicon-option-vertical"></i> Otorisasi Nota
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#kategori_konfigurasi">
                            <i class="glyphicon glyphicon-option-vertical"></i> Kategori Konfigurasi
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#konfigurasi">
                            <i class="glyphicon glyphicon-option-vertical"></i> Konfigurasi
                        </a>
                    </li>
                </ul>
            </li>
            <li class="treeview">
                <a href="#"><i class="fa fa-link"></i> <span>Master Data</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#metode_penyusutan_pajak">
                            <i class="glyphicon glyphicon-option-vertical"></i>Metode Penyusutan Pajak
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#tipe_aktiva_tetap_pajak">
                            <i class="glyphicon glyphicon-option-vertical"></i>Tipe Aktiva Tetap Pajak
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#tipe_aktiva_tetap">
                            <i class="glyphicon glyphicon-option-vertical"></i> Tipe Aktiva Tetap
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#aktiva_tetap">
                            <i class="glyphicon glyphicon-option-vertical"></i> Aktiva Tetap
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#beban_produksi">
                            <i class="glyphicon glyphicon-option-vertical"></i> Beban Produksi
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#cc_setting">
                            <i class="glyphicon glyphicon-option-vertical"></i> CC Setting
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#cc_master_mesin">
                            <i class="glyphicon glyphicon-option-vertical"></i> CC Master Mesin
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#cabang">
                            <i class="glyphicon glyphicon-option-vertical"></i> Cabang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#">
                            <i class="glyphicon glyphicon-option-vertical"></i> Master Wrapper
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#kategori_perkiraan">
                            <i class="glyphicon glyphicon-option-vertical"></i> Kategori Perkiraan
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#perkiraan">
                            <i class="glyphicon glyphicon-option-vertical"></i> Perkiraan / Chart of Account (CoA)
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#gudang">
                            <i class="glyphicon glyphicon-option-vertical"></i> Gudang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#pelanggan">
                            <i class="glyphicon glyphicon-option-vertical"></i> Pelanggan / Customer
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#wilayah_pelanggan">
                            <i class="glyphicon glyphicon-option-vertical"></i> Wilayah Pelanggan
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#kategori_pelanggan">
                            <i class="glyphicon glyphicon-option-vertical"></i> Kategori Pelanggan
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#pemasok">
                            <i class="glyphicon glyphicon-option-vertical"></i> Supplier
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#salesman">
                            <i class="glyphicon glyphicon-option-vertical"></i> Salesman
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#ekspedisi">
                            <i class="glyphicon glyphicon-option-vertical"></i> Ekspedisi
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#bom">
                            <i class="glyphicon glyphicon-option-vertical"></i> Struktur Produk / Bill of Materials
                            (BOM) (GDG)
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#bom_hpp">
                            <i class="glyphicon glyphicon-option-vertical"></i> Struktur Produk / Bill of Materials
                            (BOM) (Rp)
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#jenis_transaksi">
                            <i class="glyphicon glyphicon-option-vertical"></i> Jenis Transaksi
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#jenis_pembayaran">
                            <i class="glyphicon glyphicon-option-vertical"></i> Jenis Pembayaran
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#bank">
                            <i class="glyphicon glyphicon-option-vertical"></i> Bank
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#mata_uang">
                            <i class="glyphicon glyphicon-option-vertical"></i> Mata Uang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#rak">
                            <i class="glyphicon glyphicon-option-vertical"></i> Rak
                        </a>
                    </li>
                </ul>
            </li>
            <li class="treeview">
                <a href="#"><i class="fa fa-link"></i> <span>Master Barang</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#jenis_barang">
                            <i class="glyphicon glyphicon-option-vertical"></i>Jenis Barang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#satuan_barang">
                            <i class="glyphicon glyphicon-option-vertical"></i>Satuan Barang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#kategori_barang">
                            <i class="glyphicon glyphicon-option-vertical"></i> Kategori Barang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#barang">
                            <i class="glyphicon glyphicon-option-vertical"></i> Barang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#cc_barang">
                            <i class="glyphicon glyphicon-option-vertical"></i> CC Barang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#isi_satuan_barang">
                            <i class="glyphicon glyphicon-option-vertical"></i> Konversi Satuan Barang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#relasi_jenis_barang">
                            <i class="glyphicon glyphicon-option-vertical"></i> Relasi Satuan Barang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#barang_pemasok">
                            <i class="glyphicon glyphicon-option-vertical"></i> Harga Beli Per Supplier
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#barang_pelanggan">
                            <i class="glyphicon glyphicon-option-vertical"></i> Harga Jual Per Pelanggan
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#stok_minimal_barang_gudang">
                            <i class="glyphicon glyphicon-option-vertical"></i> Stok Minimal Barang
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#promo_barang_kategori_pelanggan">
                            <i class="glyphicon glyphicon-option-vertical"></i> Promo Jual Per Pelanggan
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#obat">
                            <i class="glyphicon glyphicon-option-vertical"></i> Master Barang
                        </a>
                    </li>
                </ul>
            </li>
            <li class="treeview">
                <a href="#"><i class="fa fa-link"></i> <span>Transaksi</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Pembelian</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            {{-- bermasalah --}}
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Purchase Requisitions (PR)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#permintaan_pembelian">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Purchase Order (PO)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#pembelian">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Penerimaan Barang (PB)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#pembelian_invoice">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Invoice Pembelian (Rp)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#cc_pembelian">
                                    <i class="glyphicon glyphicon-option-vertical"></i>CC Invoice Pembelian (Rp)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#retur_pembelian">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Retur Pembelian
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#dd_pembayaran_pembelian">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Pembayaran Pembelian
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Persediaan</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#pindah_gudang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Pisah Barang
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#pindah_gudang2">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Pisah Barang (Qnt)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#koreksi_stok">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Koreksi Stok
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#stok_opname">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Stok Opname
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#rak_masuk">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Rak Masuk
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#rak_keluar">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Rak Keluar
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#gabung_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Gabung Barang
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#rak_masuk2">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Rak Masuk (Cepat)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#rak_keluar2">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Rak Keluar (Cepat)
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Produksi</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#produksi">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Produksi
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#produksi2">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Produksi (Qnt)
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Penjualan</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#permintaan_penjualan">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Sales Order (SO)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#penjualan">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Delivery Order (DO)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#penjualan_faktur">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Faktur Penjualan (Rp)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#retur_penjualan">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Retur Penjualan
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#dd_penerimaan_penjualan">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Penerimaan Penjualan
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Kas & Bank</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#dd_kas_bank">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Penerimaan & Pembayaran
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#jurnal_voucher2">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Jurnal Voucher
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="treeview">
                <a href="#"><i class="fa fa-link"></i> <span>Laporan</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Pembelian</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_po">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Purchase Order (PO)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_outstanding_po">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Outstanding PO
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_pembelian">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Pembelian
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_retur_pembelian">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Retur Pembelian
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Persediaan</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_pindah_gudang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Pisah Barang
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_koreksi_stok">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Koreksi Stok
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_stok">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Stok
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_stok">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Kartu Stok Per Barang Per Gudang
                                    [1]
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_stok2">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Kartu Stok Per Barang Per Gudang
                                    [2]
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_stok_all">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Lacak QR Code
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_outstanding_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Outstanding QR Code Barang Per
                                    Gudang
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_outstanding_qr_code_barang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Outstanding QR Code Barang Per
                                    Gudang (Tanggal Akhir)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#outstanding_qr_code">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Outstanding QR Code barang
                                    (Gedangan)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_stok_minimal">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Stok Minimal
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_kesalahan">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Barang Belum Masuk Rak
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_persediaan">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Rincian Valuasi Persediaan
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Produksi</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_produksi">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Produksi
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_produksi_hpp">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Produksi (Rekap)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_produksi_beban">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Produksi (Beban & Tenaga Kerja)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_produksi_beban">
                                    <i class="glyphicon glyphicon-option-vertical"></i>HPP Bahan Baku Produksi
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Penjualan</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_so">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Sales Order (SO)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_outstanding_so">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Outstanding SO
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_penjualan_gdg">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Penjualan (GDG)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_penjualan">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Penjualan (Rp)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_retur_penjualan">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Retur Penjualan
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_piutang_rekap">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Sisa Piutang (Rekap)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_kartu_piutang">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Sisa Piutang (Detail)
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-link"></i> <span>Kas & Bank</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#bb_laporan_buku_besar3">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Buku Besar (Accurate)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#bb_laporan_neraca">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Neraca (Accurate)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#bb_laporan_neraca_saldo">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Neraca Saldo (Accurate)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#bb_laporan_laba_rugi">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Laba - Rugi (Accurate)
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#mutasi_akun">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Mutasi Akun
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#buku_besar2">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Buku Besar 2
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#laporan_buku_besar_rinci">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Buku Besar - Rinci
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#giro">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Giro
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#jurnal_umum">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Jurnal Umum
                                </a>
                            </li>
                            <li>
                                <a href="{{ env('OLD_URL_ROOT') }}#jurnal_penyesuaian">
                                    <i class="glyphicon glyphicon-option-vertical"></i>Jurnal Penyesuaian
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="treeview">
                <a href="#"><i class="fa fa-link"></i> <span>Audit</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#audit_konfigurasi">
                            <i class="glyphicon glyphicon-option-vertical"></i>Konfigurasi
                        </a>
                    </li>
                    <li>
                        <a href="{{ env('OLD_URL_ROOT') }}#audit_kartu_stok">
                            <i class="glyphicon glyphicon-option-vertical"></i>Kartu Stok
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </section>
</aside>
