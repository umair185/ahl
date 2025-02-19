<?php
    $tag_lines = Helper::getTaglines();
?>
<nav class="navbar header-navbar pcoded-header">
    <div class="navbar-wrapper">
        <div class="navbar-logo">
            <a class="mobile-menu waves-effect waves-light" id="mobile-collapse" href="#!">
                <i class="ti-menu"></i>
            </a>
            <div class="mobile-search waves-effect waves-light">
                <div class="header-search">
                    <div class="main-search morphsearch-search">
                        <div class="input-group">
                            <span class="input-group-prepend search-close"><i class="ti-close input-group-text"></i></span>
                            <input type="text" class="form-control" placeholder="Enter Keyword">
                            <span class="input-group-append search-btn"><i class="ti-search input-group-text"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{route('index')}}">
                <img class="img-fluid" src="{{ asset('logo/AHL_for_portal.png') }}" height="60" width="114" alt="Theme-Logo" style="margin-left: 35px; margin-top: 10px;" />
            </a>
            <a class="mobile-options waves-effect waves-light">
                <i class="ti-more"></i>
            </a>
        </div>
        <div class="navbar-container container-fluid">
            <ul class="nav-left">
                <li>
                    <div class="sidebar_toggle"><a href="javascript:void(0)"><i class="ti-menu"></i></a></div>
                </li>
                <li>
                    <a href="#!" onclick="javascript:toggleFullScreen()" class="waves-effect waves-light">
                        <i class="ti-fullscreen"></i>
                    </a>
                </li>
            </ul>
            <ul class="nav-right">
                @if(Auth::user()->id == 1)
                <li class="header-notification">
                    <a href="{{route('delayedOrders')}}">
                        <button class="btn btn-success">Click Here</button>
                    </a>
                </li>
                @endif
                <li class="user-profile header-notification">
                    <a href="#!" class="waves-effect waves-light">
                        <img src="{{ asset('assets/images/profile.png')}}" class="img-radius" alt="User-Profile-Image">
                        <span>{{ Auth::user() ? Auth::user()->name : ''}}</span>
                        <i class="ti-angle-down"></i>
                    </a>
                    <ul class="show-notification profile-notification">
                        <!-- <li class="waves-effect waves-light">
                            <a href="#!">
                                <i class="ti-settings"></i> Settings
                            </a>
                        </li>
                        <li class="waves-effect waves-light">
                            <a href="#">
                                <i class="ti-user"></i> Profile
                            </a>
                        </li> -->
                        <!-- <li class="waves-effect waves-light">
                            <a href="email-inbox.html">
                                <i class="ti-email"></i> My Messages
                            </a>
                        </li> -->
                        <!-- <li class="waves-effect waves-light">
                            <a href="auth-lock-screen.html">
                                <i class="ti-lock"></i> Lock Screen
                            </a>
                        </li> -->
                        <li class="waves-effect waves-light">
                            <a href="{{ route('passwordUpdate',['id'=>Helper::encrypt(Auth::user()->id)]) }}"><i class="ti-user"></i>Change Password</a>
                        </li>
                        <li class="waves-effect waves-light">
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();"><i class="ti-layout-sidebar-left"></i>Logout</a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <style>
        .blink {
            color: white;
            font-family: sans-serif;
            font-size: 20px;
            font-weight: bold;
        }
        @keyframes blinker {
            50% {
                opacity: 0;
            }
        }
    </style>
    <div class="navbar-wrapper">
        <marquee class="blink">
            @foreach($tag_lines as $tag_line)
                *** {{$tag_line->tag_line}} ***
                <span style="margin-right: 50px;"></span>
            @endforeach
        </marquee>
    </div>
</nav>