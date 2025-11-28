/**
 * Session Timeout Management
 * Handles idle timeout detection and user notifications
 */
class SessionTimeout {
    constructor(options = {}) {
        this.options = {
            // Default values (you can override on init)
            warningTime: 5 * 60 * 1000,   // 5 minutes
            timeoutTime: 10 * 60 * 1000,  // 10 minutes
            checkInterval: 30 * 1000,     // 30 seconds

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
        this.countdownInterval = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.startCheckTimer();
        this.startWarningTimer();
    }

    bindEvents() {
        // Track user activity BEFORE the warning modal is shown
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

        events.forEach(event => {
            document.addEventListener(event, (e) => {
                this.handleActivity(e);
            }, true);
        });

        // Handle visibility change (tab switching)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.handleActivity();
            }
        });

        // Handle page unload
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });
    }

    /**
     * Called on any user interaction.
     * BEFORE warning: resets timers.
     * AFTER warning: does NOT reset timers (user must click "Stay Logged In").
     */
    handleActivity() {
        if (this.isTimedOut) return;

        // If warning popup is already shown, ignore passive activity.
        // Only clicking "Stay Logged In" should extend the session.
        if (this.isWarningShown) {
            // We can optionally update lastActivity, but DO NOT reset timers.
            this.lastActivity = Date.now();
            return;
        }

        // Normal case: no warning yet → reset timers
        this.resetTimers();
    }

    resetTimers() {
        if (this.isTimedOut) return;

        this.lastActivity = Date.now();
        this.isWarningShown = false;

        // Clear existing timers
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
            this.warningTimer = null;
        }
        if (this.timeoutTimer) {
            clearTimeout(this.timeoutTimer);
            this.timeoutTimer = null;
        }

        // Also clear any countdown in case it was running
        this.clearCountdown();

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

    const remainingMs  = this.options.timeoutTime - this.options.warningTime;
    const remainingMin = Math.max(1, Math.ceil(remainingMs / 60000)); // avoid 0

    const message = this.options.warningMessage.replace('{time}', remainingMin);

    // 🔔 If tab is not visible and notifications are allowed, show native browser notification
    if (
        document.hidden &&
        'Notification' in window &&
        Notification.permission === 'granted'
    ) {
        try {
            new Notification('SLEA Session Expiring Soon', {
                body: `Your SLEA session will expire in about ${remainingMin} minute(s) if you stay idle.`,
                icon: '/images/osas-logo.png', // optional, adjust path
            });
        } catch (e) {
            console.warn('Notification failed:', e);
        }
    }

    // Existing behavior: show your Bootstrap-style in-page warning modal
    this.createWarningModal(message, remainingMin);
    this.startTimeoutTimer();
}


    createWarningModal(message, remainingMinutes) {
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
        modal.style.zIndex = '1055';

        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Session Timeout Warning
                        </h5>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: 0%" id="timeout-progress"></div>
                        </div>
                        <p class="text-muted small mb-0">
                            Click <strong>"Stay Logged In"</strong> to continue your session,
                            or you will be automatically logged out.
                        </p>
                    </div>
                    <div class="modal-footer d-flex flex-column flex-sm-row gap-2">
                        <button type="button"
                                class="btn btn-secondary flex-fill flex-sm-grow-0"
                                id="logout-now"
                                style="min-width: 150px; white-space: nowrap;">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout Now
                        </button>
                        <button type="button"
                                class="btn btn-primary flex-fill flex-sm-grow-0"
                                id="stay-logged-in"
                                style="min-width: 170px; white-space: nowrap;">
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
        this.startCountdown(remainingMinutes);
    }

    startCountdown(remainingMinutes) {
        const progressBar = document.getElementById('timeout-progress');
        if (!progressBar) return;

        const totalTimeSec = remainingMinutes * 60;
        let timeLeft = totalTimeSec;

        this.clearCountdown(); // ensure any previous interval is cleared

        this.countdownInterval = setInterval(() => {
            timeLeft--;

            const percentage = ((totalTimeSec - timeLeft) / totalTimeSec) * 100;
            progressBar.style.width = percentage + '%';

            if (timeLeft <= 0) {
                this.clearCountdown();
                this.handleTimeout();
            }
        }, 1000);
    }

    clearCountdown() {
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
    }

    stayLoggedIn() {
        // User explicitly chose to extend session
        this.hideWarningModal();
        this.resetTimers();   // will clear countdown + restart timers
        this.sendKeepAlive(); // tell server we're still here
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
        this.clearCountdown();
        this.isWarningShown = false;
    }

    async sendKeepAlive() {
        try {
            const response = await fetch('/check-session', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
        this.showTimeoutMessage();

        // Perform logout after a short delay
        setTimeout(() => {
            this.performLogout();
        }, 2000);
    }

    showTimeoutMessage() {
        const existing = document.getElementById('session-timeout-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'session-timeout-modal';
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.style.zIndex = '1056';

        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title mb-0">
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
            const response = await fetch('/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({})
            });

            let data = {};
            try {
                data = await response.json();
            } catch (e) {
                // Non-JSON response is fine
            }

            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.href = '/login';
            }
        } catch (error) {
            console.error('Logout failed:', error);
            window.location.href = '/login';
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
        this.clearCountdown();
    }
}

// Export for manual initialization if needed
window.SessionTimeout = SessionTimeout;
