/* admin_profile.js
   Scoped to Admin Profile page (T-layout).
   Requires bootstrap 5 for modal/toast markup (optional fallback used).
*/

document.addEventListener('DOMContentLoaded', () => {
  // Only run on profile page: check for main profile element
  if (!document.getElementById('profilePicture') && !document.querySelector('.profile-banner')) return;

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  /* ---------- Utilities: toasts & modal helpers ---------- */
  function showToast(message, isError = false, timeout = 3500) {
    // simple floating toast (bootstrap optional)
    const toast = document.createElement('div');
    toast.className = `admin-toast ${isError ? 'admin-toast-error' : 'admin-toast-success'}`;
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
      // create a modal node
      const wrapper = document.createElement('div');
      wrapper.innerHTML = `
      <div class="admin-confirm-modal-backdrop" style="position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;">
        <div class="admin-confirm-modal" style="background:#fff;border-radius:8px;max-width:420px;width:92%;padding:1rem;box-shadow:0 8px 30px rgba(0,0,0,.2);">
          <div class="admin-confirm-body">${htmlBody}</div>
          <div style="display:flex;gap:.6rem;justify-content:center;margin-top:.8rem;">
            <button class="admin-confirm-cancel" style="padding:.5rem .9rem;border-radius:6px;border:1px solid #dcdcdc;background:#fff;">Cancel</button>
            <button class="admin-confirm-ok" style="padding:.5rem .9rem;border-radius:6px;border:0;background:#0d6efd;color:#fff;">Confirm</button>
          </div>
        </div>
      </div>`;
      document.body.appendChild(wrapper);
      const cancelBtn = wrapper.querySelector('.admin-confirm-cancel');
      const okBtn = wrapper.querySelector('.admin-confirm-ok');

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
  /* ---------- Avatar upload button trigger ---------- */
  const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');

  if (uploadPhotoBtn && avatarInput) {
    uploadPhotoBtn.addEventListener('click', (e) => {
      e.preventDefault();
      console.log("🟢 Upload Photo button clicked");
      avatarInput.click(); // ✅ opens file dialog
    });
  }

  /* ---------- Profile edit toggles ---------- */
  // hide edit mode if not present
  if (editMode) editMode.style.display = 'none';
  if (saveButtonsContainer) saveButtonsContainer.style.display = 'none';

  function enableEditing() {
    if (!editMode || !displayMode || !updateForm) return;
    displayMode.style.display = 'none';
    editMode.style.display = 'block';
    if (editPersonalBtn) editPersonalBtn.style.display = 'none';
    if (saveButtonsContainer) saveButtonsContainer.style.display = 'flex';
    // Make inputs editable except readonly ones (admin id, email)
    updateForm.querySelectorAll('input').forEach(i => {
      if (i.hasAttribute('data-noreadonly') || i.getAttribute('name') === 'admin_id' || i.readOnly) return;
      i.readOnly = false;
    });
  }

  function cancelEditing() {
    if (!editMode || !displayMode || !updateForm) return;
    // revert visible values by reloading or re-fetch original values (simpler: reload)
    // here we restore by reloading to keep server state in sync
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
    // ensure method override is set if your form action expects PUT; we add _method if not present
    updateForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const action = updateForm.action || updateForm.getAttribute('data-action') || null;
      if (!action) {
        console.warn('Profile update: form action not set. Aborting AJAX update.');
        showToast('Profile update failed: missing form action', true);
        return;
      }

      const fd = new FormData(updateForm);
      // if server expects PUT / _method field:
      if (!fd.has('_method')) fd.append('_method', 'PUT');

      try {
        const res = await fetch(action, {
          method: 'POST', // using method override
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: fd
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast(data.message || 'Profile updated');
          // reflect changes visually: reload to be safe
          setTimeout(() => window.location.reload(), 900);
        } else {
          // show errors (if provided)
          const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Update failed');
          showToast(msg, true);
        }
      } catch (err) {
        console.error('Profile update error', err);
        showToast('Error saving profile', true);
      }
    });

    // If user clicks Save button in the edit form, keep UX consistent
    const saveBtn = updateForm.querySelector('button[type="submit"], .btn-save');
    if (saveBtn) saveBtn.addEventListener('click', (ev) => {
      // if button is not type submit, trigger submit
      if (saveBtn.getAttribute('type') !== 'submit') updateForm.dispatchEvent(new Event('submit', {cancelable:true}));
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
    // reset checklist styling
    ['length','uppercase','lowercase','number','special'].forEach(k => {
      const el = document.getElementById(k);
      if (el) el.classList.remove('valid'), el.classList.add('invalid');
    });
  }
  // attach a global function so inline calls from blade still work:
  window.cancelPasswordEdit = cancelPasswordEdit;

  /* ---------- Password validation checklist ---------- */
  function setChecklistState(key, ok) {
    const el = document.getElementById(key);
    if (!el) return;
    el.classList.toggle('valid', ok);
    el.classList.toggle('invalid', !ok);

    // show check icon when valid, circle when not (support both markup styles)
    // if the element contains <i> icons, toggle classes
    const checkIcon = el.querySelector('.check-icon');
    const circleIcon = el.querySelector('.circle-icon');
    if (checkIcon && circleIcon) {
      checkIcon.classList.toggle('d-none', !ok);
      circleIcon.classList.toggle('d-none', ok);
    } else {
      // fallback: prepend/replace inline Unicode icons
      if (ok) {
        if (!el.querySelector('.admin-check-mark')) {
          el.insertAdjacentHTML('afterbegin', '<span class="admin-check-mark" style="display:inline-block;width:18px;margin-right:.45rem;color:#28a745;">✓</span>');
        }
        // remove any circle if present
        const circ = el.querySelector('.admin-circle-mark');
        if (circ) circ.remove();
      } else {
        // remove check if present, ensure circle exists
        const chk = el.querySelector('.admin-check-mark');
        if (chk) chk.remove();
        if (!el.querySelector('.admin-circle-mark')) {
          el.insertAdjacentHTML('afterbegin', '<span class="admin-circle-mark" style="display:inline-block;width:18px;margin-right:.45rem;color:#9aa0a6;">○</span>');
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

  // toggle show/hide password (exposed too)
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
      // ensure action present
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

      // Basic client-side check that rules pass
      const passed = ['length','uppercase','lowercase','number','special'].every(k => {
        const el = document.getElementById(k); return el && el.classList.contains('valid');
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

    // in case button is not submit type
    const passwordBtn = passwordForm.querySelector('button[type="submit"], .btn-save');
    if (passwordBtn) passwordBtn.addEventListener('click', (ev) => {
      if (passwordBtn.getAttribute('type') !== 'submit') passwordForm.dispatchEvent(new Event('submit', {cancelable:true}));
    });
  }

  /* ---------- Avatar preview + confirmation + upload ---------- */
  async function handleAvatarFile(file) {
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showToast('Please pick an image file', true); return;
    }
    if (file.size > 5 * 1024 * 1024) {
      showToast('Image must be under 5MB', true); return;
    }

    // preview data URL
    const reader = new FileReader();
    reader.onload = async (ev) => {
      const previewSrc = ev.target.result;
      // show confirm modal with preview
      const html = `<div style="text-align:center;"><img src="${previewSrc}" style="width:120px;height:120px;border-radius:999px;object-fit:cover;display:block;margin:0 auto 0.6rem;"><div>Save this as your new profile picture?</div></div>`;
      const ok = await confirmModal(html);
      if (!ok) return;

      // form action = avatar upload action
      // find a form with id avatarForm or fallback to data-action on input
      const avatarFormEl = document.getElementById('avatarForm');
      let action = (avatarFormEl && avatarFormEl.action) ? avatarFormEl.action : (document.getElementById('avatarUpload')?.getAttribute('data-action') || (document.getElementById('avatarUpload')?.form?.action || null));
      if (!action) {
        // fallback guess: use current location + 'admin/profile/avatar' - but warn
        console.warn('Avatar upload: no action found on form/input. Aborting upload.');
        showToast('Avatar upload failed: missing endpoint', true);
        return;
      }

      const uploadFD = new FormData();
      uploadFD.append('avatar', file);
      // server might expect _token; but we'll send header + form includes token if present.
      try {
        const res = await fetch(action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: uploadFD
        });
        const data = await res.json();
        if (res.ok && data.success) {
          // update preview and sidebar
          if (profilePicture && data.avatar_url) {
            profilePicture.src = data.avatar_url;
            localStorage.setItem('profileImage', data.avatar_url);
          }
          showToast(data.message || 'Avatar updated');
        } else {
          const msg = data.message || 'Upload failed';
          showToast(msg, true);
        }
      } catch (err) {
        console.error('Avatar upload error', err);
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
    // expose previewAvatar for inline onChange fallback
    window.previewAvatar = (ev) => {
      const file = ev.target.files[0];
      handleAvatarFile(file);
    };
  }

  /* ---------- Load stored avatar (client side fallback) ---------- */
  try {
    const saved = localStorage.getItem('profileImage');
    if (saved && profilePicture) profilePicture.src = saved;
  } catch (err) { /* ignore */ }


  /* ---------- keyboard accessibility: allow Enter on edit buttons ---------- */
  [editPersonalBtn, editPasswordBtn].forEach(btn => {
    if (!btn) return;
    btn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
    });
  });

}); // DOMContentLoaded
