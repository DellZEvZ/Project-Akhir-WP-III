<!DOCTYPE html>
<html dir="ltr" lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#667eea">
    <meta name="description" content="BARBERFLOW - Barbershop Management System">
    <meta name="author" content="BARBERFLOW">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('image/icon_univ_bsi.png') }}">
    <title>BARBERFLOW - Barbershop Management System</title>
    <!-- Custom CSS -->
    <link href="{{ asset('backend/dist/css/style.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- ============================================== -->
    <!-- SweetAlert2 CSS -->
    <!-- ============================================== -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -200px;
            right: -200px;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -150px;
            left: -150px;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .main-wrapper {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lds-ripple {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .lds-ripple .lds-pos {
            position: absolute;
            border: 4px solid #fff;
            opacity: 1;
            border-radius: 50%;
            animation: lds-ripple 1s cubic-bezier(0, 0.2, 0.8, 1) infinite;
        }

        .lds-ripple .lds-pos:nth-child(2) {
            animation-delay: -0.5s;
        }

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

        .auth-wrapper {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }

        .auth-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            border-bottom: 3px solid #5a67d8;
        }

        .logo-text {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logo-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 8px;
            font-weight: 400;
            letter-spacing: 1px;
        }

        #loginform, #recoverform {
            padding: 40px 30px;
        }

        #recoverform {
            display: none;
        }

        .form-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
            text-align: center;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 12px 15px;
            font-size: 14px;
        }

        .alert-danger {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group-prepend {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            z-index: 10;
        }

        .input-group-text {
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 45px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px 0 0 10px;
            font-size: 18px;
        }

        .form-control {
            height: 48px;
            padding-left: 55px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-link {
            background: transparent;
            color: #667eea;
            padding: 10px 15px;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            font-weight: 500;
        }

        .btn-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .form-actions {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .recover-text {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            text-align: center;
            margin-bottom: 20px;
        }

        .text-center {
            text-align: center;
        }

        .float-right {
            float: right;
        }

        @media (max-width: 768px) {
            .main-wrapper {
                padding: 15px;
                min-height: auto;
            }

            .auth-wrapper {
                max-width: 100%;
                width: 100%;
            }

            .auth-box {
                margin: 0;
                border-radius: 15px;
            }

            .logo-section {
                padding: 30px 20px;
            }

            .logo-text {
                font-size: 28px;
            }

            .logo-subtitle {
                font-size: 12px;
            }

            #loginform, #recoverform {
                padding: 25px 20px;
            }

            .form-title {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .input-group {
                margin-bottom: 15px;
            }

            .form-control {
                height: 44px;
                font-size: 16px;
                padding-left: 50px;
            }

            .input-group-text {
                width: 44px;
                height: 44px;
                font-size: 16px;
            }

            .btn {
                padding: 11px 20px;
                font-size: 13px;
                min-height: 44px;
                min-width: 44px;
            }

            .form-actions {
                flex-direction: column;
                gap: 10px;
            }

            .btn-primary {
                width: 100%;
            }

            .btn-link {
                width: 100%;
                padding: 10px 15px;
            }

            .btn-secondary {
                width: 100%;
            }

            .float-right {
                float: none;
            }

            .alert {
                font-size: 13px;
                padding: 10px 12px;
            }

            .recover-text {
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .main-wrapper {
                padding: 10px;
            }

            .auth-box {
                border-radius: 12px;
            }

            .logo-section {
                padding: 25px 15px;
            }

            .logo-text {
                font-size: 24px;
                letter-spacing: 1px;
            }

            .logo-subtitle {
                font-size: 11px;
                margin-top: 5px;
            }

            #loginform, #recoverform {
                padding: 20px 15px;
            }

            .form-title {
                font-size: 18px;
                margin-bottom: 18px;
            }

            .input-group {
                margin-bottom: 12px;
            }

            .form-control {
                height: 42px;
                font-size: 16px;
                padding-left: 48px;
            }

            .input-group-text {
                width: 42px;
                height: 42px;
                font-size: 14px;
            }

            .btn {
                padding: 10px 15px;
                font-size: 12px;
                min-height: 40px;
            }

            .form-actions {
                margin-top: 20px;
                padding-top: 15px;
            }

            .alert {
                font-size: 12px;
                padding: 8px 10px;
            }

            .recover-text {
                font-size: 12px;
                line-height: 1.5;
                margin-bottom: 18px;
            }
        }

        /* Icon styling */
        .ti-user::before, .fas.fa-user::before { content: '\f007'; font-family: 'Font Awesome 5 Free'; font-weight: 900; }
        .ti-pencil::before, .fas.fa-lock::before { content: '\f023'; font-family: 'Font Awesome 5 Free'; font-weight: 900; }
        .ti-email::before, .fas.fa-envelope::before { content: '\f0e0'; font-family: 'Font Awesome 5 Free'; font-weight: 900; }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <div class="main-wrapper">
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
        <!-- Login box -->
        <!-- ============================================================== -->
        <div class="auth-wrapper">
            <div class="auth-box">
                <!-- Logo Section -->
                <div class="logo-section">
                    <h1 class="logo-text">BARBERFLOW</h1>
                    <p class="logo-subtitle">Barbershop Management System</p>
                </div>

                <!-- Login Form -->
                <div id="loginform">
                    <h2 class="form-title">Masuk ke Akun Anda</h2>

                    <!-- Error Alert (Fallback for browsers without JS) -->
                    @if(session()->has('error'))
                        @if(is_array(session('error')))
                            <!-- Error message will be shown via SweetAlert, keeping fallback -->
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="position: absolute; right: 10px; top: 8px; background: none; border: none; font-size: 20px; color: #c33; cursor: pointer;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <strong>{{ session('error.title') }}</strong>: {{ session('error.message') }}
                            </div>
                        @else
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="position: absolute; right: 10px; top: 8px; background: none; border: none; font-size: 20px; color: #c33; cursor: pointer;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <strong>{{ session('error') }}</strong>
                            </div>
                        @endif
                    @endif

                    <!-- Login Form -->
                    <form action="{{ route('backend.login') }}" method="post">
                        @csrf

                        <!-- Email Input -->
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="email-addon">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="Masukkan Email"
                                   aria-label="Email"
                                   aria-describedby="email-addon"
                                   required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="password-addon">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                            <input type="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Masukkan Password"
                                   aria-label="Password"
                                   aria-describedby="password-addon"
                                   required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-link" id="to-recover">
                                <i class="fas fa-lock"></i> Lupa Password?
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>

                        <!-- Quick Login for Testing (Only in DEBUG mode) -->
                        @if(app()->isLocal() && config('app.debug'))
                        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                            <button type="button" class="btn btn-link" style="color: #667eea; font-size: 13px;" id="btn-quick-login">
                                <i class="fas fa-bolt"></i> Login Cepat - Testing
                            </button>
                        </div>
                        @endif
                    </form>
                </div>

                <!-- Recover Password Form -->
                <div id="recoverform">
                    <h2 class="form-title">Pulihkan Password</h2>

                    <p class="recover-text">
                        Masukkan alamat email Anda di bawah ini dan kami akan mengirimkan instruksi untuk memulihkan password Anda.
                    </p>

                    <form action="#" method="post">
                        @csrf

                        <!-- Email Input for Recovery -->
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="recover-email-addon">
                                    <i class="fas fa-envelope"></i>
                                </span>
                            </div>
                            <input type="email"
                                   name="recover_email"
                                   class="form-control"
                                   placeholder="Alamat Email"
                                   aria-label="Email"
                                   aria-describedby="recover-email-addon"
                                   required>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" id="to-login">
                                <i class="fas fa-arrow-left"></i> Kembali ke Login
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Login box -->
        <!-- ============================================================== -->

        <!-- Quick Login Modal -->
        @if(config('app.debug'))
        <div id="quickLoginModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; overflow-y: auto; padding: 20px;">
            <div style="max-width: 900px; margin: 40px auto; background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); animation: slideUp 0.3s ease-out;">
                <!-- Modal Header -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px 30px; border-radius: 20px 20px 0 0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="color: white; margin: 0; font-size: 22px; font-weight: 600;">
                            <i class="fas fa-bolt"></i> Login Cepat - Testing
                        </h3>
                        <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0; font-size: 13px;">
                            Pilih akun untuk login langsung (Mode DEBUG)
                        </p>
                    </div>
                    <button type="button" id="closeQuickLogin" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div style="padding: 30px;">
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
                    <div id="usersLoading" style="text-align: center; padding: 40px;">
                        <div class="lds-ripple">
                            <div class="lds-pos"></div>
                            <div class="lds-pos"></div>
                        </div>
                        <p style="color: #667eea; margin-top: 20px;">Memuat daftar user...</p>
                    </div>

                    <!-- Users Grid -->
                    <div id="usersGrid" style="display: none; max-height: 500px; overflow-y: auto;">
                        <!-- Users will be populated here -->
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- All Required js -->
    <!-- ============================================================== -->
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="{{ asset('backend/libs/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{ asset('backend/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('backend/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- ============================================================== -->
    <!-- This page plugin js -->
    <!-- ============================================================== -->
    <script>
        // =======================================================
        // SweetAlert Notifications
        // =======================================================
        @if(session()->has('error'))
        Swal.fire({
            icon: '{{ session("error.type") ?? "error" }}',
            title: '{{ session("error.title") }}',
            text: '{{ session("error.message") }}',
            confirmButtonColor: '#667eea',
            confirmButtonText: 'OK',
            timer: 5000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
        @endif

        @if(session()->has('success'))
        Swal.fire({
            icon: '{{ session("success.type") ?? "success" }}',
            title: '{{ session("success.title") }}',
            text: '{{ session("success.message") }}',
            confirmButtonColor: '#667eea',
            confirmButtonText: 'OK',
            timer: 3000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
        @endif

        @if($errors->any())
        let errorMessages = '';
        @foreach($errors->all() as $error)
            errorMessages += '{{ $error }}<br>';
        @endforeach
        
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Validasi!',
            html: errorMessages,
            confirmButtonColor: '#667eea',
            confirmButtonText: 'OK'
        });
        @endif
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Fade out preloader
        $(".preloader").fadeOut(500);

        // ==============================================================
        // Login and Recover Password Toggle
        // ==============================================================
        $('#to-recover').on("click", function (e) {
            e.preventDefault();
            $("#loginform").slideUp(300, function() {
                $("#recoverform").fadeIn(400);
            });
        });

        $('#to-login').on("click", function (e) {
            e.preventDefault();
            $("#recoverform").fadeOut(300, function() {
                $("#loginform").slideDown(400);
            });
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(500);
        }, 5000);

        // Close button for alerts
        $('.close').on('click', function() {
            $(this).parent('.alert').fadeOut(300);
        });

        @if(config('app.debug'))
        // ==============================================================
        // Quick Login Modal Functions
        // ==============================================================
        let usersData = [];

        // Open Quick Login Modal
        $('#btn-quick-login').on('click', function(e) {
            e.preventDefault();
            $('#quickLoginModal').fadeIn(300);
            loadUsers();
        });

        // Close Quick Login Modal
        $('#closeQuickLogin').on('click', function() {
            $('#quickLoginModal').fadeOut(300);
        });

        // Close on background click
        $('#quickLoginModal').on('click', function(e) {
            if (e.target.id === 'quickLoginModal') {
                $(this).fadeOut(300);
            }
        });

        // Close on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#quickLoginModal').is(':visible')) {
                $('#quickLoginModal').fadeOut(300);
            }
        });

        // Load users from API
        function loadUsers() {
            $('#usersLoading').show();
            $('#usersGrid').hide();

            $.ajax({
                url: '{{ url("api/quick-login/users") }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        usersData = response.users;
                        renderUsers(response.users);
                    }
                },
                error: function(xhr) {
                    $('#usersLoading').html(
                        '<div style="text-align: center; color: #dc3545;">' +
                        '<i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 10px;"></i>' +
                        '<p>Gagal memuat daftar user</p>' +
                        '</div>'
                    );
                }
            });
        }

        // Render users grid
        function renderUsers(users) {
            let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">';

            users.forEach(function(user) {
                const role = user.role || { name: 'no-role', display_name: 'No Role', permissions_count: 0 };
                const badgeClass = getRoleBadgeClass(role.name);
                const roleIcon = getRoleIcon(role.name);
                const initial = user.nama.charAt(0).toUpperCase();

                html += `
                    <div class="quick-user-card" style="background: white; border: 2px solid #e0e0e0; border-radius: 12px; padding: 20px; transition: all 0.3s; cursor: pointer;">
                        <div style="display: flex; align-items: start; margin-bottom: 15px;">
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold; margin-right: 15px; flex-shrink: 0;">
                                ${initial}
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h5 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #333; overflow: hidden; text-overflow: ellipsis;">${user.nama}</h5>
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

                        <form action="{{ url('backend/quick-login') }}/${user.id}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn-quick-login" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                                <i class="fas fa-sign-in-alt"></i> Login sebagai ${user.nama.split(' ')[0]}
                            </button>
                        </form>
                    </div>
                `;
            });

            html += '</div>';

            $('#usersLoading').hide();
            $('#usersGrid').html(html).fadeIn(300);

            // Add hover effects
            $('.quick-user-card').hover(
                function() {
                    $(this).css({
                        'border-color': '#667eea',
                        'box-shadow': '0 5px 15px rgba(102, 126, 234, 0.3)',
                        'transform': 'translateY(-3px)'
                    });
                },
                function() {
                    $(this).css({
                        'border-color': '#e0e0e0',
                        'box-shadow': 'none',
                        'transform': 'translateY(0)'
                    });
                }
            );

            $('.btn-quick-login').hover(
                function() {
                    $(this).css({
                        'transform': 'scale(1.02)',
                        'box-shadow': '0 4px 10px rgba(102, 126, 234, 0.4)'
                    });
                },
                function() {
                    $(this).css({
                        'transform': 'scale(1)',
                        'box-shadow': 'none'
                    });
                }
            );
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
        @endif
    </script>

    <style>
        @if(config('app.debug'))
        #closeQuickLogin:hover {
            background: rgba(255,255,255,0.3) !important;
            transform: rotate(90deg);
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
            #quickLoginModal > div {
                margin: 20px auto !important;
            }

            #usersGrid > div {
                grid-template-columns: 1fr !important;
            }
        }
        @endif
    </style>
</body>
</html>