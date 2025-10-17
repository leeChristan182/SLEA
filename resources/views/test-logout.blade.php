@extends('layouts.app')

@section('title', 'Logout Test - SLEA')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Logout & Session Timeout Test
                    </h4>
                </div>
                <div class="card-body">
                    @auth
                    <div class="alert alert-success">
                        <h5><i class="fas fa-user-check me-2"></i>You are logged in!</h5>
                        <p><strong>User:</strong> {{ auth()->user()->name }}</p>
                        <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                        <p><strong>Role:</strong> {{ ucfirst(auth()->user()->user_role) }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Session Information</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Session Lifetime:</span>
                                    <span id="session-lifetime">Loading...</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Last Activity:</span>
                                    <span id="last-activity">Loading...</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Session Status:</span>
                                    <span class="badge bg-success" id="session-status">Active</span>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h6>Test Actions</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="checkSession()">
                                    <i class="fas fa-sync me-1"></i>
                                    Check Session
                                </button>
                                <button class="btn btn-warning" onclick="simulateIdle()">
                                    <i class="fas fa-clock me-1"></i>
                                    Simulate Idle (5 min)
                                </button>
                                <button class="btn btn-danger" onclick="logout()">
                                    <i class="fas fa-sign-out-alt me-1"></i>
                                    Logout Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6>Idle Timeout Test</h6>
                        <p class="text-muted">
                            The system will show a warning after 5 minutes of inactivity and automatically logout after 10 minutes.
                            You can test this by clicking "Simulate Idle" or by not interacting with the page for 5+ minutes.
                        </p>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%" id="idle-progress"></div>
                        </div>
                        <small class="text-muted" id="idle-timer">Idle time: 0 seconds</small>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Not Logged In</h5>
                        <p>Please log in to test the logout functionality.</p>
                        <a href="{{ route('login.show') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Go to Login
                        </a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

@auth
<script>
    let idleStartTime = Date.now();
    let idleTimer = null;

    // Load session information
    document.addEventListener('DOMContentLoaded', function() {
        checkSession();
        startIdleTimer();
    });

    function checkSession() {
        fetch('/test-auth')
            .then(response => response.json())
            .then(data => {
                document.getElementById('session-lifetime').textContent = data.session_lifetime + ' minutes';
                document.getElementById('last-activity').textContent = data.last_activity ?
                    new Date(data.last_activity * 1000).toLocaleString() : 'Not set';
                document.getElementById('session-status').textContent = data.authenticated ? 'Active' : 'Expired';
                document.getElementById('session-status').className = data.authenticated ?
                    'badge bg-success' : 'badge bg-danger';
            })
            .catch(error => {
                console.error('Session check failed:', error);
                document.getElementById('session-status').textContent = 'Error';
                document.getElementById('session-status').className = 'badge bg-danger';
            });
    }

    function simulateIdle() {
        // Simulate 5 minutes of idle time
        idleStartTime = Date.now() - (5 * 60 * 1000);
        updateIdleProgress();
    }

    function startIdleTimer() {
        idleTimer = setInterval(updateIdleProgress, 1000);
    }

    function updateIdleProgress() {
        const now = Date.now();
        const idleTime = now - idleStartTime;
        const idleSeconds = Math.floor(idleTime / 1000);
        const idleMinutes = Math.floor(idleSeconds / 60);

        document.getElementById('idle-timer').textContent = `Idle time: ${idleSeconds} seconds`;

        // Update progress bar (5 minutes = 100%)
        const progress = Math.min((idleTime / (5 * 60 * 1000)) * 100, 100);
        const progressBar = document.getElementById('idle-progress');
        progressBar.style.width = progress + '%';

        if (progress >= 100) {
            progressBar.className = 'progress-bar bg-warning';
        }
    }

    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            fetch('/ajax-logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect_url;
                    } else {
                        window.location.href = '/';
                    }
                })
                .catch(error => {
                    console.error('Logout failed:', error);
                    window.location.href = '/';
                });
        }
    }

    // Reset idle timer on any activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
        document.addEventListener(event, () => {
            idleStartTime = Date.now();
            document.getElementById('idle-progress').className = 'progress-bar';
        }, true);
    });
</script>
@endauth
@endsection


