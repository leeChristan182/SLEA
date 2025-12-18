/* profile.js
   Shared logic for Admin & Assessor Profile pages (T-layout).
   Requires bootstrap 5 for modal/toast markup (optional fallback used).

   Auto-detects context:
   - Admin page    => input[name="admin_id"]
   - Assessor page => input[name="assessor_id"]
*/

document.addEventListener('DOMContentLoaded', () => {
  // Only run on profile page: check for main profile element
  if (!document.getElementById('profilePicture') && !document.querySelector('.profile-banner')) return;

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// Detect which profile we're on (ADMIN or ASSESSOR only)
const adminIdInput    = document.querySelector('input[name="admin_id"]');
const assessorIdInput = document.querySelector('input[name="assessor_id"]');

const isAdmin    = !!adminIdInput;
const isAssessor = !!assessorIdInput;

// If neither, don't run unified profile logic (student uses a different JS)
if (!isAdmin && !isAssessor) {
  console.warn('profile.js: no admin_id or assessor_id found; skipping unified profile script.');
  return;
}

// Per-user avatar cache key (only admin/assessor)
const currentUserId    = adminIdInput?.value || assessorIdInput?.value || null;
const avatarStorageKey = currentUserId ? `profileImage:${currentUserId}` : null;

// Role prefix used for toast CSS classnames etc.
const rolePrefix = isAdmin ? 'admin' : 'assessor';

// Which field should stay read-only in edit mode
const primaryIdFieldName = isAdmin ? 'admin_id' : 'assessor_id';

  /* ---------- Utilities: toasts & modal helpers ---------- */
  function showToast(message, isError = false, timeout = 3500) {
    // simple floating toast (bootstrap optional)
    const toast = document.createElement('div');
    toast.className = `${rolePrefix}-toast ${isError ? `${rolePrefix}-toast-error` : `${rolePrefix}-toast-success`}`;
    toast.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:99999;padding:.6rem 1rem;border-radius:.4rem;box-shadow:0 6px 18px rgba(0,0,0,.12);font-weight:600;';
    toast.style.background = isError ? '#dc3545' : '#198754';
    toast.style.color = 'white';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.transition = 'opacity .3s'; toast.style.opacity = '0'; }, timeout);
    setTimeout(() => toast.remove(), timeout + 400);
  }

  function confirmModal(htmlBody) {
    // returns a promise that resolves true/false based on user confirm/cancel
    return new Promise(resolve => {
      const wrapper = document.createElement('div');
      wrapper.innerHTML = `
      <div class="${rolePrefix}-confirm-modal-backdrop" style="position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;">
        <div class="${rolePrefix}-confirm-modal" style="background:#fff;border-radius:8px;max-width:420px;width:92%;padding:1rem;box-shadow:0 8px 30px rgba(0,0,0,.2);">
          <div class="${rolePrefix}-confirm-body">${htmlBody}</div>
          <div style="display:flex;gap:.6rem;justify-content:center;margin-top:.8rem;">
            <button class="${rolePrefix}-confirm-cancel" style="padding:.5rem .9rem;border-radius:6px;border:1px solid #dcdcdc;background:#fff;">Cancel</button>
            <button class="${rolePrefix}-confirm-ok" style="padding:.5rem .9rem;border-radius:6px;border:0;background:#0d6efd;color:#fff;">Confirm</button>
          </div>
        </div>
      </div>`;
      document.body.appendChild(wrapper);
      const cancelBtn = wrapper.querySelector(`.${rolePrefix}-confirm-cancel`);
      const okBtn = wrapper.querySelector(`.${rolePrefix}-confirm-ok`);

      function cleanup(val) {
        wrapper.remove();
        resolve(val);
      }
      cancelBtn.addEventListener('click', () => cleanup(false));
      okBtn.addEventListener('click', () => cleanup(true));
    });
  }

  /* ---------- ELEMENTS ---------- */
  const displayMode = document.getElementById('displayMode');
  const editMode = document.getElementById('editMode');
  const editPersonalBtn = document.getElementById('editPersonalBtn');
  const updateForm = document.getElementById('updateForm'); // form in editMode
  const saveButtonsContainer = updateForm ? updateForm.querySelector('.form-actions') : null;

  const passwordDisplayMode = document.getElementById('passwordDisplayMode');
  const passwordEditMode = document.getElementById('passwordEditMode');
  const editPasswordBtn = document.getElementById('editPasswordBtn');
  const passwordForm = document.getElementById('passwordForm');

  const avatarInput = document.getElementById('avatarUpload');
  const profilePicture = document.getElementById('profilePicture');
  const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');

  /* ---------- Avatar upload button trigger ---------- */
  if (uploadPhotoBtn && avatarInput) {
    uploadPhotoBtn.addEventListener('click', (e) => {
      e.preventDefault();
      console.log("🟢 Upload Photo button clicked");
      avatarInput.click(); // opens file dialog
    });
  }

  /* ---------- Profile edit toggles ---------- */
  if (editMode) editMode.style.display = 'none';
  if (saveButtonsContainer) saveButtonsContainer.style.display = 'none';

  function enableEditing() {
    if (!editMode || !displayMode || !updateForm) return;
    displayMode.style.display = 'none';
    editMode.style.display = 'block';
    if (editPersonalBtn) editPersonalBtn.style.display = 'none';
    if (saveButtonsContainer) saveButtonsContainer.style.display = 'flex';

    // Make inputs editable except readonly ones (admin_id / assessor_id / explicitly locked)
    updateForm.querySelectorAll('input').forEach(i => {
      if (i.hasAttribute('data-noreadonly')) return;
      if (primaryIdFieldName && i.getAttribute('name') === primaryIdFieldName) return;
      if (i.readOnly) i.readOnly = false;
    });
  }

  function cancelEditing() {
    if (!editMode || !displayMode || !updateForm) return;
    window.location.reload();
  }

  if (editPersonalBtn) {
    editPersonalBtn.addEventListener('click', (e) => {
      e.preventDefault();
      enableEditing();
    });
  }

  /* ---------- Profile update (AJAX) ---------- */
  if (updateForm) {
    updateForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const action = updateForm.action || updateForm.getAttribute('data-action') || null;
      if (!action) {
        console.warn('Profile update: form action not set. Aborting AJAX update.');
        showToast('Profile update failed: missing form action', true);
        return;
      }

      const fd = new FormData(updateForm);
      if (!fd.has('_method')) fd.append('_method', 'PUT');

      try {
        const res = await fetch(action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: fd
        });

        // Check if response is JSON
        const contentType = res.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
          data = await res.json();
        } else {
          // If not JSON, it's likely a redirect (successful save)
          // Check if it's a redirect (status 302 or 200 with HTML)
          if (res.ok || res.redirected) {
            showToast('Profile updated successfully', false);
            setTimeout(() => window.location.reload(), 900);
            return;
          } else {
            // Read as text to see what the error is
            const text = await res.text();
            console.error('Profile update non-JSON response', { status: res.status, text });
            showToast('Profile update failed', true);
            return;
          }
        }

        if (res.ok && data.success) {
          showToast(data.message || 'Profile updated successfully', false);
          setTimeout(() => window.location.reload(), 900);
        } else {
          const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Update failed');
          showToast(msg, true);
        }
      } catch (err) {
        console.error('Profile update error', err);
        // Check if it's a JSON parse error (which might mean the save actually succeeded)
        if (err instanceof SyntaxError && err.message.includes('JSON')) {
          // Likely a redirect response, which means the save succeeded
          showToast('Profile updated successfully', false);
          setTimeout(() => window.location.reload(), 900);
        } else {
          showToast('Error saving profile', true);
        }
      }
    });

    const saveBtn = updateForm.querySelector('button[type="submit"], .btn-save');
    if (saveBtn) saveBtn.addEventListener('click', (ev) => {
      if (saveBtn.getAttribute('type') !== 'submit') {
        updateForm.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });
  }

  /* ---------- Password edit toggles ---------- */
  if (passwordEditMode) passwordEditMode.style.display = 'none';

  if (editPasswordBtn) {
    editPasswordBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (passwordDisplayMode) passwordDisplayMode.style.display = 'none';
      if (passwordEditMode) passwordEditMode.style.display = 'block';
      editPasswordBtn.style.display = 'none';
    });
  }

  function cancelPasswordEdit() {
    if (passwordDisplayMode) passwordDisplayMode.style.display = 'block';
    if (passwordEditMode) passwordEditMode.style.display = 'none';
    if (editPasswordBtn) editPasswordBtn.style.display = 'flex';
    if (passwordForm) passwordForm.reset();
    ['length','uppercase','lowercase','number','special'].forEach(k => {
      const el = document.getElementById(k);
      if (el) {
        el.classList.remove('valid');
        el.classList.add('invalid');
      }
    });
  }

  // expose global cancel for inline Blade
  window.cancelPasswordEdit = cancelPasswordEdit;

  document.getElementById('cancelPersonalBtn')?.addEventListener('click', cancelEditing);
  document.getElementById('cancelPasswordBtn')?.addEventListener('click', cancelPasswordEdit);

  /* ---------- Password validation checklist ---------- */
  function setChecklistState(key, ok) {
    const el = document.getElementById(key);
    if (!el) return;
    el.classList.toggle('valid', ok);
    el.classList.toggle('invalid', !ok);

    const checkIcon = el.querySelector('.check-icon');
    const circleIcon = el.querySelector('.circle-icon');
    if (checkIcon && circleIcon) {
      checkIcon.classList.toggle('d-none', !ok);
      circleIcon.classList.toggle('d-none', ok);
    } else {
      const checkClass = `${rolePrefix}-check-mark`;
      const circleClass = `${rolePrefix}-circle-mark`;

      if (ok) {
        if (!el.querySelector(`.${checkClass}`)) {
          el.insertAdjacentHTML('afterbegin',
            `<span class="${checkClass}" style="display:inline-block;width:18px;margin-right:.45rem;color:#28a745;">✓</span>`
          );
        }
        const circ = el.querySelector(`.${circleClass}`);
        if (circ) circ.remove();
      } else {
        const chk = el.querySelector(`.${checkClass}`);
        if (chk) chk.remove();
        if (!el.querySelector(`.${circleClass}`)) {
          el.insertAdjacentHTML('afterbegin',
            `<span class="${circleClass}" style="display:inline-block;width:18px;margin-right:.45rem;color:#9aa0a6;">○</span>`
          );
        }
      }
    }
  }

  window.validatePassword = function () {
    const val = (document.getElementById('newPassword')?.value || '');
    const rules = {
      length: val.length >= 8,
      uppercase: /[A-Z]/.test(val),
      lowercase: /[a-z]/.test(val),
      number: /\d/.test(val),
      special: /[!@#$%^&*(),.?":{}|<>]/.test(val)
    };
    Object.entries(rules).forEach(([k, v]) => setChecklistState(k, v));
  };

  window.togglePassword = function () {
    ['currentPassword', 'newPassword', 'confirmPassword'].forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      el.type = (el.type === 'password') ? 'text' : 'password';
    });
  };

  /* ---------- Password update (AJAX) ---------- */
  if (passwordForm) {
    passwordForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const action = passwordForm.action || passwordForm.getAttribute('data-action') || null;
      if (!action) {
        console.warn('Password update: form action not set. Aborting AJAX update.');
        showToast('Password update failed: missing form action', true);
        return;
      }

      const newPassword = document.getElementById('newPassword')?.value || '';
      const confirmPassword = document.getElementById('confirmPassword')?.value || '';
      if (newPassword !== confirmPassword) {
        showToast('Passwords do not match', true);
        return;
      }

      const passed = ['length','uppercase','lowercase','number','special'].every(k => {
        const el = document.getElementById(k);
        return el && el.classList.contains('valid');
      });
      if (!passed) {
        showToast('Password does not meet requirements', true);
        return;
      }

      const fd = new FormData(passwordForm);
      if (!fd.has('_method')) fd.append('_method', 'PUT');

      try {
        const res = await fetch(action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: fd
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast(data.message || 'Password changed');
          cancelPasswordEdit();
          passwordForm.reset();
        } else {
          const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Password update failed');
          showToast(msg, true);
        }
      } catch (err) {
        console.error('Password update error', err);
        showToast('Error updating password', true);
      }
    });

    const passwordBtn = passwordForm.querySelector('button[type="submit"], .btn-save');
    if (passwordBtn) passwordBtn.addEventListener('click', (ev) => {
      if (passwordBtn.getAttribute('type') !== 'submit') {
        passwordForm.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });
  }

  /* ---------- Avatar preview + confirmation + upload ---------- */
  async function handleAvatarFile(file) {
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showToast('Please pick an image file', true); return;
    }
    // match PHP 5MB limit (5120 KB)
    if (file.size > 5 * 1024 * 1024) {
      showToast('Image must be under 5MB', true); return;
    }

    const reader = new FileReader();
    reader.onload = async (ev) => {
      const previewSrc = ev.target.result;
      const html = `
        <div style="text-align:center;">
          <img src="${previewSrc}" style="width:120px;height:120px;border-radius:999px;object-fit:cover;display:block;margin:0 auto 0.6rem;">
          <div>Save this as your new profile picture?</div>
        </div>`;
      const ok = await confirmModal(html);
      if (!ok) return;

      const avatarFormEl = document.getElementById('avatarForm');
      let action =
        (avatarFormEl && avatarFormEl.action)
        || document.getElementById('avatarUpload')?.getAttribute('data-action')
        || (document.getElementById('avatarUpload')?.form?.action || null);

      if (!action) {
        console.warn('Avatar upload: no action found on form/input. Aborting upload.');
        showToast('Avatar upload failed: missing endpoint', true);
        return;
      }

      const uploadFD = new FormData();
      uploadFD.append('avatar', file);

      try {
        const res = await fetch(action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: uploadFD
        });

        // Read response as text first, then try to parse as JSON
        const raw = await res.text();
        let data;
        try {
          data = raw ? JSON.parse(raw) : {};
        } catch (e) {
          console.error('Avatar upload non-JSON response', { status: res.status, raw });
          showToast('Avatar upload failed (non-JSON response)', true);
          return;
        }

        if (res.ok && data.success) {
          if (profilePicture && data.avatar_url) {
            // Add cache-busting parameter if not already present
            let avatarUrl = data.avatar_url;
            if (!avatarUrl.includes('?v=')) {
              avatarUrl += (avatarUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
            }
            profilePicture.src = avatarUrl;
            
            // Force image reload
            profilePicture.onload = function() {
              console.log('✅ Avatar image loaded successfully');
            };
            profilePicture.onerror = function() {
              console.error('❌ Failed to load avatar image');
              // Fallback: reload page after a short delay
              setTimeout(() => window.location.reload(), 1000);
            };
            
            if (avatarStorageKey) {
              try {
                localStorage.setItem(avatarStorageKey, avatarUrl);
              } catch (e) { /* ignore */ }
            }
          }
          showToast(data.message || 'Avatar updated');
        } else {
          const msg =
            data.message
            || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Upload failed');
          showToast(msg, true);
          console.error('Avatar upload error response', { status: res.status, data });
        }
      } catch (err) {
        console.error('Avatar upload network/error', err);
        showToast('Avatar upload error', true);
      }
    };
    reader.readAsDataURL(file);
  }

  if (avatarInput) {
    avatarInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      handleAvatarFile(file);
    });
    window.previewAvatar = (ev) => {
      const file = ev.target.files[0];
      handleAvatarFile(file);
    };
  }

  /* ---------- Load stored avatar (client side fallback, per user) ---------- */
  try {
    if (avatarStorageKey) {
      const saved = localStorage.getItem(avatarStorageKey);
      if (saved && profilePicture) profilePicture.src = saved;
    }
  } catch (err) { /* ignore */ }

  /* ---------- keyboard accessibility: allow Enter/Space on edit buttons ---------- */
  [editPersonalBtn, editPasswordBtn].forEach(btn => {
    if (!btn) return;
    btn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
    });
  });
}); // DOMContentLoaded
