<!DOCTYPE html>
<html class="loading" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>Alsa7a</title>
    <link rel="apple-touch-icon" href="{{asset("app-assets/images/ico/apple-icon-120.png")}}">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('app-assets/images/logo.jpeg')}}">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Quicksand:300,400,500,700"
        rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    @if (app()->getLocale() == 'ar')
        <style>
            body,
            h1,
            h2,
            h3,
            h4,
            h5,
            h6,
            .navigation,
            .card-title,
            .table,
            .btn {
                font-family: 'Cairo', sans-serif !important;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css-rtl/core/colors/palette-gradient.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css-rtl/pages/timeline.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css-rtl/pages/dashboard-ecommerce.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("assets/css/style-rtl.css")}}">
        <link rel="stylesheet" type="text/css"
            href="{{asset("app-assets/css-rtl/core/menu/menu-types/vertical-content-menu.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css-rtl/core/colors/palette-gradient.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css-rtl/app.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css-rtl/custom-rtl.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css-rtl/vendors.css")}}">
        <style>
            button.btn-close {
                margin-right: auto !important;
                margin-left: unset !important;
            }
        </style>
    @else
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css/core/colors/palette-gradient.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css/pages/timeline.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css/pages/dashboard-ecommerce.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("assets/css/style.css")}}">
        <link rel="stylesheet" type="text/css"
            href="{{asset("app-assets/css/core/menu/menu-types/vertical-content-menu.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css/core/colors/palette-gradient.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css/app.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css/custom.css")}}">
        <link rel="stylesheet" type="text/css" href="{{asset("app-assets/css/vendors.css")}}">
    @endif

    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="{{asset("app-assets/vendors/css/weather-icons/climacons.min.css")}}">
    <link rel="stylesheet" type="text/css" href="{{asset("app-assets/fonts/meteocons/style.css")}}">
    <link rel="stylesheet" type="text/css" href="{{asset("app-assets/vendors/css/charts/morris.css")}}">
    <link rel="stylesheet" type="text/css" href="{{asset("app-assets/vendors/css/charts/chartist.css")}}">
    <link rel="stylesheet" type="text/css"
        href="{{asset("app-assets/vendors/css/charts/chartist-plugin-tooltip.css")}}">

    <!-- END VENDOR CSS-->
    <!-- END MODERN CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="{{asset("app-assets/fonts/simple-line-icons/style.css")}}">
    <link rel="stylesheet" type="text/css" href="{{asset("assets/css/style-rtl.css")}}">
    <link rel="stylesheet" type="text/css" href="{{asset("assets/css/custom.css")}}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    @stack('css')
    @livewireStyles
    <style>
        @media (max-width: 720px) {
            .card.p-5 {
                padding: 0px !important;
            }
        }
    </style>
</head>

<body class="vertical-layout vertical-content-menu 2-columns menu-expanded fixed-navbar" data-open="click"
    data-menu="vertical-content-menu" data-col="2-columns">
    <!-- fixed-top-->
    <!-- fixed-top-->
    <nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-dark navbar-hide-on-scroll navbar-border navbar-shadow navbar-brand-center bg-gradient-to-r from-emerald-500 to-cyan-500"
        style="background: linear-gradient(90deg, #34d399 0%, #0ea5e9 100%);">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav flex-row">
                    <li class="nav-item mobile-menu d-md-none mr-auto"><a
                            class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i
                                class="ft-menu font-large-1"></i></a></li>
                    <li class="nav-item">
                        <a class="navbar-brand" href="{{route('dashboard')}}">
                            {{-- Logo: Ensure it looks good on the gradient --}}
                            <img class="brand-logo" alt="modern admin logo"
                                src="{{asset('app-assets/images/logo.jpeg')}}"
                                style="height: 45px; width: auto; border-radius: 8px;">
                        </a>
                    </li>
                    <li class="nav-item d-md-none">
                        <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i
                                class="la la-ellipsis-v"></i></a>
                    </li>
                </ul>
            </div>
            <div class="navbar-container content">
                <div class="collapse navbar-collapse" id="navbar-mobile">
                    <ul class="nav navbar-nav mr-auto float-left">
                        <li class="nav-item d-none d-md-block"><a class="nav-link nav-menu-main menu-toggle hidden-xs"
                                href="#"><i class="ft-menu text-white"></i></a></li>
                        {{-- Search Bar Injection --}}
                        <li class="nav-item d-none d-md-block nav-search-container"
                            style="margin-top: 10px; margin-left: 20px; width: 400px;">
                            <div class="position-relative">
                                <input type="text" class="form-control round border-0 shadow-sm"
                                    placeholder="Search for anything..."
                                    style="border-radius: 999px; padding-left: 1.5rem;">
                                <div class="form-control-position" style="right: 10px; top: 2px;">
                                    <i class="la la-search primary font-medium-4"></i>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav float-right">
                        <li class="nav-item">
                            <a class="nav-link" !data-widget="fullscreen"
                                href="{{ App::getLocale() == 'ar' ? LaravelLocalization::getLocalizedURL('en') : LaravelLocalization::getLocalizedURL('ar') }}"
                                role="button">
                                <span
                                    class="text-uppercase badge badge-pill badge-white text-dark">{{ App::getLocale() == 'ar' ? 'en' : 'ar' }}</span>
                            </a>
                        </li>

                        <li class="dropdown dropdown-user nav-item">
                            <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                                @auth
                                    <span class="mr-1 text-white">{{ __('lang.hello') }},
                                        <span class="user-name text-bold-700 text-white">{{ Auth::user()->name }}</span>
                                    </span>
                                @endauth
                                @guest
                                    <span class="mr-1 text-white">{{ __('lang.hello') }}, <span
                                            class="user-name text-bold-700 text-white">{{ __('lang.guest') }}</span></span>
                                @endguest
                                <span class="avatar avatar-online">
                                    <img src="{{ asset('app-assets/images/portrait/small/avatar-s-19.png') }}"
                                        alt="avatar" style="border: 2px solid white;"><i></i>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="ft-power"></i> {{ __('lang.logout') }}
                                </a>

                                <form id="logout-form" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row"></div>
            <div class="main-menu menu-static menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
                <div class="main-menu-content">
                    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

                        <li class="nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}">
                                <i class="la la-share-alt"></i>
                                <span class="menu-title"
                                    data-i18n="nav.morris_charts.main">{{ __('admin.menu.dashboard') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.categories.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.categories.index') }}">
                                <i class="la la-list"></i>
                                <span class="menu-title">{{ __('admin.menu.categories') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.sports.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.sports.index') }}">
                                <i class="la la-trophy"></i>
                                <span class="menu-title">{{ __('admin.menu.sports') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.sliders.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.sliders.index') }}">
                                <i class="la la-image"></i>
                                <span class="menu-title">{{ __('admin.menu.sliders') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.leagues.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.leagues.index') }}">
                                <i class="la la-server"></i>
                                <span class="menu-title">{{ __('admin.leagues.index') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.events.index') }}">
                                <i class="la la-calendar"></i>
                                <span class="menu-title">{{ __('admin.events.index') }}</span>
                            </a>
                        </li>

                        <li class=" navigation-header">
                            <span data-i18n="nav.category.community">Community</span><i
                                class="la la-ellipsis-h ft-minus" data-toggle="tooltip" data-placement="right"
                                data-original-title="Community"></i>
                        </li>

                        <li class="nav-item {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.news.index') }}">
                                <i class="la la-newspaper-o"></i>
                                <span class="menu-title">{{ __('admin.news.title') }}</span>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.posts.index') }}">
                                <i class="la la-comments"></i>
                                <span class="menu-title">{{ __('admin.posts.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.services.index') }}">
                                <i class="la la-briefcase"></i>
                                <span class="menu-title">{{ __('admin.services.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.service_requests.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.service_requests.index') }}">
                                <i class="la la-exchange"></i>
                                <span class="menu-title">{{ __('admin.service_requests.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.tickets.index') }}">
                                <i class="la la-ticket"></i>
                                <span class="menu-title">{{ __('admin.tickets.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.settings.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.settings.index') }}">
                                <i class="la la-cog"></i>
                                <span class="menu-title">{{ __('admin.settings.index') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="content-body">
                {{-- {{ $slot }} --}}
                @yield('content')
            </div>
        </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <footer class="footer footer-static footer-light navbar-border">
        <p class="clearfix blue-grey lighten-2 text-sm-center mb-0 px-2">
            <span class="float-md-left d-block d-md-inline-block">Copyright &copy; 2025 <a
                    class="text-bold-800 grey darken-2" target="_blank">Mazaya </a>, All rights reserved. </span>
        </p>
    </footer>
    <!-- BEGIN VENDOR JS-->
    <script src="{{asset("app-assets/vendors/js/vendors.min.js")}}" type="text/javascript"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <script src="{{asset("app-assets/vendors/js/ui/headroom.min.js")}}" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{asset("app-assets/vendors/js/charts/raphael-min.js")}}" type="text/javascript"></script>
    <script src="{{asset("app-assets/vendors/js/charts/morris.min.js")}}" type="text/javascript"></script>
    <script src="{{asset("app-assets/vendors/js/timeline/horizontal-timeline.js")}}" type="text/javascript"></script>
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN MODERN JS-->
    <script src="{{asset("app-assets/js/core/app-menu.js")}}" type="text/javascript"></script>
    <script src="{{asset("app-assets/js/core/app.js")}}" type="text/javascript"></script>
    <script src="{{asset("app-assets/js/scripts/customizer.js")}}" type="text/javascript"></script>
    <!-- END MODERN JS-->
    <!-- BEGIN PAGE LEVEL JS-->
    {{--
    <script src="{{asset(" app-assets/js/scripts/pages/dashboard-ecommerce.js")}}" type="text/javascript"></script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.13.0/Sortable.min.js"></script>

    <script>
        document.addEventListener('livewire:load', function () {
            let sortableList = document.getElementById('sortable-list');
            let confirmButton = document.getElementById('confirm-order-btn');
            let updatedOrder = [];

            // Ensure that the sortableList exists in the DOM
            if (sortableList) {
                // Initialize SortableJS manually
                new Sortable(sortableList, {
                    animation: 150,
                    handle: 'div',
                    onEnd: function (event) {
                        // Capture the new order
                        updatedOrder = Array.from(event.target.children).map((el, index) => el.getAttribute('wire:sortable.item'));

                        // Show the confirm button
                        confirmButton.style.display = 'block';
                    }
                });
            }

            // Handle the confirm button click
            confirmButton.addEventListener('click', function () {
                // Emit the Livewire event with the updated order
                Livewire.emit('updateOrder', updatedOrder);

                // Hide the confirm button after order is confirmed
                confirmButton.style.display = 'none';
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Function to check body class and toggle the visibility of all h3 elements with class menu-title
            function checkBodyClass() {
                var body = document.body;
                var menuTitles = document.querySelectorAll('h3.menu-title');

                menuTitles.forEach(function (title) {
                    if (body.classList.contains('menu-collapsed')) {
                        title.style.display = 'none';
                    } else {
                        title.style.display = 'block';
                    }
                });
            }

            // Initial check
            checkBodyClass();

            // Monitor class changes on the body element
            var observer = new MutationObserver(checkBodyClass);
            observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
        });
    </script>

    <script>
        // Check for session messages and fire SweetAlert
        @if(session('swal_success'))
            Swal.fire({
                title: '{{ __("Success!") }}',
                text: '{{ session("swal_success") }}',
                icon: 'success',
                confirmButtonText: '{{ __("OK") }}',
                confirmButtonColor: '#34d399',
                background: '#fff',
                color: '#333'
            });
        @endif

        @if(session('swal_error'))
            Swal.fire({
                title: '{{ __("Error!") }}',
                text: '{{ session("swal_error") }}',
                icon: 'error',
                confirmButtonText: '{{ __("OK") }}',
                confirmButtonColor: '#d33',
            });
        @endif
    </script>

    @livewireScripts
    @stack('js')

</body>

</html>