<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SLEA')</title>

    <link rel="icon" href="{{ asset('images/osas-logo.png') }}?v={{ filemtime(public_path('images/osas-logo.png')) }}"
        type="image/png">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Prevent caching (for back/forward issues) --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Auth flag (for SessionTimeout) --}}
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <!-- Global CSS -->
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Account Disabled Modal Styles --}}
    <style>
        #accountDisabledModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }

        body.dark-mode #accountDisabledModal .modal-content {
            background: #2a2a2a;
            color: #f0f0f0;
        }

        body.dark-mode #accountDisabledModal .modal-body p {
            color: #f0f0f0;
        }
    </style>

    @yield('head')
</head>

<body class="d-flex flex-column min-vh-100
             {{ session('dark_mode', false) ? 'dark-mode' : '' }}
             {{ auth()->check() ? 'authenticated' : 'guest' }}">

    {{-- Header --}}
    @include('partials.header')

    <div class="d-flex">
        {{-- Sidebar (if any) --}}
        {{-- @include('partials.sidebar') --}}

        {{-- Main Content --}}
        <main class="flex-grow-1">
            @yield('content')
        </main>
    </div>

    {{-- Footer --}}
    @include('partials.footer')

    {{-- Account Disabled Modal --}}
    @if(session('account_disabled'))
        <div id="accountDisabledModal" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 500px; margin: auto; background: white; border-radius: 8px;
                                            padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div class="modal-header" style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 48px; color: #dc3545; margin-bottom: 15px;">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h3 style="color: #dc3545; margin: 0; font-weight: 700;">Account Disabled</h3>
                </div>
                <div class="modal-body" style="text-align: center; margin-bottom: 20px;">
                    <p style="font-size: 16px; color: #333; margin: 0;">
                        Your account has been disabled by an administrator. You will be logged out automatically.
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-backdrop-custom"></div>
    @endif

    {{-- JS Scripts (only once) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Session timeout handler --}}
    <script src="{{ asset('js/session-timeout.js') }}"></script>

    {{-- Initialize SessionTimeout (TEST VALUES) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isAuthenticated =
                document.querySelector('meta[name="user-authenticated"]')?.content === 'true' ||
                document.body.classList.contains('authenticated');

            if (!isAuthenticated) return;

            // Ask for notification permission once (optional, you can move this behind a button too)
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(function (result) {
                    console.log('Notification permission:', result);
                });
            }

            new SessionTimeout({
                warningTime: 5 * 60 * 1000,   // 5 min
                timeoutTime: 10 * 60 * 1000,  // 10 min
                checkInterval: 30 * 1000,     // 30 sec
            });

        });
    </script>

    {{-- Account Disabled Auto-Logout Script --}}
    @if(session('account_disabled'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('accountDisabledModal');
                const backdrop = document.querySelector('.modal-backdrop-custom');

                // Prevent body scroll
                document.body.style.overflow = 'hidden';

                // After 3 seconds, start fade out (2 seconds), then logout
                setTimeout(function () {
                    if (modal) {
                        modal.style.transition = 'opacity 2s ease-out';
                        modal.style.opacity = '0';
                    }
                    if (backdrop) {
                        backdrop.style.transition = 'opacity 2s ease-out';
                        backdrop.style.opacity = '0';
                    }

                    setTimeout(function () {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("logout") }}';

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        form.appendChild(csrfInput);

                        document.body.appendChild(form);
                        form.submit();
                    }, 2000);
                }, 3000);
            });
        </script>
    @endif

    @stack('scripts')
</body>

</html>