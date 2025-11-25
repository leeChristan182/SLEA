<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SLEA')</title>
    <link rel="icon" href="{{ asset('images/osas-logo.png') }}?v={{ filemtime(public_path('images/osas-logo.png')) }}"
        type="image/">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Global CSS -->
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('head')
</head>

<body class="d-flex flex-column min-vh-100 {{ session('dark_mode', false) ? 'dark-mode' : '' }}">
    {{-- Include Header --}}
    @include('partials.header')

    {{-- Global SLEA Award banner – shows ONLY for awarded students --}}
    @if(isset($currentRole, $sleaAwarded) && $currentRole === 'student' && $sleaAwarded)
        <div class="slea-global-banner">
            <div class="container slea-global-banner-inner">
                <div class="slea-global-medal">
                    <i class="fa-solid fa-medal"></i>
                </div>
                <div class="slea-global-text">
                    <div class="slea-global-heading">
                        Congratulations, {{ auth()->user()->first_name ?? 'Student' }}!
                    </div>
                    <div class="slea-global-sub">
                        You have been awarded the
                        <strong>Student Leadership Excellence Award</strong>.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex">
        {{-- Global Sidebar --}}

        {{-- Page Content (Main Area) --}}
        <main class="flex-grow-1">
            @yield('content')
        </main>
    </div>

    {{-- Include Footer --}}
    @include('partials.footer')

    <!-- JS Scripts -->
    ...
</body>


<!-- JS Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
@stack('scripts')
</body>

</html>