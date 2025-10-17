<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - SLEA</title>
    <link rel="icon" href="{{ asset('images/osas-logo.png') }}?v={{ filemtime(public_path('images/osas-logo.png')) }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Global CSS -->
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        .dashboard-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .welcome-section {
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .quick-action-btn {
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
            text-align: center;
        }

        .quick-action-btn:hover {
            background: linear-gradient(135deg, #A52A2A 0%, #8B0000 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }

        .nav-link {
            color: #8B0000;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #A52A2A;
        }

        .nav-link.active {
            color: #8B0000;
            background-color: rgba(139, 0, 0, 0.1);
            border-radius: 8px;
        }
    </style>
</head>

<body class="dashboard-container">
    <div class="header-container">
        <div class="header">
            <div class="d-flex align-items-center gap-3">
                <!-- Logo -->
                <img src="{{ asset('images/osas-logo.png') }}" alt="USeP Logo" height="60">
                <span class="fs-3 fw-bolder logo-text">SLEA</span>
                <div style="width: 1px; height: 40px; background-color: #ccc; margin: 0 0.5rem;"></div>

                <!-- Tagline -->
                <div class="tagline ms-3">
                    <span class="gold1">Empowering</span> <span class="maroon1">Leadership.</span><br>
                    <span class="maroon1">Recognizing</span> <span class="gold1">Excellence.</span>
                </div>
            </div>
            <div class="header-right d-flex align-items-center gap-3">
                <div class="text-end">
                    <small>Welcome, {{ $user->name }}</small><br>
                    <small class="text-muted">Student Dashboard</small>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="container py-4">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Debug Message -->
        <div class="alert alert-warning" style="margin-bottom: 2rem;">
            <strong>DEBUG:</strong> This is the STUDENT dashboard. If you see this message as an admin, there's a routing issue.
            <br>User: {{ $user->email ?? 'Not authenticated' }} | Role: {{ $user->user_role ?? 'No role' }}
        </div>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-3">Welcome back, {{ $user->name }}!</h1>
                    <p class="mb-0 fs-5">Ready to showcase your leadership excellence? Let's get started with your submissions and track your progress.</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-graduation-cap" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="text-maroon mb-3">Quick Actions</h3>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('student.submit') }}" class="quick-action-btn">
                    <i class="fas fa-plus-circle mb-2" style="font-size: 2rem;"></i>
                    <h5>Submit Document</h5>
                    <p class="mb-0">Upload your leadership documents</p>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('student.performance') }}" class="quick-action-btn">
                    <i class="fas fa-chart-line mb-2" style="font-size: 2rem;"></i>
                    <h5>View Performance</h5>
                    <p class="mb-0">Track your progress and scores</p>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('student.criteria') }}" class="quick-action-btn">
                    <i class="fas fa-list-check mb-2" style="font-size: 2rem;"></i>
                    <h5>View Criteria</h5>
                    <p class="mb-0">Check evaluation criteria</p>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('student.history') }}" class="quick-action-btn">
                    <i class="fas fa-history mb-2" style="font-size: 2rem;"></i>
                    <h5>View History</h5>
                    <p class="mb-0">See your submission history</p>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <i class="fas fa-file-alt mb-2" style="font-size: 2rem;"></i>
                    <h3>0</h3>
                    <p class="mb-0">Documents Submitted</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <i class="fas fa-star mb-2" style="font-size: 2rem;"></i>
                    <h3>0</h3>
                    <p class="mb-0">Total Score</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <i class="fas fa-trophy mb-2" style="font-size: 2rem;"></i>
                    <h3>Pending</h3>
                    <p class="mb-0">Account Status</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card p-4">
                    <h4 class="text-maroon mb-3">Recent Activity</h4>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p>No recent activity to display. Start by submitting your first document!</p>
                        <a href="{{ route('student.submit') }}" class="btn btn-primary">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
