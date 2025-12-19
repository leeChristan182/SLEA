(() => {
  /**
   * Session Timeout Management
   * - warningTime: time AFTER idle starts when warning modal appears
   * - timeoutTime: TOTAL idle time before logout
   */

  const IS_AUTHENTICATED =
    document.querySelector('meta[name="user-authenticated"]')?.content === 'true' ||
    document.body?.classList?.contains('authenticated');

  const hasAccountDisabledModal = () => !!document.getElementById('accountDisabledModal');

  if (!IS_AUTHENTICATED) {
    [
      'slea_last_activity',
      'slea_idle_deadline',
      'slea_warning_shown',
      'slea_privacy_accepted',
      'slea_privacy_dismissed',
    ].forEach((k) => {
      try { localStorage.removeItem(k); } catch (_) {}
      try { sessionStorage.removeItem(k); } catch (_) {}
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
        // ✅ NEW: if true, any activity while warning is shown counts as “Stay”
        autoExtendOnActivityDuringWarning: true,
        ...options,
      };

      this.isWarningShown = false;
      this.isTimedOut = false;
      this.notified = false;

      this.warningTimer = null;
      this.timeoutTimer = null;
      this.checkTimer = null;
      this.countdownInterval = null;

      this.lastActivity = Date.now();

      this._lockedBodyScroll = false;
      this._createdAt = Date.now(); // for debugging if needed

      this.init();
    }

    init() {
      if (!IS_AUTHENTICATED) return;
      if (hasAccountDisabledModal()) return;

      this.bindEvents();
      this.startCheckTimer();
      this.resetTimers();
    }

    bindEvents() {
      const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

      events.forEach((event) => {
        document.addEventListener(event, (e) => this.handleActivity(e), true);
      });

      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) this.handleActivity();
      });

      window.addEventListener('beforeunload', () => this.cleanup());
    }

    // ✅ REAL remaining ms (based on last activity)
    getRemainingMs() {
      const elapsed = Date.now() - this.lastActivity;
      return Math.max(0, this.options.timeoutTime - elapsed);
    }

    handleActivity(e) {
      if (this.isTimedOut) return;

      // If warning modal is shown, activity should not be ignored.
      // ✅ Option A: Auto-extend on any activity (recommended)
      if (this.isWarningShown && this.options.autoExtendOnActivityDuringWarning) {
        // avoid accidental extend when user clicked "Log Out"
        const t = e?.target;
        if (t && (t.id === 'logout-now' || t.closest?.('#logout-now'))) return;

        this.stayLoggedIn(); // hides modal + resets + keepalive
        return;
      }

      // ✅ Option B: still update lastActivity so countdown remains accurate
      this.lastActivity = Date.now();

      // If warning is shown and autoExtend is off, keep timers running
      if (this.isWarningShown) return;

      this.resetTimers();
    }

    resetTimers() {
      if (this.isTimedOut) return;

      this.lastActivity = Date.now();
      this.isWarningShown = false;
      this.notified = false;

      if (this.warningTimer) clearTimeout(this.warningTimer);
      if (this.timeoutTimer) clearTimeout(this.timeoutTimer);

      this.startWarningTimer();
      this.startTimeoutTimer();
    }

    startWarningTimer() {
      this.warningTimer = setTimeout(() => this.showWarning(), this.options.warningTime);
    }

    startTimeoutTimer() {
      this.timeoutTimer = setTimeout(() => this.handleTimeout(), this.options.timeoutTime);
    }

    startCheckTimer() {
      if (this.checkTimer) clearInterval(this.checkTimer);
      this.checkTimer = setInterval(() => this.checkSessionStatus(), this.options.checkInterval);
    }

    showWarning() {
      if (this.isWarningShown || this.isTimedOut) return;
      if (hasAccountDisabledModal()) return;

      // ✅ If already almost timed out, just time out
      const remainingMs = this.getRemainingMs();
      if (remainingMs <= 500) return this.handleTimeout();

      this.isWarningShown = true;

      const remainingMin = Math.max(1, Math.ceil(remainingMs / 60000));
      const message = this.options.warningMessage.replace('{time}', remainingMin);

      if (
        document.hidden &&
        !this.notified &&
        'Notification' in window &&
        Notification.permission === 'granted'
      ) {
        this.notified = true;
        try {
          new Notification('SLEA Session Expiring Soon', {
            body: `Your SLEA session will expire in about ${remainingMin} minute(s) if you stay idle.`,
            icon: '/images/osas-logo.png',
          });
        } catch (_) {}
      }

      this.createWarningModal(message);
      this.startCountdown(); // ✅ now based on real remaining time
    }

    createWarningModal(message) {
      const existingModal = document.getElementById('session-warning-modal');
      if (existingModal) existingModal.remove();

      const modal = document.createElement('div');
      modal.id = 'session-warning-modal';
      modal.className = 'modal fade show session-timeout-modal';
      modal.style.display = 'flex';
      modal.style.alignItems = 'center';
      modal.style.justifyContent = 'center';
      modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
      modal.style.backdropFilter = 'blur(5px)';
      modal.style.webkitBackdropFilter = 'blur(5px)';
      modal.style.zIndex = '20000'; // ✅ keep above bootstrap modals
      modal.style.position = 'fixed';
      modal.style.top = '0';
      modal.style.left = '0';
      modal.style.width = '100%';
      modal.style.height = '100%';

      modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered session-timeout-dialog">
          <div class="modal-content session-timeout-content">
            <div class="modal-header bg-warning text-dark session-timeout-header">
              <h5 class="modal-title mb-0 session-timeout-title">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Session Timeout Warning
              </h5>
            </div>
            <div class="modal-body session-timeout-body">
              <p class="session-timeout-message">${message}</p>

              <div class="progress session-timeout-progress">
                <div class="progress-bar bg-warning" role="progressbar"
                     style="width:0%" id="timeout-progress"></div>
              </div>

              <p class="text-muted small session-timeout-hint mb-0">
                Click <strong>"Stay"</strong> to continue your session,
                or you will be automatically logged out.
                <br>
                <span id="timeout-countdown" class="fw-semibold"></span>
              </p>
            </div>
            <div class="modal-footer session-timeout-footer">
              <button type="button" class="btn btn-secondary session-timeout-btn" id="logout-now">
                <i class="fas fa-sign-out-alt"></i>
                <span>Log Out</span>
              </button>
              <button type="button" class="btn btn-success session-timeout-btn" id="stay-logged-in">
                <i class="fas fa-clock"></i>
                <span>Stay</span>
              </button>
            </div>
          </div>
        </div>
      `;

      document.body.appendChild(modal);

      // ✅ lock scroll only if we did it
      if (document.body.style.overflow !== 'hidden') {
        document.body.style.overflow = 'hidden';
        this._lockedBodyScroll = true;
      }

      document.getElementById('stay-logged-in')?.addEventListener('click', () => this.stayLoggedIn());
      document.getElementById('logout-now')?.addEventListener('click', () => this.logoutNow());
    }

    // ✅ Countdown/progress tied to true remaining time
    startCountdown() {
      const progressBar = document.getElementById('timeout-progress');
      const countdownEl = document.getElementById('timeout-countdown');
      if (!progressBar) return;

      const totalWindow = Math.max(1, this.options.timeoutTime - this.options.warningTime);

      this.clearCountdown();

      this.countdownInterval = setInterval(() => {
        if (this.isTimedOut) {
          this.clearCountdown();
          return;
        }

        const remainingMs = this.getRemainingMs();
        const remainingSec = Math.ceil(remainingMs / 1000);

        // percent of the warning window elapsed
        const elapsedSinceWarning = Math.max(0, totalWindow - remainingMs);
        const percentage = Math.min(100, Math.max(0, (elapsedSinceWarning / totalWindow) * 100));
        progressBar.style.width = percentage + '%';

        if (countdownEl) {
          const m = Math.floor(remainingSec / 60);
          const s = remainingSec % 60;
          countdownEl.textContent = `Time left: ${m}:${String(s).padStart(2, '0')}`;
        }

        if (remainingMs <= 0) {
          this.clearCountdown();
          this.handleTimeout();
        }
      }, 250); // ✅ smoother + more accurate than 1s
    }

    clearCountdown() {
      if (this.countdownInterval) {
        clearInterval(this.countdownInterval);
        this.countdownInterval = null;
      }
    }

    stayLoggedIn() {
      if (this.isTimedOut) return;
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

      if (this._lockedBodyScroll) {
        document.body.style.overflow = '';
        this._lockedBodyScroll = false;
      }
    }

    async sendKeepAlive() {
      try {
        const response = await fetch('/keep-alive', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        });

        if (response.status === 401) return this.handleTimeout();
        if (!response.ok) throw new Error('Keep-alive failed');

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) return;

        const data = await response.json().catch(() => null);

        if (data?.csrf_token) {
          document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', data.csrf_token);
        }

        if (data && data.authenticated === false) this.handleTimeout();
      } catch (_) {
        // ok: periodic check-session will handle it
      }
    }

    async checkSessionStatus() {
      if (this.isTimedOut) return;

      try {
        const response = await fetch('/check-session', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        });

        if (response.status === 401) return this.handleTimeout();
        if (!response.ok) return;

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) return;

        const data = await response.json().catch(() => null);
        if (data && data.authenticated === false) this.handleTimeout();
      } catch (_) {}
    }

    handleTimeout() {
      if (this.isTimedOut) return;

      this.isTimedOut = true;

      if (this.warningTimer) clearTimeout(this.warningTimer);
      if (this.timeoutTimer) clearTimeout(this.timeoutTimer);
      if (this.checkTimer) clearInterval(this.checkTimer);
      this.clearCountdown();

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
      modal.style.zIndex = '20001';

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

      if (document.body.style.overflow !== 'hidden') {
        document.body.style.overflow = 'hidden';
        this._lockedBodyScroll = true;
      }
    }

    async performLogout() {
      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const response = await fetch('/logout', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
          },
        });

        let data = null;
        try { data = await response.json(); } catch (_) {}

        if (this._lockedBodyScroll) {
          document.body.style.overflow = '';
          this._lockedBodyScroll = false;
        }

        window.location.href = data?.success && data?.redirect_url ? data.redirect_url : '/login';
      } catch (_) {
        if (this._lockedBodyScroll) {
          document.body.style.overflow = '';
          this._lockedBodyScroll = false;
        }
        window.location.href = '/login';
      }
    }

    cleanup() {
      if (this.warningTimer) clearTimeout(this.warningTimer);
      if (this.timeoutTimer) clearTimeout(this.timeoutTimer);
      if (this.checkTimer) clearInterval(this.checkTimer);
      this.clearCountdown();

      if (this._lockedBodyScroll) {
        document.body.style.overflow = '';
        this._lockedBodyScroll = false;
      }
    }
  }

  window.SessionTimeout = SessionTimeout;

  // ✅ Prevent duplicate instances: clean up any old one before creating a new one
  window.__sessionTimeoutCleanup = window.__sessionTimeoutCleanup || (() => {
    try { window.__sessionTimeout?.cleanup?.(); } catch (_) {}
    window.__sessionTimeout = null;
  });

  // OPTIONAL: if you initialize here (instead of in blade), do:
  // window.__sessionTimeoutCleanup();
  // window.__sessionTimeout = new SessionTimeout({ ... });
})();
