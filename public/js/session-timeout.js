(() => {
  /**
   * Session Timeout Management
   * - warningTime: time AFTER idle starts when warning modal appears
   * - timeoutTime: TOTAL idle time before logout
   *
   * Requires in layout:
   *  <meta name="user-authenticated" content="true|false">
   *  <meta name="csrf-token" content="...">
   *
   * Layout should instantiate:
   *  window.__sessionTimeout = new SessionTimeout({ warningTime, timeoutTime, checkInterval });
   */

  function isUserAuthenticated() {
    return (
      document.querySelector('meta[name="user-authenticated"]')?.content === 'true' ||
      document.body?.classList?.contains('authenticated')
    );
  }

  function hasAccountDisabledModal() {
    return !!document.getElementById('accountDisabledModal');
  }

  // If not authenticated, clear ONLY app keys (don’t nuke all storage)
  if (!isUserAuthenticated()) {
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

      // Track whether *we* locked scroll, so we don’t undo someone else’s lock
      this._lockedBodyScroll = false;

      this.init();
    }

    init() {
      // Don’t run if not authenticated or if account disabled modal is active
      if (!isUserAuthenticated()) return;
      if (hasAccountDisabledModal()) return;

      this.bindEvents();
      this.startCheckTimer();
      this.resetTimers();
    }

    bindEvents() {
      const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

      events.forEach((event) => {
        document.addEventListener(event, () => this.handleActivity(), true);
      });

      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) this.handleActivity();
      });

      window.addEventListener('beforeunload', () => this.cleanup());
    }

    handleActivity() {
      if (this.isTimedOut) return;
      if (this.isWarningShown) return; // only reset when user clicks "Stay"
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
      this.checkTimer = setInterval(() => this.checkSessionStatus(), this.options.checkInterval);
    }

    showWarning() {
      if (this.isWarningShown || this.isTimedOut) return;
      if (hasAccountDisabledModal()) return;

      this.isWarningShown = true;

      const remainingMs = Math.max(0, this.options.timeoutTime - this.options.warningTime);
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
        } catch (e) {
          console.warn('Notification failed:', e);
        }
      }

      this.createWarningModal(message, remainingMin);
    }

    createWarningModal(message, remainingMinutes) {
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
      modal.style.zIndex = '1055';
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
              <p class="text-muted small session-timeout-hint">
                Click <strong>"Stay"</strong> to continue your session,
                or you will be automatically logged out.
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

      if (document.body.style.overflow !== 'hidden') {
        document.body.style.overflow = 'hidden';
        this._lockedBodyScroll = true;
      }

      document.getElementById('stay-logged-in')?.addEventListener('click', () => this.stayLoggedIn());
      document.getElementById('logout-now')?.addEventListener('click', () => this.logoutNow());

      this.startCountdown(remainingMinutes);
    }

    startCountdown(remainingMinutes) {
      const progressBar = document.getElementById('timeout-progress');
      if (!progressBar) return;

      const totalTimeSec = Math.max(1, remainingMinutes * 60);
      let timeLeft = totalTimeSec;

      this.clearCountdown();

      this.countdownInterval = setInterval(() => {
        if (this.isTimedOut) {
          this.clearCountdown();
          return;
        }

        timeLeft = Math.max(0, timeLeft - 1);

        const percentage = Math.min(
          100,
          Math.max(0, ((totalTimeSec - timeLeft) / totalTimeSec) * 100)
        );
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
        if (!contentType.includes('application/json')) throw new Error('Keep-alive got non-JSON');

        const data = await response.json().catch(() => null);

        if (data?.csrf_token) {
          document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', data.csrf_token);
        }

        if (data && data.authenticated === false) this.handleTimeout();
      } catch (err) {
        console.warn('Keep-alive failed, will retry via periodic check:', err);
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
      } catch (error) {
        console.warn('Session check failed, will retry:', error);
      }
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
        } else {
          document.body.style.overflow = '';
        }

        window.location.href = data?.success && data?.redirect_url ? data.redirect_url : '/login';
      } catch (error) {
        console.error('Logout failed:', error);

        if (this._lockedBodyScroll) {
          document.body.style.overflow = '';
          this._lockedBodyScroll = false;
        } else {
          document.body.style.overflow = '';
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

  // Expose class globally (needed by app.blade initializer)
  window.SessionTimeout = SessionTimeout;
})();
