<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Smart Soko') }}</title>

    <!-- Styles -->
{{--<link href="{{ asset('css/app.css') }}" rel="stylesheet">--}}

<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- Bootstrap Core Css -->
    <link href="{{url('/plugins/bootstrap/css/bootstrap.css')}}"  rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="{{url('/plugins/node-waves/waves.css')}}"  rel="stylesheet" />

    <!-- Animation Css -->
    <link href="{{url('/plugins/animate-css/animate.css')}}" rel="stylesheet" />

    <!-- Custom Css -->
    <link href="{{url('/css/style.css')}}" rel="stylesheet">

    <link href="{{url('/plugins/sweetalert/sweetalert.css')}}" rel="stylesheet" />


    <!-- Bootstrap Select Css -->
    <link href="{{url('/plugins/bootstrap-select/css/bootstrap-select.css')}}" rel="stylesheet" />

    {{--<link href="{{url('/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet" />--}}


    <link href="{{url('/plugins/nouislider/nouislider.min.css')}}" type="text/css" rel="stylesheet"/>

@yield('css')



<!-- AdminBSB Themes. You can choose a theme from css/themes instead of get all themes -->
    <link href="{{url('/css/themes/theme-light-blue.css')}}" rel="stylesheet" />
</head>
<body class="theme-light-blue">
<!-- Page Loader -->
<div class="page-loader-wrapper">
    <div class="loader">
        <div class="preloader">
            <div class="spinner-layer pl-red">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>
        </div>
        <p>Please wait...</p>
    </div>
</div>

<!-- #END# Page Loader -->
<!-- Overlay For Sidebars -->
<div class="overlay"></div>
<!-- #END# Overlay For Sidebars -->
<!-- Search Bar -->
<div class="search-bar">
    <div class="search-icon">
        <i class="material-icons">search</i>
    </div>
    <input type="text" placeholder="START TYPING...">
    <div class="close-search">
        <i class="material-icons">close</i>
    </div>
</div>
<!-- #END# Search Bar -->

<!-- Top Bar -->
<nav class="navbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
            <a href="javascript:void(0);" class="bars"></a>
            <a class="navbar-brand" href="{{url('/')}}">Smart Soko - Dashboard</a>
            {{--<img src="{{url('images/logo.png')}}">--}}

        </div>
        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                        <span style="padding: 7px 7px 2px 7px;" id="time"> </span>
                    </a>
                </li>


            </ul>
        </div>
    </div>
</nav>
<!-- #Top Bar -->
<section>
    <!-- Left Sidebar -->
    <aside id="leftsidebar" class="sidebar">
        <!-- User Info -->
        <div class="user-info">
            <div class="image">
                <img src="{{url('images/user.png')}}" width="48" height="48" alt="User" />
            </div>
            <div class="info-container">
                <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{Auth::user()->name}}</div>
                <div class="email">{{Auth::user()->email}}</div>
                <div class="btn-group user-helper-dropdown">
                    <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                    <ul class="dropdown-menu pull-right">
                        <li><a href="{{url('logout')}}"><i class="material-icons">input</i>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- #User Info -->
        <!-- Menu -->
        <div class="menu">
            <ul class="list">
                <li class="header active">MAIN NAVIGATION</li>
                <li class="{{\Request::is('/') ? 'active' : ''}}">
                    <a href="{{url('/')}}">
                        <i class="material-icons">dashboard</i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="{{\Request::is('/services') ? 'active' : ''}}">
                    <a href="{{url('/services')}}">
                        <i class="material-icons">insert_emoticon</i>
                        <span>Services</span>
                    </a>
                </li>


                {{--@if($perm_role->has_perm([1]))--}}
                <li class="{{\Request::is('providers') || \Request::is('businesses') || \Request::is('experts') ? 'active' : ''}}">
                    <a href="javascript:void(0);" class="menu-toggle">
                        <i class="material-icons">work</i>
                        <span>Service Providers</span>
                    </a>
                    <ul class="ml-menu">
                        <li class="{{\Request::is('providers') ? 'active' : ''}}">
                            <a href="{{url('/providers')}}">Service Providers</a>
                        </li>
                        <li class="{{\Request::is('businesses') ? 'active' : ''}}">
                            <a href="{{url('/businesses')}}">Businesses</a>
                        </li>
                        <li class="{{\Request::is('experts') ? 'active' : ''}}">
                            <a href="{{url('/experts')}}">Individual Experts</a>
                        </li>

                    </ul>
                </li>
                {{--@endif--}}





                    {{--@if($perm_role->has_perm([1]))--}}
                    <li class="{{\Request::is('users') || \Request::is('users/groups') ? 'active' : ''}}">
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">people</i>
                            <span>User Management</span>
                        </a>
                        <ul class="ml-menu">
                            <li class="{{\Request::is('users') ? 'active' : ''}}">
                                <a href="{{url('/users')}}">Users</a>
                            </li>
                            {{--<li class="{{\Request::is('users/groups') ? 'active' : ''}}">--}}
                            {{--<a href="{{url('/users/groups')}}">User Groups</a>--}}
                            {{--</li>--}}

                        </ul>
                    </li>
                    {{--@endif--}}




            </ul>
        </div>
        <!-- #Menu -->
        <!-- Footer -->
        <div class="legal">
            <div class="copyright">
                &copy; {{\Carbon\Carbon::now()->year}} <a href="javascript:void(0);">Depo Manager</a>.
            </div>
            <div class="version">
                <b>Version: </b> 1.0.1
            </div>
        </div>
        <!-- #Footer -->
    </aside>
    <!-- #END# Left Sidebar -->
</section>


@yield('content')

<!-- Jquery Core Js -->
<script src="{{url('/plugins/jquery/jquery.min.js')}}"></script>

<!-- Bootstrap Core Js -->
<script src="{{url('/plugins/bootstrap/js/bootstrap.js')}}"></script>

<!-- Select Plugin Js -->
<script src="{{url('/plugins/bootstrap-select/js/bootstrap-select.js')}}"></script>

<!-- Slimscroll Plugin Js -->
<script src="{{url('/plugins/jquery-slimscroll/jquery.slimscroll.js')}}"></script>

<!-- Waves Effect Plugin Js -->
<script src="{{url('/plugins/node-waves/waves.js')}}"></script>

<script type="text/javascript" src="{{url('/js/jquery.uploadPreview.min.js')}}"></script>


<!-- Multi Select Plugin Js -->
<script src="{{url('/plugins/multi-select/js/jquery.multi-select.js')}}"></script>

<!-- Autosize Plugin Js -->
<script src="{{url('/plugins/autosize/autosize.js')}}"></script>


<script src="{{url('/plugins/bootbox/bootbox.js')}}"></script>


<!-- Moment Plugin Js -->
<script src="{{url('/plugins/momentjs/moment.js')}}"></script>

<!-- Bootstrap Material Datetime Picker Plugin Js -->
{{--<script src="{{url('/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js')}}"></script>--}}

<!-- Custom Js -->
<script src="{{url('/js/admin.js')}}"></script>
<script src="{{url('js/pages/index.js')}}"></script>
{{--<script src="{{url('/js/pages/forms/basic-form-elements.js')}}"></script>--}}
<!-- Custom Js -->

{{--<script src="{{url('js/pages/forms/advanced-form-elements.js')}}"></script>--}}

<!-- Demo Js -->
<script src="{{url('/js/demo.js')}}"></script>


<!-- Jquery CountTo Plugin Js -->
<script src="{{url('/plugins/jquery-countto/jquery.countTo.js')}}"></script>

<script src="{{url('/plugins/sweetalert/sweetalert.min.js')}}"></script>




<script>


    (function () {
        function checkTime(i) {
            return (i < 10) ? "0" + i : i;
        }

        function startTime() {
            var today = new Date(),
                h = checkTime(today.getHours()),
                m = checkTime(today.getMinutes()),
                s = checkTime(today.getSeconds());
            document.getElementById('time').innerHTML = h + ":" + m + ":" + s;
            t = setTimeout(function () {
                startTime()
            }, 500);
        }
        startTime();
    })();


</script>

@yield('scripts')

</body>
</html>
