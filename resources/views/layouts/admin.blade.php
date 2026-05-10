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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    @stack('css')
    @livewireStyles
    @include('layouts.admin_premium_css')
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
                        <a class="navbar-brand" href="{{route('admin.dashboard')}}">
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
                                <input type="text" id="global-search-input"
                                    class="form-control round border-0 shadow-sm"
                                    placeholder="{{ __('admin.buttons.actions') }}..." autocomplete="off"
                                    style="border-radius: 999px; padding-left: 1.5rem;">
                                <div class="form-control-position" style="right: 10px; top: 2px;">
                                    <i class="la la-search primary font-medium-4"></i>
                                </div>
                                <div id="global-search-results" class="search-results-dropdown"></div>
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

                        <li class="dropdown dropdown-notification nav-item">
                            <a class="nav-link nav-link-label" href="#" data-toggle="dropdown">
                                <i class="ficon ft-bell text-white"></i>
                                <span class="badge badge-pill badge-danger badge-up badge-glow" id="notification-count" style="display:none; position: absolute; top: 10px; right: 5px; font-size: 10px; padding: 2px 5px;">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                                <li class="dropdown-menu-header">
                                    <h6 class="dropdown-header m-0">
                                        <span class="grey darken-2">{{ __('admin.notifications.title') }}</span>
                                    </h6>
                                    <span class="notification-tag badge badge-danger float-right m-0"><span id="unread-text">0</span> {{ __('admin.notifications.unread') }}</span>
                                </li>
                                <li class="scrollable-container media-list w-100" id="notification-list" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Notifications will be injected here -->
                                </li>
                                <li class="dropdown-menu-footer">
                                    <div class="d-flex justify-content-between px-2 py-1">
                                        <a class="text-muted font-small-3" href="javascript:void(0)" id="mark-all-read">{{ __('admin.notifications.mark_all_read') }}</a>
                                        <a class="text-info font-small-3" href="javascript:void(0)" id="test-notification-sound"><i class="la la-volume-up"></i> {{ App::getLocale() == 'ar' ? 'تجربة الصوت' : 'Test Sound' }}</a>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown dropdown-user nav-item">
                            <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                                @auth
                                    <span class="mr-1 text-white">{{ __('admin.messages.hello') }},
                                        <span class="user-name text-bold-700 text-white">{{ Auth::user()->name }}</span>
                                    </span>
                                @endauth
                                @guest
                                    <span class="mr-1 text-white">{{ __('admin.messages.hello') }}, <span
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
                                    <i class="ft-power"></i> {{ __('admin.buttons.logout') }}
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
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="la la-home"></i>
                                <span class="menu-title"
                                    data-i18n="nav.morris_charts.main">{{ __('admin.menu.dashboard') }}</span>
                            </a>
                        </li>

                        <!-- User Management -->
                        <li class=" navigation-header" data-section="users">
                            <span>{{ App::getLocale() == 'ar' ? 'إدارة المستخدمين' : 'User Management' }}</span>
                            <i class="la la-angle-down nav-section-icon"></i>
                        </li>
                        <li class="nav-item {{ Route::is('admin.users.*') && !request()->has('pending_verification') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.index') }}">
                                <i class="la la-users"></i>
                                <span class="menu-title">{{ __('admin.dashboard.users') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->has('pending_verification') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.index', ['pending_verification' => 1]) }}">
                                <i class="la la-check-circle"></i>
                                <span class="menu-title">{{ __('admin.menu.verification_requests') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.otps.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.otps.index') }}">
                                <i class="la la-key"></i>
                                <span class="menu-title">{{ __('admin.otps.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.verification.manual') ? 'active' : '' }}">
                            <a href="{{ route('admin.verification.manual') }}">
                                <i class="la la-shield"></i>
                                <span class="menu-title">{{ __('admin.otps.force_verification') }}</span>
                            </a>
                        </li>

                        <!-- Sports & Events -->
                        <li class=" navigation-header" data-section="sports">
                            <span>{{ App::getLocale() == 'ar' ? 'الرياضة والفعاليات' : 'Sports & Events' }}</span>
                            <i class="la la-angle-down nav-section-icon"></i>
                        </li>
                        <li class="nav-item {{ Route::is('admin.sports.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.sports.index') }}">
                                <i class="la la-trophy"></i>
                                <span class="menu-title">{{ __('admin.menu.sports') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.leagues.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.leagues.index') }}">
                                <i class="la la-server"></i>
                                <span class="menu-title">{{ __('admin.leagues.index') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.clubs.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.clubs.index') }}">
                                <i class="la la-shield"></i>
                                <span class="menu-title">{{ __('admin.menu.clubs') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.events.index') }}">
                                <i class="la la-calendar"></i>
                                <span class="menu-title">{{ __('admin.events.index') }}</span>
                            </a>
                        </li>

                        <!-- Content & Community -->
                        <li class=" navigation-header" data-section="content">
                            <span>{{ App::getLocale() == 'ar' ? 'المحتوى والأقسام' : 'Content & Categories' }}</span>
                            <i class="la la-angle-down nav-section-icon"></i>
                        </li>
                        <li class="nav-item {{ Route::is('admin.parent_categories.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.parent_categories.index') }}">
                                <i class="la la-sitemap"></i>
                                <span class="menu-title">{{ __('admin.parent_categories.index') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.categories.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.categories.index') }}">
                                <i class="la la-list"></i>
                                <span class="menu-title">{{ __('admin.menu.categories') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.community_categories.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.community_categories.index') }}">
                                <i class="la la-tags"></i>
                                <span class="menu-title">{{ __('admin.community_categories.index') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.news.index') }}">
                                <i class="la la-newspaper-o"></i>
                                <span class="menu-title">{{ __('admin.news.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.posts.index') }}">
                                <i class="la la-instagram"></i>
                                <span class="menu-title">{{ __('admin.posts.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.community_posts.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.community_posts.index') }}">
                                <i class="la la-comments"></i>
                                <span class="menu-title">{{ __('admin.community_posts.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.sliders.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.sliders.index') }}">
                                <i class="la la-image"></i>
                                <span class="menu-title">{{ __('admin.menu.sliders') }}</span>
                            </a>
                        </li>

                        <!-- Services & Support -->
                        <li class=" navigation-header" data-section="services">
                            <span>{{ App::getLocale() == 'ar' ? 'الخدمات والدعم' : 'Services & Support' }}</span>
                            <i class="la la-angle-down nav-section-icon"></i>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.services.index') }}">
                                <i class="la la-briefcase"></i>
                                <span class="menu-title">{{ __('admin.services.index') }}</span>
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

                        <!-- Settings & Legal -->
                        <li class=" navigation-header" data-section="settings">
                            <span>{{ App::getLocale() == 'ar' ? 'الإعدادات والقوانين' : 'Settings & Legal' }}</span>
                            <i class="la la-angle-down nav-section-icon"></i>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.notifications.create') }}">
                                <i class="la la-bell"></i>
                                <span class="menu-title">{{ __('admin.menu.notifications.title') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Route::is('admin.settings.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.settings.index') }}">
                                <i class="la la-cog"></i>
                                <span class="menu-title">{{ __('admin.settings.index') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('terms') }}" target="_blank">
                                <i class="la la-file-text"></i>
                                <span class="menu-title">{{ __('admin.settings.terms_ar') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('privacy') }}" target="_blank">
                                <i class="la la-lock"></i>
                                <span class="menu-title">{{ __('admin.settings.privacy_ar') }}</span>
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
            <span class="float-md-left d-block d-md-inline-block">Copyright &copy; 2026 <a
                    class="text-bold-800 grey darken-2" target="_blank">Mwaslx </a>, All rights reserved. </span>
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
    <script src="{{ asset('app-assets/js/scripts/forms/select/form-select2.js') }}" type="text/javascript"></script>

    <!-- Custom Script for Dynamic Sidebar Accordion -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const headers = document.querySelectorAll('.navigation-header');
            
            // On load: check if a section contains the active item. If not, we might want it closed by default.
            // For a slick look, let's close all sections EXCEPT the one containing the active link!
            headers.forEach(header => {
                let keepOpen = false;
                let next = header.nextElementSibling;
                const itemsToToggle = [];
                
                while(next && !next.classList.contains('navigation-header')) {
                    if(next.classList.contains('nav-item')) {
                        itemsToToggle.push(next);
                        // Check if this item is active
                        if(next.classList.contains('active')) {
                            keepOpen = true;
                        }
                    }
                    next = next.nextElementSibling;
                }

                // If this section doesn't have the active route, close it initially for a cleaner look
                if (!keepOpen) {
                    header.classList.add('section-closed');
                    itemsToToggle.forEach(i => i.classList.add('nav-item-hidden'));
                }

                // Attach Click Event
                header.addEventListener('click', function() {
                    const isClosed = this.classList.contains('section-closed');
                    
                    // Close all other sections for "Accordion" effect
                    headers.forEach(otherHeader => {
                        if (otherHeader !== this && !otherHeader.classList.contains('section-closed')) {
                            otherHeader.classList.add('section-closed');
                            let otherNext = otherHeader.nextElementSibling;
                            while(otherNext && !otherNext.classList.contains('navigation-header')) {
                                if(otherNext.classList.contains('nav-item')) {
                                    otherNext.classList.add('nav-item-hidden');
                                }
                                otherNext = otherNext.nextElementSibling;
                            }
                        }
                    });

                    // Toggle current section
                    this.classList.toggle('section-closed');
                    let currNext = this.nextElementSibling;
                    while(currNext && !currNext.classList.contains('navigation-header')) {
                        if(currNext.classList.contains('nav-item')) {
                            if (isClosed) {
                                currNext.classList.remove('nav-item-hidden');
                                // Trigger reflow for CSS animation
                                void currNext.offsetWidth; 
                            } else {
                                currNext.classList.add('nav-item-hidden');
                            }
                        }
                        currNext = currNext.nextElementSibling;
                    }
                });
            });
        });
    </script>

    <script src="{{asset("app-assets/js/scripts/customizer.js")}}" type="text/javascript"></script>
    <!-- END MODERN JS-->
    <!-- BEGIN PAGE LEVEL JS-->
    {{--
    <script src="{{asset(" app-assets/js/scripts/pages/dashboard-ecommerce.js")}}" type="text/javascript"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                title: '{{ __("admin.modal.success") }}',
                text: '{{ session("swal_success") }}',
                icon: 'success',
                confirmButtonText: '{{ __("admin.modal.ok") }}',
                confirmButtonColor: '#34d399',
                background: '#fff',
                color: '#333'
            });
        @endif

        @if(session('swal_error'))
            Swal.fire({
                title: '{{ __("admin.modal.error") }}',
                text: '{{ session("swal_error") }}',
                icon: 'error',
                confirmButtonText: '{{ __("admin.modal.ok") }}',
                confirmButtonColor: '#d33',
            });
        @endif

        @if($errors->any())
            Swal.fire({
                title: '{{ __("admin.modal.error") }}',
                html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                icon: 'error',
                confirmButtonText: '{{ __("admin.modal.ok") }}',
                confirmButtonColor: '#d33',
            });
        @endif
    </script>

    <script>
        $(document).ready(function () {
            let timeout = null;
            const searchInput = $('#global-search-input');
            const resultsContainer = $('#global-search-results');

            searchInput.on('keyup', function () {
                clearTimeout(timeout);
                const query = $(this).val();

                if (query.length < 2) {
                    resultsContainer.hide().html('');
                    return;
                }

                timeout = setTimeout(function () {
                    $.ajax({
                        url: "{{ route('admin.global_search') }}",
                        type: "GET",
                        data: { query: query },
                        success: function (data) {
                            if (data.length > 0) {
                                let html = '';
                                data.forEach(item => {
                                    html += `
                                        <a href="${item.url}" class="search-item">
                                            <i class="${item.icon}"></i>
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold">${item.title}</span>
                                            </div>
                                            <span class="search-type">${item.type}</span>
                                        </a>
                                    `;
                                });
                                resultsContainer.html(html).slideDown(200);
                            } else {
                                resultsContainer.html('<div class="p-3 text-center text-muted small">{{ __("admin.categories.no_records") }}</div>').slideDown(200);
                            }
                        }
                    });
                }, 400); // 400ms debounce
            });

            // Close on click outside
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.nav-search-container').length) {
                    resultsContainer.slideUp(100);
                }
            });

            // Re-open on focus if has value
            searchInput.on('focus', function () {
                if ($(this).val().length >= 2) {
                    resultsContainer.slideDown(200);
                }
            });

            // Initialize Select2
            $('.select2').each(function () {
                $(this).select2({
                    width: '100%',
                    placeholder: "{{ __('admin.buttons.select') }}",
                    dir: "{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
                });
            });
        });
    </script>
    <script>
        let lastCount = 0;
        let audioEnabled = false;
        // Using a more robust notification sound link
        const notificationAudio = new Audio('https://notifications-sounds.com/storage/sounds/pizzicato.mp3');

        function fetchNotifications() {
            $.get('{{ route('admin.notifications.fetch') }}', function(data) {
                if (data.count > lastCount) {
                    // Only play sound if count increased
                    if (data.count > 0) {
                         notificationAudio.play().catch(e => {
                             console.log('Autoplay blocked. Sound will play after first interaction.');
                         });
                    }
                }
                lastCount = data.count;

                if (data.count > 0) {
                    $('#notification-count').text(data.count).show();
                    $('#unread-text').text(data.count);
                    
                    let html = '';
                    data.notifications.forEach(n => {
                        const isRead = n.read_at !== null;
                        const bgClass = isRead ? 'bg-light' : '';
                        const iconClass = isRead ? 'bg-grey bg-lighten-1' : 'bg-cyan';
                        const opacityStyle = isRead ? 'opacity: 0.6;' : '';
                        const textWeight = isRead ? '' : 'text-bold-600';

                        html += `
                            <a href="${n.data.url}" class="dropdown-item border-bottom notification-link ${bgClass}" data-id="${n.id}" style="${opacityStyle}">
                                <div class="media">
                                    <div class="media-left align-self-center mr-2"><i class="ft-user-plus icon-bg-circle ${iconClass}"></i></div>
                                    <div class="media-body">
                                        <h6 class="media-heading font-small-3 ${textWeight} mb-0">${n.data.user_name} ${n.data.registered_text || 'registered'}</h6>
                                        <p class="notification-text font-small-2 text-muted mb-0">${n.data.message}</p>
                                        <small><time class="media-meta text-muted" style="font-size: 10px;">${n.created_at}</time></small>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    $('#notification-list').html(html);
                } else {
                    $('#notification-count').hide();
                    $('#unread-text').text(0);
                    $('#notification-list').html('<div class="p-3 text-center text-muted">{{ __('admin.notifications.no_new') }}</div>');
                }
            });
        }

        $(document).ready(function() {
            fetchNotifications();
            setInterval(fetchNotifications, 10000); // Poll every 10 seconds

            // Enable audio on first click anywhere
            $(document).one('click', function() {
                audioEnabled = true;
                console.log('Audio enabled by user interaction');
            });

            $('#test-notification-sound').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                notificationAudio.play();
                Swal.fire({
                    title: '{{ App::getLocale() == "ar" ? "تم تفعيل الصوت" : "Sound Enabled" }}',
                    text: '{{ App::getLocale() == "ar" ? "ستسمع هذا التنبيه عند تسجيل مستخدم جديد" : "You will hear this alert when a new user registers" }}',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            });

            $(document).on('click', '.notification-link', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let id = $(this).data('id');
                let markUrl = '{{ route('admin.notifications.mark_single_read', ':id') }}'.replace(':id', id);

                $.post(markUrl, { _token: '{{ csrf_token() }}' }, function() {
                    window.location.href = url;
                });
            });

            $('#mark-all-read').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $.post('{{ route('admin.notifications.mark_read') }}', { _token: '{{ csrf_token() }}' }, function() {
                    fetchNotifications();
                });
            });
        });
    </script>
    @stack('js')

</body>

</html>