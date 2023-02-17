<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">>
        <!-- Sidebar Menu -->
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
            <li class="nav-item {{ request()->segment(1) == 'dashboard' ? 'active' : null }}">
                <a href="{{ route('dashboard') }}">
                </a>
            </li>
            <!-- Optionally, you can add icons to the links -->
            <li class="treeview {{ request()->segment(1) == 'master' ? 'active' : null }}">
                <a href="#"><i class="fa fa-link"></i> <span>Master</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->segment(2) == 'coa' ? 'active' : null }}"><a
                            href="{{ route('master-coa') }}">Master CoA</a></li>
                    <li class="{{ request()->segment(2) == 'slip' ? 'active' : null }}"><a
                            href="{{ route('master-slip') }}">Master Slip</a></li>
                </ul>
            </li>
            <li class="treeview ">
                <a href="#"><i class="fa fa-link"></i> <span>Transaction</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class=""><a href="#">Link in level 2</a></li>
                    <li><a href="#">Link in level 2</a></li>
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
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
