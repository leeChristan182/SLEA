/**
 * Session Timeout Management
 * - warningTime: time AFTER idle starts when warning modal appears
 * - timeoutTime: TOTAL idle time before logout
 */
// Put this early in DOMContentLoaded
const isAuthenticated =
  document.querySelector('meta[name="auth"]')?.content === '1'
  || document.body?.dataset?.auth === '1'; // pick one convention

if (!isAuthenticated) {
  // Clear ONLY your app keys (don’t nuke all localStorage)
  [
    'slea_last_activity',
    'slea_idle_deadline',
    'slea_warning_shown',
    'slea_privacy_accepted',
    'slea_privacy_dismissed',
  ].forEach(k => {
    localStorage.removeItem(k);
    sessionStorage.removeItem(k);
  });
}

class SessionTimeout {
    constructor(options = {}) {
        this.options = {
            warningTime: 5 * 60 * 1000,
            timeoutTime: 10 * 60 * 1000,
            checkInterval: 30 * 1000,

            warningMessage:
                'Your session will expire in {time} minutes due to inactivity. Do you want to stay logged in?',
            timeoutMessage:
                'Your session has expired due to inactivity. You will be redirected to the login page.',
            ...options,
        };

        this.isWarningShown = false;
        this.isTimedOut = false;

        this.warningTimer = null;
        this.timeoutTimer = null;
        this.checkTimer = null;
        this.countdownInterval = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.startCheckTimer();

        // ✅ start BOTH timers aligned to same baseline
        this.resetTimers();
    }

    bindEvents() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

        events.forEach((event) => {
            document.addEventListener(
                event,
                () => {
                    this.handleActivity();
                },
                true
            );
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) this.handleActivity();
        });

        window.addEventListener('beforeunload', () => this.cleanup());
    }

    handleActivity() {
        if (this.isTimedOut) return;

        // If warning already shown, don't reset timers unless user clicks "Stay Logged In"
        if (this.isWarningShown) return;

        this.resetTimers();
    }

resetTimers() {
  if (this.isTimedOut) return;

  this.lastActivity = Date.now();
  this.isWarningShown = false;

  if (this.warningTimer) clearTimeout(this.warningTimer);
  if (this.timeoutTimer) clearTimeout(this.timeoutTimer);

  this.startWarningTimer();
  this.startTimeoutTimer(); // ✅ ADD THIS
}

    startWarningTimer() {
        this.warningTimer = setTimeout(() => this.showWarning(), this.options.warningTime);
    }

    startTimeoutTimer() {
        this.timeoutTimer = setTimeout(() => this.handleTimeout(), this.options.timeoutTime);
    }

    startCheckTimer() {
        this.checkTimer = setInterval(() => this.checkSessionStatus(), this.options.checkInterval);
    }

    showWarning() {
        if (this.isWarningShown || this.isTimedOut) return;
        this.isWarningShown = true;

        const remainingMs = Math.max(0, this.options.timeoutTime - this.options.warningTime);
        const remainingMin = Math.max(1, Math.ceil(remainingMs / 60000));
        const message = this.options.warningMessage.replace('{time}', remainingMin);

        // If tab hidden & notifications granted
        if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
            try {
                new Notification('SLEA Session Expiring Soon', {
                    body: `Your SLEA session will expire in about ${remainingMin} minute(s) if you stay idle.`,
                    icon: '/images/osas-logo.png',
                });
            } catch (e) {
                console.warn('Notification failed:', e);
            }
        }

        this.createWarningModal(message, remainingMin);
        // ✅ DO NOT start timeout timer here; it is already running
    }

    createWarningModal(message, remainingMinutes) {
        const existingModal = document.getElementById('session-warning-modal');
        if (existingModal) existingModal.remove();

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
                                 style="width:0%" id="timeout-progress"></div>
                        </div>
                        <p class="text-muted small mb-0">
                            Click <strong>"Stay Logged In"</strong> to continue your session,
                            or you will be automatically logged out.
                        </p>
                    </div>
                    <div class="modal-footer d-flex flex-column flex-sm-row gap-2">
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

        document.getElementById('stay-logged-in')?.addEventListener('click', () => this.stayLoggedIn());
        document.getElementById('logout-now')?.addEventListener('click', () => this.logoutNow());

        this.startCountdown(remainingMinutes);
    }

    startCountdown(remainingMinutes) {
        const progressBar = document.getElementById('timeout-progress');
        if (!progressBar) return;

        const totalTimeSec = remainingMinutes * 60;
        let timeLeft = totalTimeSec;

        this.clearCountdown();

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
        this.hideWarningModal();
        this.resetTimers();
        this.sendKeepAlive();
    }

    logoutNow() {
        this.hideWarningModal();
        this.performLogout();
    }

    hideWarningModal() {
        const modal = document.getElementById('session-warning-modal');
        if (modal) modal.remove();
        this.clearCountdown();
        this.isWarningShown = false;
    }

    async sendKeepAlive() {
        try {
            const response = await fetch('/check-session', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Keep-alive failed (non-OK)');

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) throw new Error('Keep-alive got non-JSON');

            const data = await response.json();
            if (!data.authenticated) this.handleTimeout();
        } catch (err) {
            console.error('Keep-alive failed:', err);
            this.handleTimeout();
        }
    }

    async checkSessionStatus() {
        try {
            const response = await fetch('/check-session', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            // If session is gone and server returns 401, timeout
            if (response.status === 401) return this.handleTimeout();

            if (!response.ok) throw new Error('Session check non-OK');

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                // got HTML redirect or something unexpected
                return this.handleTimeout();
            }

            const data = await response.json();
            if (!data.authenticated) this.handleTimeout();
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

        setTimeout(() => this.performLogout(), 1500);
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
                        <i class="fas fa-exclamation-circle text-danger mb-3" style="font-size:3rem;"></i>
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
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const response = await fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            let data = {};
            try { data = await response.json(); } catch (e) {}

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
        if (this.warningTimer) clearTimeout(this.warningTimer);
        if (this.timeoutTimer) clearTimeout(this.timeoutTimer);
        if (this.checkTimer) clearInterval(this.checkTimer);
        this.clearCountdown();
    }
}

window.SessionTimeout = SessionTimeout;
