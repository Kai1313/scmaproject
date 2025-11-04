@php
    $datas = session()->get('list_menu') ?? [];
    $routeName = request()->route()->getName();
@endphp
<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu" data-widget="tree">
            <li class="nav-item" data-alias="beranda">
                <a href="{{ env('OLD_URL_ROOT') }}">
                    <i class="glyphicon glyphicon-home"></i> <span>Beranda</span>
                </a>
            </li>
            @foreach ($datas as $data1)
                @if (checkAccessMenu($data1->alias_menu))
                    @php
                        $active = '';
                        if ($data1->route_laravel_menu) {
                            $link = route($data1->route_laravel_menu);
                            $active = menuActiveSidebar($data1, $routeName);
                        } else {
                            $link = count($data1->childs) > 0 ? '#' : env('OLD_URL_ROOT') . '#' . $data1->alias_menu;
                        }
                    @endphp
                    <li class="{{ count($data1->childs) > 0 ? 'treeview' : 'nav-item' }} {{ $active }}"
                        data-alias="{{ $data1->alias_menu }}">
                        <a href="{{ $link }}">
                            <i class="glyphicon glyphicon-{{ $data1->gambar_menu }}"></i>
                            <span>{{ $data1->nama_menu }}</span>
                            @if (count($data1->childs) > 0)
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            @endif
                        </a>
                        @if (count($data1->childs) > 0)
                            <ul class="treeview-menu">
                                @foreach ($data1->childs as $data2)
                                    @if (checkAccessMenu($data2->alias_menu))
                                        @php
                                            $active = '';
                                            if ($data2->route_laravel_menu) {
                                                $link2 = route($data2->route_laravel_menu);
                                                $active = menuActiveSidebar($data2, $routeName);
                                            } else {
                                                $link2 =
                                                    count($data2->childs) > 0
                                                        ? '#'
                                                        : env('OLD_URL_ROOT') . '#' . $data2->alias_menu;
                                            }
                                        @endphp
                                        <li class="{{ count($data2->childs) > 0 ? 'treeview' : 'nav-item' }} {{ $active }}"
                                            data-alias="{{ $data2->alias_menu }}">
                                            <a href="{{ $link2 }}">
                                                <i class="glyphicon glyphicon-option-vertical"></i>
                                                <span>{{ $data2->nama_menu }}</span>
                                                @if (count($data2->childs) > 0)
                                                    <span class="pull-right-container">
                                                        <i class="fa fa-angle-left pull-right"></i>
                                                    </span>
                                                @endif
                                            </a>
                                            @if (count($data2->childs) > 0)
                                                <ul class="treeview-menu">
                                                    @foreach ($data2->childs as $data3)
                                                        @if (checkAccessMenu($data3->alias_menu))
                                                            @php
                                                                $active = '';
                                                                if ($data3->route_laravel_menu) {
                                                                    $link3 = route($data3->route_laravel_menu);
                                                                    $active = menuActiveSidebar($data3, $routeName);
                                                                } else {
                                                                    $link3 =
                                                                        count($data3->childs) > 0
                                                                            ? '#'
                                                                            : env('OLD_URL_ROOT') .
                                                                                '#' .
                                                                                $data3->alias_menu;
                                                                }
                                                            @endphp
                                                            <li class="{{ count($data3->childs) > 0 ? 'treeview' : 'nav-item' }} {{ $active }}"
                                                                data-alias="{{ $data3->alias_menu }}">
                                                                <a href="{{ $link3 }}">
                                                                    <i class="glyphicon glyphicon-option-vertical"></i>
                                                                    <span>{{ $data3->nama_menu }}</span>
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    </section>
</aside>
