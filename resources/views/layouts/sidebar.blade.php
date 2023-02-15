<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">>
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">ACCOUNTING</li>
            <li  class="nav-item {{ request()->segment(1) == 'dashboard' ? 'active' : null }}">
                <a href="{{ route('dashboard') }}"><i class="fa fa-briefcase"></i> <span>Dashboard</span>
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
                    <li class="{{ request()->segment(2) == 'coa' ? 'active' : null }}"><a href="{{ route('master-coa') }}">Master CoA</a></li>
                    <li class="{{ request()->segment(2) == 'slip' ? 'active' : null }}"><a href="{{ route('master-slip') }}">Master Slip</a></li>
                </ul>
            </li>
            <li class="treeview ">
                <a href="#"><i class="fa fa-link"></i> <span>Transaction</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->segment(2) == 'slip' ? 'active' : null }}"><a href="{{ route('transaction-general-ledger') }}">Jurnal Umum</a></li>
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
