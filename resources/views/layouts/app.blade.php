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
        
        .modal-backdrop {
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

<body class="d-flex flex-column min-vh-100 {{ session('dark_mode', false) ? 'dark-mode' : '' }}">
    {{-- Include Header --}}
    @include('partials.header')

    <div class="d-flex">
        {{-- Global Sidebar --}}

        {{-- Page Content (Main Area) --}}
        <main class="flex-grow-1">
            @yield('content')
        </main>
    </div>

    {{-- Include Footer --}}
    @include('partials.footer')

    {{-- Account Disabled Modal --}}
    @if(session('account_disabled'))
    <div id="accountDisabledModal" class="modal" style="display: flex; position: fixed; z-index: 10000;">
        <div class="modal-content" style="max-width: 500px; margin: auto; background: white; border-radius: 8px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
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
    <div class="modal-backdrop" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;"></div>
    @endif

    <!-- JS Scripts -->
    ...
</body>


<!-- JS Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>

{{-- Account Disabled Auto-Logout Script --}}
@if(session('account_disabled'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('accountDisabledModal');
        const backdrop = document.querySelector('.modal-backdrop');
        
        // Show modal immediately
        if (modal) {
            modal.style.display = 'flex';
        }
        if (backdrop) {
            backdrop.style.display = 'block';
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // After 3 seconds, start fade out (2 seconds animation), then logout
        setTimeout(function() {
            // Fade out modal over 2 seconds
            if (modal) {
                modal.style.transition = 'opacity 2s ease-out';
                modal.style.opacity = '0';
            }
            if (backdrop) {
                backdrop.style.transition = 'opacity 2s ease-out';
                backdrop.style.opacity = '0';
            }
            
            // After 2 second fade animation completes, logout
            setTimeout(function() {
                // Create and submit logout form
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
            }, 2000); // Wait for 2 second fade animation
        }, 3000); // 3 seconds before starting fade
    });
</script>
@endif

@stack('scripts')
</body>

</html>