<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Barber Flow - Sistem Manajemen Barbershop">
    <meta name="author" content="Barber Flow Team">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('image/icon_univ_bsi.png') }}">
    <title>Barber Flow - Management System</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/extra-libs/multicheck/multicheck.css') }}">
    <link href="{{ asset('backend/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/dist/css/style.min.css') }}" rel="stylesheet">

    <!-- BARBERFLOW Custom Styles -->
    <style>
        :root {
            --carexis-primary: #1a1a2e;
            --carexis-secondary: #b8860b;
            --carexis-gradient: linear-gradient(135deg, #1a1a2e 0%, #b8860b 100%);
        }

        /* Navbar Gradient */
        .topbar {
            background: var(--carexis-gradient) !important;
        }

        .topbar .navbar-header {
            background: var(--carexis-gradient) !important;
        }

        /* Logo Text with Gradient */
        .carexis-logo-text {
            background: var(--carexis-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .carexis-subtitle {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: -5px;
            display: block;
        }

        /* Sidebar Gradient for Active Items */
        .sidebar-nav ul li.active > a,
        .sidebar-nav ul li.active > a:hover {
            background: var(--carexis-gradient) !important;
        }

        .sidebar-nav ul li:hover > a {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }

        /* Left Sidebar */
        .left-sidebar {
            background: #fff;
        }

        /* Buttons */
        .btn-primary {
            background: var(--carexis-gradient) !important;
            border: none !important;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        /* Footer */
        footer.footer {
            background: #f8f9fa;
            border-top: 2px solid;
            border-image: var(--carexis-gradient) 1;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 992px) {
            .topbar .navbar-header {
                min-height: 50px;
            }

            .carexis-logo-text {
                font-size: 18px;
            }

            .carexis-subtitle {
                font-size: 8px;
            }
        }

        @media (max-width: 768px) {
            body {
                margin: 0;
                padding: 0;
            }

            .main-wrapper {
                display: flex;
                flex-direction: column;
            }

            .topbar {
                min-height: 50px !important;
            }

            .topbar .navbar-header {
                padding: 0 10px !important;
            }

            .navbar-brand {
                padding: 5px 0 !important;
            }

            .carexis-logo-text {
                font-size: 16px;
                letter-spacing: 0.5px;
            }

            .carexis-subtitle {
                font-size: 7px;
                margin-top: -2px;
            }

            .nav-item {
                padding: 0 5px !important;
            }

            .nav-link {
                padding: 10px 8px !important;
                font-size: 14px !important;
            }

            .dropdown-menu {
                min-width: 160px !important;
                max-width: 280px !important;
            }

            .left-sidebar {
                min-width: 220px;
            }

            .sidebar-nav ul li a {
                padding: 12px 15px !important;
                font-size: 13px !important;
            }

            .page-wrapper {
                margin-left: 0 !important;
            }

            .page-breadcrumb {
                padding: 15px 15px !important;
            }

            .container-fluid {
                padding: 15px !important;
            }

            footer.footer {
                padding: 15px !important;
                font-size: 12px;
            }

            .hide-menu {
                display: none !important;
            }
        }

        @media (max-width: 576px) {
            .topbar {
                min-height: 45px !important;
            }

            .topbar .navbar-header {
                padding: 5px !important;
            }

            .navbar-brand {
                padding: 2px 5px !important;
            }

            .carexis-logo-text {
                font-size: 14px;
                letter-spacing: 0.3px;
            }

            .carexis-subtitle {
                font-size: 6px;
                margin-top: 0;
            }

            .nav-toggler {
                padding: 8px !important;
            }

            .topbartoggler {
                padding: 8px !important;
            }

            .dropdown-menu {
                min-width: 140px !important;
                max-width: 240px !important;
                font-size: 12px !important;
            }

            .dropdown-item {
                padding: 8px 12px !important;
                font-size: 12px !important;
            }

            .dropdown-header {
                padding: 8px 12px !important;
                font-size: 11px !important;
            }

            .nav-link {
                padding: 8px 5px !important;
                font-size: 12px !important;
            }

            .left-sidebar {
                min-width: 200px;
                position: fixed;
                left: 0;
                top: 45px;
                height: calc(100vh - 45px);
                overflow-y: auto;
                z-index: 999;
            }

            .sidebar-nav {
                padding-top: 10px !important;
            }

            .sidebar-nav ul li a {
                padding: 10px 12px !important;
                font-size: 12px !important;
            }

            .scroll-sidebar {
                width: 100%;
            }

            .page-breadcrumb {
                padding: 12px 12px !important;
            }

            .page-title {
                font-size: 16px !important;
            }

            .container-fluid {
                padding: 12px !important;
            }

            footer.footer {
                padding: 12px !important;
                font-size: 11px;
            }

            footer.footer .row {
                margin: 0 !important;
            }

            footer.footer .col-12 {
                padding: 0 !important;
            }

            .card {
                margin-bottom: 12px !important;
            }

            .card-body {
                padding: 0.75rem !important;
            }

            .table {
                font-size: 11px !important;
            }

            .badge {
                font-size: 9px !important;
                padding: 3px 6px !important;
            }

            .btn {
                padding: 6px 12px !important;
                font-size: 11px !important;
                min-height: 32px !important;
            }
        }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin5">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="{{ route('backend.beranda') }}">
                        <!-- Logo icon -->
                        <b class="logo-icon p-l-10">
                            <img src="{{ asset('image/logos/logo.png') }}" alt="Barber Flow Logo" style="height: 40px; width: auto;">
                        </b>
                        <!--End Logo icon -->
                         <!-- Logo text -->
                        <span class="logo-text" style="color: white;">
                            <div style="line-height: 1.2;">
                                <strong style="font-size: 20px; letter-spacing: 1px;">Barber Flow</strong>
                                <small class="carexis-subtitle" style="display: block; font-size: 9px; margin-top: -2px;">Barbershop Management System</small>
                            </div>
                        </span>
                    </a>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- Toggle which is visible on mobile only -->
                    <!-- ============================================================== -->
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                        <!-- ============================================================== -->
                        <!-- Quick Actions -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                             <span class="d-none d-md-block">Tambah Data <i class="fa fa-angle-down"></i></span>
                             <span class="d-block d-md-none"><i class="fa fa-plus"></i></span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('backend.barber.create') }}"><i class="mdi mdi-content-cut"></i> Barber Baru</a>
                                <a class="dropdown-item" href="{{ route('backend.layanan.create') }}"><i class="mdi mdi-scissors-cutting"></i> Layanan Baru</a>
                                <a class="dropdown-item" href="{{ route('backend.galeri.create') }}"><i class="mdi mdi-camera-plus"></i> Upload Galeri</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('backend.produk.create') }}"><i class="mdi mdi-bottle-tonic"></i> Produk Baru</a>
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- Search -->
                        <!-- ============================================================== -->
                        <li class="nav-item search-box"> <a class="nav-link waves-effect waves-dark" href="javascript:void(0)"><i class="ti-search"></i></a>
                            <form class="app-search position-absolute">
                                <input type="text" class="form-control" placeholder="Search &amp; enter"> <a class="srh-btn"><i class="ti-close"></i></a>
                            </form>
                        </li>
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-right">
                        <!-- ============================================================== -->
                        <!-- Notifications -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-bell font-24"></i>
                                <span class="badge badge-danger rounded-circle" style="position: absolute; top: 8px; right: 8px; font-size: 9px;">3</span>
                            </a>
                             <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <h6 class="dropdown-header">Notifikasi Terbaru</h6>
                                <a class="dropdown-item" href="{{ route('backend.notifikasi') }}"><i class="mdi mdi-account-alert text-warning"></i> Pengajuan cuti baru</a>
                                <a class="dropdown-item" href="{{ route('backend.notifikasi') }}"><i class="mdi mdi-calendar-clock text-info"></i> Jadwal shift minggu depan</a>
                                <a class="dropdown-item" href="{{ route('backend.notifikasi') }}"><i class="mdi mdi-package-variant text-success"></i> Aset baru ditambahkan</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="{{ route('backend.notifikasi') }}">Lihat Semua Notifikasi</a>
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- End Notifications -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- Messages -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="javascript:void(0)" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="font-24 mdi mdi-email"></i>
                                <span class="badge badge-info rounded-circle" style="position: absolute; top: 8px; right: 8px; font-size: 9px;">2</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right mailbox animated bounceInDown" aria-labelledby="2">
                                <ul class="list-style-none">
                                    <li>
                                        <div class="">
                                            <h6 class="dropdown-header">Pesan Terbaru</h6>
                                             <!-- Message -->
                                            <a href="{{ route('backend.pesan') }}" class="link border-top">
                                                <div class="d-flex no-block align-items-center p-10">
                                                    <span class="btn btn-success btn-circle"><i class="mdi mdi-calendar-check"></i></span>
                                                    <div class="m-l-10">
                                                        <h5 class="m-b-0">Jadwal Hari Ini</h5>
                                                        <span class="mail-desc">Pengingat jadwal shift Anda</span>
                                                    </div>
                                                </div>
                                            </a>
                                            <!-- Message -->
                                            <a href="{{ route('backend.pesan') }}" class="link border-top">
                                                <div class="d-flex no-block align-items-center p-10">
                                                    <span class="btn btn-info btn-circle"><i class="mdi mdi-account-multiple"></i></span>
                                                    <div class="m-l-10">
                                                        <h5 class="m-b-0">Update Tim</h5>
                                                        <span class="mail-desc">Ada update dari manajemen</span>
                                                    </div>
                                                </div>
                                            </a>
                                            <!-- Message -->
                                            <a href="{{ route('backend.pesan') }}" class="link border-top text-center">
                                                <strong>Lihat Semua Pesan</strong>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- End Messages -->
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown"> 
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria- expanded="false"> 
                                @if (Auth::user()->foto) 
                                <img src="{{ asset('storage/img-user/' . Auth::user()->foto) }}" alt="user" class="rounded-circle" width="31"> 
                                @else
                                <img src="{{ asset('storage/img-user/img-default.jpg') }}" alt="user" class="rounded-circle" width="31"> 
                                @endif 
                            </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <div class="d-flex align-items-center p-3 border-bottom">
                                    @if (Auth::user()->foto)
                                    <img src="{{ asset('storage/img-user/' . Auth::user()->foto) }}" alt="user" class="rounded-circle" width="60">
                                    @else
                                    <img src="{{ asset('storage/img-user/img-default.jpg') }}" alt="user" class="rounded-circle" width="60">
                                    @endif
                                    <div class="ml-3">
                                        <h5 class="mb-0">{{ Auth::user()->nama }}</h5>
                                        <small class="text-muted">{{ Auth::user()->email }}</small>
                                    </div>
                                </div>
                                <a class="dropdown-item" href="{{ route('backend.profil.index') }}"><i class="mdi mdi-account m-r-5 m-l-5"></i> Profil Saya</a>
                                @if(config('app.debug'))
                                <a class="dropdown-item" href="#" id="btn-quick-login-dashboard"><i class="mdi mdi-account-switch m-r-5 m-l-5"></i> Quick Login</a>
                                @endif
                                <a class="dropdown-item" href="{{ route('backend.setting.bantuan') }}"><i class="mdi mdi-help-circle m-r-5 m-l-5"></i> Bantuan</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="" onclick="event.preventDefault(); document.getElementById('keluar-app').submit();"><i class="mdi mdi-power m-r-5 m-l-5"></i> Keluar</a>
                            </div> 
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin5">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar"> 
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav" class="p-t-30">

                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link"
                            href="{{ route('backend.beranda') }}"
                            aria-expanded="false">
                                <i class="mdi mdi-view-dashboard"></i>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>

                        {{-- BARBER --}}
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                                <i class="mdi mdi-content-cut"></i>
                                <span class="hide-menu">Barber</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.barber.index') }}" class="sidebar-link">
                                        <i class="mdi mdi-account-check"></i>
                                        <span class="hide-menu">Data Barber</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.galeri.index') }}" class="sidebar-link">
                                        <i class="mdi mdi-image-multiple"></i>
                                        <span class="hide-menu">Galeri Foto</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- BOOKING --}}
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark" href="{{ route('backend.order.index') }}" aria-expanded="false">
                                <i class="mdi mdi-calendar-check"></i>
                                <span class="hide-menu">Booking</span>
                            </a>
                        </li>

                        {{-- TRAFFIC & AKTIVITAS --}}
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark" href="{{ route('backend.aktivitas.index') }}" aria-expanded="false">
                                <i class="mdi mdi-pulse"></i>
                                <span class="hide-menu">Traffic &amp; Aktivitas</span>
                            </a>
                        </li>

                        {{-- INVENTARIS --}}
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                                <i class="mdi mdi-package-variant"></i>
                                <span class="hide-menu">Inventaris</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.kategori.index') }}" class="sidebar-link">
                                        <i class="mdi mdi-tag-multiple"></i>
                                        <span class="hide-menu">Kategori Produk</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.produk.index') }}" class="sidebar-link">
                                        <i class="mdi mdi-bottle-tonic"></i>
                                        <span class="hide-menu">Produk Rambut</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.layanan.index') }}" class="sidebar-link">
                                        <i class="mdi mdi-scissors-cutting"></i>
                                        <span class="hide-menu">Layanan</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- LAPORAN --}}
                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                                <i class="mdi mdi-file-document-multiple"></i>
                                <span class="hide-menu">Laporan</span>
                            </a>
                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.laporan.formproduk') }}" class="sidebar-link">
                                        <i class="mdi mdi-bottle-tonic"></i>
                                        <span class="hide-menu">Laporan Produk</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.laporan.formuser') }}" class="sidebar-link">
                                        <i class="mdi mdi-account-multiple"></i>
                                        <span class="hide-menu">Laporan User</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- USER MANAGEMENT --}}
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark" href="{{ route('backend.user.index') }}" aria-expanded="false">
                                <i class="mdi mdi-account-supervisor"></i>
                                <span class="hide-menu">User Management</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a class="sidebar-link has-arrow waves-effect waves-dark"
                            href="javascript:void(0)"
                            aria-expanded="false">
                                <i class="mdi mdi-cog"></i>
                                <span class="hide-menu">Pengaturan</span>
                            </a>

                            <ul aria-expanded="false" class="collapse first-level">
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.setting.sistem') }}" class="sidebar-link">
                                        <i class="mdi mdi-settings"></i>
                                        <span class="hide-menu">Sistem</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.setting.backup') }}" class="sidebar-link">
                                        <i class="mdi mdi-database"></i>
                                        <span class="hide-menu">Backup & Restore</span>
                                    </a>
                                </li>
                                <li class="sidebar-item">
                                    <a href="{{ route('backend.setting.log') }}" class="sidebar-link">
                                        <i class="mdi mdi-file-document"></i>
                                        <span class="hide-menu">Log Aktivitas</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            @hasSection('breadcrumb')
                <div class="page-breadcrumb">
                    <div class="row">
                        <div class="col-12 d-flex no-block align-items-center">
                            @yield('breadcrumb')
                        </div>
                    </div>
                </div>
            @endif
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                @yield('content')
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center">
                <div class="row">
                    <div class="col-12">
                        <strong style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">BARBERFLOW</strong>
                        - Barbershop Management System untuk Barbershop Modern
                        <br>
                        <small class="text-muted">&copy; 2025 BARBERFLOW. All Rights Reserved.</small>
                    </div>
                </div>
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="{{ asset('backend/libs/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{ asset('backend/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('backend/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="{{ asset('backend/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('backend/extra-libs/sparkline/sparkline.js') }}"></script>
    <!--Wave Effects -->
    <script src="{{ asset('backend/dist/js/waves.js') }}"></script>
    <!--Menu sidebar -->
    <script src="{{ asset('backend/dist/js/sidebarmenu.js') }}"></script>
    <!--Custom JavaScript -->
    <script src="{{ asset('backend/dist/js/custom.min.js') }}"></script>
    <!-- this page js -->
    <script src="{{ asset('backend/extra-libs/multicheck/datatable-checkbox-init.js') }}"></script>
    <script src="{{ asset('backend/extra-libs/multicheck/jquery.multicheck.js') }}"></script>
    <script src="{{ asset('backend/extra-libs/DataTables/datatables.min.js') }}"></script>

    @stack('scripts')

    <!-- sweetalert -->
    <script src="{{ asset('sweetalert/sweetalert2.all.min.js') }}"></script>
    <!-- sweetalert End -->

    <!-- CKEditor -->
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <!-- <script src="https://cdn.ckeditor.com/ckeditor5/30.0.0/classic/ckeditor.js"></script> -->
    <script>
        if (document.querySelector('#ckeditor')) {
            ClassicEditor
                .create(document.querySelector('#ckeditor'))
                .catch(error => {
                    console.error(error);
                });
        }
    </script>
    <!-- CKEditor End -->

    <!-- konfirmasi success-->
    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}"
        });
    </script>
    @endif
    <!-- konfirmasi success End-->

    <!-- konfirmasi permission denied -->
    @if (session('error_permission_title'))
    <script>
        Swal.fire({
            icon: 'error',
            title: "{{ session('error_permission_title') }}",
            text: "{{ session('error_permission_message') }}",
            confirmButtonText: 'OK',
            confirmButtonColor: '#d33'
        });
    </script>
    @endif
    <!-- konfirmasi permission denied End-->
    <script type="text/javascript">
        //Konfirmasi delete
        $('.show_confirm').click(function(event) {
            var form = $(this).closest("form");
            var konfdelete = $(this).data("konf-delete");
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Hapus Data?',
                html: "Data yang dihapus <strong>" + konfdelete + "</strong> tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, dihapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success')
                        .then(() => {
                            form.submit();
                        });
                }
            });
        });

        // previewfoto
        function previewFoto() {
            const foto = document.querySelector('input[name="foto"]');
            const fotoPreview = document.querySelector('.foto-preview');
            fotoPreview.style.display = 'block';
            const fotoReader = new FileReader();
            fotoReader.readAsDataURL(foto.files[0]);
            fotoReader.onload = function(fotoEvent) {
                fotoPreview.src = fotoEvent.target.result;
            }
        }
</script>

    
    <!-- form keluar app -->
    <form id="keluar-app" action="{{ route('backend.logout') }}" method="POST" class="d-none">
        @csrf
    </form>
    <!-- form keluar app end -->

    <!-- Quick Login Modal -->
    @if(config('app.debug'))
    <div id="quickLoginModalDashboard" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; overflow-y: auto; padding: 20px;">
        <div style="max-width: 900px; margin: 40px auto; background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px 30px; border-radius: 20px 20px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="color: white; margin: 0; font-size: 22px; font-weight: 600;">
                        <i class="fas fa-bolt"></i> Login Cepat - Testing
                    </h3>
                    <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0; font-size: 13px;">
                        Switch ke akun lain untuk testing (Mode DEBUG)
                    </p>
                </div>
                <button type="button" class="closeQuickLoginDashboard" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 30px;">
                <!-- Current User Info -->
                <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 10px; padding: 15px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center;">
                        <i class="fas fa-user-check" style="color: #0c5460; font-size: 18px; margin-right: 10px;"></i>
                        <div>
                            <strong style="color: #0c5460;">User Saat Ini</strong>
                            <p style="color: #0c5460; margin: 5px 0 0 0; font-size: 13px;">
                                Anda login sebagai: <strong>{{ Auth::user()->nama }}</strong> ({{ Auth::user()->email }})
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Warning Box -->
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 10px; padding: 15px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center;">
                        <i class="fas fa-exclamation-triangle" style="color: #856404; font-size: 18px; margin-right: 10px;"></i>
                        <div>
                            <strong style="color: #856404;">Mode Testing</strong>
                            <p style="color: #856404; margin: 5px 0 0 0; font-size: 13px;">
                                Fitur ini hanya tersedia dalam mode DEBUG. Password semua akun: <strong>P@55word</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="usersLoadingDashboard" style="text-align: center; padding: 40px;">
                    <div style="display: inline-block; position: relative; width: 80px; height: 80px;">
                        <div style="position: absolute; border: 4px solid #667eea; opacity: 1; border-radius: 50%; animation: lds-ripple 1s cubic-bezier(0, 0.2, 0.8, 1) infinite;"></div>
                        <div style="position: absolute; border: 4px solid #667eea; opacity: 1; border-radius: 50%; animation: lds-ripple 1s cubic-bezier(0, 0.2, 0.8, 1) infinite; animation-delay: -0.5s;"></div>
                    </div>
                    <p style="color: #667eea; margin-top: 20px;">Memuat daftar user...</p>
                </div>

                <!-- Users Grid -->
                <div id="usersGridDashboard" style="display: none; max-height: 500px; overflow-y: auto;">
                    <!-- Users will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes lds-ripple {
            0% {
                top: 36px;
                left: 36px;
                width: 0;
                height: 0;
                opacity: 1;
            }
            100% {
                top: 0px;
                left: 0px;
                width: 72px;
                height: 72px;
                opacity: 0;
            }
        }

        .closeQuickLoginDashboard:hover {
            background: rgba(255,255,255,0.3) !important;
            transform: rotate(90deg);
        }

        .quick-user-card-dashboard {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .quick-user-card-dashboard:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            transform: translateY(-3px);
        }

        .quick-user-card-dashboard.current-user {
            border-color: #28a745;
            background: #f0fff4;
        }

        .badge-super-admin {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .badge-admin {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .badge-staff-kep {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .badge-staff-inv {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .badge-viewer {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }

        @media (max-width: 768px) {
            #quickLoginModalDashboard > div {
                margin: 20px auto !important;
            }
        }
    </style>

    <script>
        // Quick Login Modal for Dashboard
        $(document).ready(function() {
            // Open Quick Login Modal
            $('#btn-quick-login-dashboard').on('click', function(e) {
                e.preventDefault();
                $('#quickLoginModalDashboard').fadeIn(300);
                loadUsersDashboard();
            });

            // Close Quick Login Modal
            $('.closeQuickLoginDashboard').on('click', function() {
                $('#quickLoginModalDashboard').fadeOut(300);
            });

            // Close on background click
            $('#quickLoginModalDashboard').on('click', function(e) {
                if (e.target.id === 'quickLoginModalDashboard') {
                    $(this).fadeOut(300);
                }
            });

            // Close on ESC key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#quickLoginModalDashboard').is(':visible')) {
                    $('#quickLoginModalDashboard').fadeOut(300);
                }
            });

            // Load users from API
            function loadUsersDashboard() {
                $('#usersLoadingDashboard').show();
                $('#usersGridDashboard').hide();

                $.ajax({
                    url: '{{ route("api.quick-login.users") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            renderUsersDashboard(response.users);
                        }
                    },
                    error: function(xhr) {
                        $('#usersLoadingDashboard').html(
                            '<div style="text-align: center; color: #dc3545;">' +
                            '<i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 10px;"></i>' +
                            '<p>Gagal memuat daftar user</p>' +
                            '</div>'
                        );
                    }
                });
            }

            // Render users grid
            function renderUsersDashboard(users) {
                const currentUserId = {{ Auth::id() }};
                let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">';

                users.forEach(function(user) {
                    const role = user.role || { name: 'no-role', display_name: 'No Role', permissions_count: 0 };
                    const badgeClass = getRoleBadgeClass(role.name);
                    const roleIcon = getRoleIcon(role.name);
                    const initial = user.nama.charAt(0).toUpperCase();
                    const isCurrentUser = user.id === currentUserId;

                    html += `
                        <div class="quick-user-card-dashboard ${isCurrentUser ? 'current-user' : ''}" style="background: ${isCurrentUser ? '#f0fff4' : 'white'}; border: 2px solid ${isCurrentUser ? '#28a745' : '#e0e0e0'}; border-radius: 12px; padding: 20px; transition: all 0.3s;">
                            <div style="display: flex; align-items: start; margin-bottom: 15px;">
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold; margin-right: 15px; flex-shrink: 0;">
                                    ${initial}
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <h5 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #333; overflow: hidden; text-overflow: ellipsis;">
                                        ${user.nama}
                                        ${isCurrentUser ? '<span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-left: 5px;">Anda</span>' : ''}
                                    </h5>
                                    <p style="margin: 0; font-size: 12px; color: #666; overflow: hidden; text-overflow: ellipsis;">${user.email}</p>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <span class="${badgeClass}" style="font-size: 11px; padding: 4px 10px; border-radius: 12px; display: inline-block;">
                                    <i class="${roleIcon}"></i> ${role.display_name}
                                </span>
                                <span style="background: #667eea; color: white; padding: 4px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; margin-left: 5px;">
                                    ${role.permissions_count} perms
                                </span>
                            </div>

                            ${user.pegawai ? `
                            <div style="font-size: 11px; color: #666; margin-bottom: 15px;">
                                <i class="fas fa-briefcase"></i> ${user.pegawai.jabatan}
                            </div>
                            ` : ''}

                            ${!isCurrentUser ? `
                            <form action="{{ url('backend/quick-login') }}/${user.id}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-quick-login-dashboard" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                                    <i class="fas fa-sign-in-alt"></i> Switch ke ${user.nama.split(' ')[0]}
                                </button>
                            </form>
                            ` : `
                            <div style="text-align: center; padding: 10px; background: #e7f5ec; border-radius: 8px; color: #28a745; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> User Aktif
                            </div>
                            `}
                        </div>
                    `;
                });

                html += '</div>';

                $('#usersLoadingDashboard').hide();
                $('#usersGridDashboard').html(html).fadeIn(300);
            }

            function getRoleBadgeClass(roleName) {
                const badges = {
                    'super-admin': 'badge-super-admin',
                    'admin': 'badge-admin',
                    'staff-kepegawaian': 'badge-staff-kep',
                    'staff-inventaris': 'badge-staff-inv',
                    'viewer': 'badge-viewer'
                };
                return badges[roleName] || 'badge badge-secondary';
            }

            function getRoleIcon(roleName) {
                const icons = {
                    'super-admin': 'fas fa-crown',
                    'admin': 'fas fa-user-shield',
                    'staff-kepegawaian': 'fas fa-users',
                    'staff-inventaris': 'fas fa-boxes',
                    'viewer': 'fas fa-eye'
                };
                return icons[roleName] || 'fas fa-user';
            }
        });
    </script>
    @endif

</body>

</html>
