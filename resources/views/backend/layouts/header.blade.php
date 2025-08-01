<a id="show-sidebar" class="btn btn-sm btn-dark z-3" href="#">
    <i class="fas fa-bars"></i>
</a>
<nav id="sidebar" class="sidebar-wrapper">
    <div class="sidebar-content">
        <div class="sidebar-brand">
            <a href="#">pro sidebar BS5</a>
            <div id="close-sidebar">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="sidebar-header">
            <div class="user-pic">
                <img class="img-responsive img-rounded"
                     src="{{asset('assets/img/profileImage.png')}}"
                     alt="User picture">
            </div>
            <div class="user-info">
                <span class="user-name">{{ucfirst(auth()->user()->name)}}</span>
                <span class="user-role">{{ucfirst(auth()->user()->user_type)}}</span>
                <span class="user-status">
            <i class="fa fa-circle"></i>
            <span>Online</span>
          </span>
            </div>
        </div>
        <!-- sidebar-header  -->
        <div class="sidebar-menu">
            <ul>
                <li class="header-menu">
                    <span>General</span>
                </li>
                <li class="{{request()->routeIs('dashboard') ? 'main-active' : ''}}">
                    <a href="{{route('dashboard')}}">
                        <i class="fa fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                        {{--                        <span class="badge badge-pill badge-primary">Beta</span>--}}
                    </a>
                </li>
                <li class="sidebar-dropdown {{request()->routeIs('registerView', 'getUser') ? 'active' : ''}}">
                    <a href="#">
                        <i class="fa fa-user-alt"></i>
                        <span>Users</span>
                        {{--                        <span class="badge badge-pill badge-warning">New</span>--}}
                    </a>
                    <div class="sidebar-submenu"
                         style="display:{{request()->routeIs('registerView', 'getUser') ? 'block' : ''}};">
                        <ul>
                            <li class="{{request()->routeIs('registerView') ? 'active' : ''}}">
                                <a href="{{route('registerView')}}">Add User
                                    {{--                                    <span class="badge badge-pill badge-success">Pro</span>--}}
                                </a>
                            </li>
                            <li class="{{request()->routeIs('getUser') ? 'active' : ''}}">
                                <a href="{{route('getUser')}}">View Users</a>
                            </li>
                        </ul>
                    </div>
                </li>
                {{--                <li class="sidebar-dropdown">--}}
                {{--                    <a href="#">--}}
                {{--                        <i class="fa fa-shopping-cart"></i>--}}
                {{--                        <span>E-commerce</span>--}}
                {{--                        <span class="badge badge-pill badge-danger">3</span>--}}
                {{--                    </a>--}}
                {{--                    <div class="sidebar-submenu">--}}
                {{--                        <ul>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Products--}}

                {{--                                </a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Orders</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Credit cart</a>--}}
                {{--                            </li>--}}
                {{--                        </ul>--}}
                {{--                    </div>--}}
                {{--                </li>--}}
                {{--                <li class="sidebar-dropdown">--}}
                {{--                    <a href="#">--}}
                {{--                        <i class="far fa-gem"></i>--}}
                {{--                        <span>Components</span>--}}
                {{--                    </a>--}}
                {{--                    <div class="sidebar-submenu">--}}
                {{--                        <ul>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">General</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Panels</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Tables</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Icons</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Forms</a>--}}
                {{--                            </li>--}}
                {{--                        </ul>--}}
                {{--                    </div>--}}
                {{--                </li>--}}
                {{--                <li class="sidebar-dropdown">--}}
                {{--                    <a href="#">--}}
                {{--                        <i class="fa fa-chart-line"></i>--}}
                {{--                        <span>Charts</span>--}}
                {{--                    </a>--}}
                {{--                    <div class="sidebar-submenu">--}}
                {{--                        <ul>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Pie chart</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Line chart</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Bar chart</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Histogram</a>--}}
                {{--                            </li>--}}
                {{--                        </ul>--}}
                {{--                    </div>--}}
                {{--                </li>--}}
                {{--                <li class="sidebar-dropdown">--}}
                {{--                    <a href="#">--}}
                {{--                        <i class="fa fa-globe"></i>--}}
                {{--                        <span>Maps</span>--}}
                {{--                    </a>--}}
                {{--                    <div class="sidebar-submenu">--}}
                {{--                        <ul>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Google maps</a>--}}
                {{--                            </li>--}}
                {{--                            <li>--}}
                {{--                                <a href="#">Open street map</a>--}}
                {{--                            </li>--}}
                {{--                        </ul>--}}
                {{--                    </div>--}}
                {{--                </li>--}}
                {{--                <li class="header-menu">--}}
                {{--                    <span>Extra</span>--}}
                {{--                </li>--}}
                {{--                <li>--}}
                {{--                    <a href="#">--}}
                {{--                        <i class="fa fa-book"></i>--}}
                {{--                        <span>Documentation</span>--}}
                {{--                        <span class="badge badge-pill badge-primary">Beta</span>--}}
                {{--                    </a>--}}
                {{--                </li>--}}
                {{--                <li>--}}
                {{--                    <a href="#">--}}
                {{--                        <i class="fa fa-calendar"></i>--}}
                {{--                        <span>Calendar</span>--}}
                {{--                    </a>--}}
                {{--                </li>--}}
                {{--                <li>--}}
                {{--                    <a href="#">--}}
                {{--                        <i class="fa fa-folder"></i>--}}
                {{--                        <span>Examples</span>--}}
                {{--                    </a>--}}
                {{--                </li>--}}
            </ul>
        </div>
        <!-- sidebar-menu  -->
    </div>
    <!-- sidebar-content  -->
    <div class="sidebar-footer">
        <a href="#">
            <i class="fa fa-bell"></i>
            <span class="badge badge-pill badge-warning notification">3</span>
        </a>
        <a href="#">
            <i class="fa fa-envelope"></i>
            <span class="badge badge-pill badge-success notification">7</span>
        </a>
        <a href="#">
            <i class="fa fa-cog"></i>
            <span class="badge-sonar"></span>
        </a>
        <a href="{{route('logout')}}">
            <i class="fa fa-power-off"></i>
        </a>
    </div>
</nav>


