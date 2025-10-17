@extends('layouts.app')

@section('title', 'Assessor Dashboard')

@section('content')
<div class="container">
    @include('partials.assessor-sidebar')

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Welcome back, {{ $user->name ?? 'Assessor' }}!</h1>
            <p>Here's an overview of your current tasks and submissions.</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3 id="pendingCount">5</h3>
                    <p>All Submissions</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <h3 id="reviewedCount">12</h3>
                    <p>Reviewed Today</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 id="completedCount">48</h3>
                    <p>Total Completed</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3 id="avgTime">2.5h</h3>
                    <p>Avg. Review Time</p>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="activity-content">
                            <p><strong>DELA CRUZ, Juan M.</strong> submitted Leadership Portfolio</p>
                            <span class="activity-time">2 hours ago</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="activity-content">
                            <p><strong>SANTOS, Maria A.</strong> submission reviewed and approved</p>
                            <span class="activity-time">4 hours ago</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="activity-content">
                            <p><strong>GARCIA, Pedro L.</strong> submission awaiting review</p>
                            <span class="activity-time">6 hours ago</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="{{ route('assessor.submissions') }}" class="action-btn primary">
                        <i class="fas fa-clock"></i>
                        <span>Review Pending</span>
                    </a>
                    <a href="{{ route('assessor.submissions') }}" class="action-btn secondary">
                        <i class="fas fa-file-alt"></i>
                        <span>View All Submissions</span>
                    </a>
                    <a href="{{ route('assessor.profile') }}" class="action-btn secondary">
                        <i class="fas fa-user"></i>
                        <span>Update Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        color: #333;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .dashboard-header p {
        color: #666;
        font-size: 1.1rem;
    }

    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .stat-card {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        background: #dc3545;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .stat-content h3 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin: 0;
    }

    .stat-content p {
        color: #666;
        margin: 0.5rem 0 0 0;
        font-size: 0.9rem;
    }

    .dashboard-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    .recent-activity,
    .quick-actions {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .recent-activity h2,
    .quick-actions h2 {
        color: #333;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 6px;
        transition: background 0.2s ease;
    }

    .activity-item:hover {
        background: #f8f9fa;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc3545;
    }

    .activity-content p {
        margin: 0;
        color: #333;
    }

    .activity-time {
        color: #666;
        font-size: 0.8rem;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }

    .action-btn.primary {
        background: #dc3545;
        color: white;
    }

    .action-btn.primary:hover {
        background: #c82333;
        transform: translateY(-1px);
    }

    .action-btn.secondary {
        background: #f8f9fa;
        color: #333;
        border-color: #dee2e6;
    }

    .action-btn.secondary:hover {
        background: #e9ecef;
        border-color: #dc3545;
    }

    .action-btn i {
        font-size: 1.2rem;
    }

    @media (max-width: 768px) {
        .dashboard-content {
            grid-template-columns: 1fr;
        }

        .dashboard-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
