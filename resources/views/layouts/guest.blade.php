<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    @php
        $companySetting = \App\Services\SettingsService::getCompany();
        $favicon = $companySetting && $companySetting->favicon ? asset('storage/' . $companySetting->favicon) : asset('assets/images/favicon.ico');
    @endphp
    <title>Login | {{ $companySetting->company_name ?? 'Small Business ERP' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Small Business Accounting & Inventory ERP" name="description" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ $favicon }}">

    <!-- preloader css -->
    <link rel="stylesheet" href="{{ asset('assets/css/preloader.min.css') }}" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    @livewireStyles
</head>

<body>

    <div class="auth-page">
        <div class="container-fluid p-0">
            {{ $slot }}
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    @livewireScripts
</body>

</html>
