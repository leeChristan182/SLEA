/**
 * Session Timeout Management
 * Handles idle timeout detection and user notifications
 */
class SessionTimeout {
    constructor(options = {}) {
        this.options = {
            warningTime: 5 * 60 * 1000, // 5 minutes in milliseconds
            timeoutTime: 10 * 60 * 1000, // 10 minutes in milliseconds
            checkInterval: 30 * 1000, // Check every 30 seconds
            warningMessage: 'Your session will expire in {time} minutes due to inactivity. Do you want to stay logged in?',
            timeoutMessage: 'Your session has expired due to inactivity. You will be redirected to the login page.',
            ...options
        };

        this.isWarningShown = false;
        this.isTimedOut = false;
        this.lastActivity = Date.now();
        this.warningTimer = null;
        this.timeoutTimer = null;
        this.checkTimer = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.startCheckTimer();
        this.startWarningTimer();
    }

    bindEvents() {
        // Track user activity
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

        events.forEach(event => {
            document.addEventListener(event, () => {
                this.resetTimers();
            }, true);
        });

        // Handle visibility change (tab switching)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.resetTimers();
            }
        });

        // Handle page unload
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });
    }

    resetTimers() {
        if (this.isTimedOut) return;

        this.lastActivity = Date.now();
        this.isWarningShown = false;

        // Clear existing timers
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
        }
        if (this.timeoutTimer) {
            clearTimeout(this.timeoutTimer);
        }

        // Start new timers
        this.startWarningTimer();
    }

    startWarningTimer() {
        this.warningTimer = setTimeout(() => {
            this.showWarning();
        }, this.options.warningTime);
    }

    startTimeoutTimer() {
        this.timeoutTimer = setTimeout(() => {
            this.handleTimeout();
        }, this.options.timeoutTime);
    }

    startCheckTimer() {
        this.checkTimer = setInterval(() => {
            this.checkSessionStatus();
        }, this.options.checkInterval);
    }

    showWarning() {
        if (this.isWarningShown || this.isTimedOut) return;

        this.isWarningShown = true;
        const remainingTime = Math.ceil((this.options.timeoutTime - this.options.warningTime) / 60000);

        const message = this.options.warningMessage.replace('{time}', remainingTime);

        // Create warning modal
        this.createWarningModal(message, remainingTime);
    }

    createWarningModal(message, remainingTime) {
        // Remove existing modal if any
        const existingModal = document.getElementById('session-warning-modal');
        if (existingModal) {
            existingModal.remove();
        }

        const modal = document.createElement('div');
        modal.id = 'session-warning-modal';
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Session Timeout Warning
                        </h5>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="timeout-progress"></div>
                        </div>
                        <p class="text-muted small">Click "Stay Logged In" to continue your session, or you will be automatically logged out.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="logout-now">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout Now
                        </button>
                        <button type="button" class="btn btn-primary" id="stay-logged-in">
                            <i class="fas fa-clock me-1"></i> Stay Logged In
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Add event listeners
        document.getElementById('stay-logged-in').addEventListener('click', () => {
            this.stayLoggedIn();
        });

        document.getElementById('logout-now').addEventListener('click', () => {
            this.logoutNow();
        });

        // Start countdown
        this.startCountdown(remainingTime);
    }

    startCountdown(remainingTime) {
        const progressBar = document.getElementById('timeout-progress');
        const totalTime = remainingTime * 60; // Convert to seconds
        let timeLeft = totalTime;

        const countdown = setInterval(() => {
            timeLeft--;
            const percentage = ((totalTime - timeLeft) / totalTime) * 100;
            progressBar.style.width = percentage + '%';

            if (timeLeft <= 0) {
                clearInterval(countdown);
                this.handleTimeout();
            }
        }, 1000);
    }

    stayLoggedIn() {
        this.resetTimers();
        this.hideWarningModal();

        // Send keep-alive request to server
        this.sendKeepAlive();
    }

    logoutNow() {
        this.hideWarningModal();
        this.performLogout();
    }

    hideWarningModal() {
        const modal = document.getElementById('session-warning-modal');
        if (modal) {
            modal.remove();
        }
    }

    async sendKeepAlive() {
        try {
            const response = await fetch('/check-session', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error('Session check failed');
            }

            const data = await response.json();
            console.log('Session kept alive:', data);
        } catch (error) {
            console.error('Keep-alive failed:', error);
            this.handleTimeout();
        }
    }

    async checkSessionStatus() {
        try {
            const response = await fetch('/check-session', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error('Session expired');
            }

            const data = await response.json();
            if (!data.authenticated) {
                this.handleTimeout();
            }
        } catch (error) {
            console.error('Session check failed:', error);
            this.handleTimeout();
        }
    }

    handleTimeout() {
        if (this.isTimedOut) return;

        this.isTimedOut = true;
        this.hideWarningModal();

        // Show timeout message
        this.showTimeoutMessage();

        // Perform logout after a short delay
        setTimeout(() => {
            this.performLogout();
        }, 2000);
    }

    showTimeoutMessage() {
        const modal = document.createElement('div');
        modal.id = 'session-timeout-modal';
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-clock me-2"></i>
                            Session Expired
                        </h5>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-exclamation-circle text-danger mb-3" style="font-size: 3rem;"></i>
                        <p>${this.options.timeoutMessage}</p>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Redirecting...</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    }

    async performLogout() {
        try {
            const response = await fetch('/ajax-logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                // Redirect to login page
                window.location.href = data.redirect_url;
            } else {
                // Fallback redirect
                window.location.href = '/';
            }
        } catch (error) {
            console.error('Logout failed:', error);
            // Fallback redirect
            window.location.href = '/';
        }
    }

    cleanup() {
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
        }
        if (this.timeoutTimer) {
            clearTimeout(this.timeoutTimer);
        }
        if (this.checkTimer) {
            clearInterval(this.checkTimer);
        }
    }
}

// Initialize session timeout when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize for authenticated users
    if (document.body.classList.contains('authenticated') ||
        document.querySelector('meta[name="user-authenticated"]')?.content === 'true') {

        new SessionTimeout({
            warningTime: 5 * 60 * 1000, // 5 minutes
            timeoutTime: 10 * 60 * 1000, // 10 minutes
            checkInterval: 30 * 1000, // 30 seconds
        });
    }
});

// Export for manual initialization if needed
window.SessionTimeout = SessionTimeout;


