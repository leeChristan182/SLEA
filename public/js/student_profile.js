/* student_profile.js
   Unified Student Profile Handler (Profile, Academic, Leadership, COR, Avatar, Password)
   Requires meta[name="csrf-token"] and proper route actions in Blade.
*/

document.addEventListener('DOMContentLoaded', () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  /* -------------------- SIDEBAR + DARK MODE -------------------- */
  const hamburger = document.querySelector('.menu-toggle');
  const overlay = document.querySelector('.sidebar-overlay');
  hamburger?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
  overlay?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
  window.toggleDarkMode = () => document.body.classList.toggle('dark-mode');

  /* -------------------- TOAST + MODAL HELPERS -------------------- */
  function showToast(msg, isError = false, timeout = 3500) {
    const el = document.createElement('div');
    el.textContent = msg;
    el.style.cssText = `
      position:fixed;top:1rem;right:1rem;z-index:9999;
      background:${isError ? '#dc3545' : '#198754'};
      color:white;padding:.6rem 1rem;border-radius:.4rem;
      font-weight:600;box-shadow:0 4px 14px rgba(0,0,0,.15);
    `;
    document.body.appendChild(el);
    setTimeout(() => { el.style.transition = 'opacity .3s'; el.style.opacity = '0'; }, timeout);
    setTimeout(() => el.remove(), timeout + 400);
  }

  function confirmModal(html) {
    return new Promise(resolve => {
      const wrap = document.createElement('div');
      wrap.innerHTML = `
      <div style="position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:8px;padding:1rem 1.4rem;max-width:420px;width:92%;box-shadow:0 8px 30px rgba(0,0,0,.2);">
          ${html}
          <div style="display:flex;gap:.6rem;justify-content:center;margin-top:.8rem;">
            <button class="cancel-btn" style="padding:.4rem .9rem;border:1px solid #ccc;border-radius:6px;">Cancel</button>
            <button class="ok-btn" style="padding:.4rem .9rem;background:#0d6efd;color:#fff;border:none;border-radius:6px;">Confirm</button>
          </div>
        </div>
      </div>`;
      document.body.appendChild(wrap);
      wrap.querySelector('.cancel-btn').onclick = () => { wrap.remove(); resolve(false); };
      wrap.querySelector('.ok-btn').onclick = () => { wrap.remove(); resolve(true); };
    });
  }

  /* -------------------- AVATAR UPLOAD -------------------- */
  const avatarInput = document.getElementById('avatarUpload');
  const avatarForm = document.getElementById('avatarForm');
  const avatarPreview = document.getElementById('avatarPreview');

  async function handleAvatarFile(file) {
    if (!file) return;
    if (!file.type.startsWith('image/')) return showToast('Select a valid image', true);
    if (file.size > 5 * 1024 * 1024) return showToast('Image must be under 5MB', true);

    const reader = new FileReader();
    reader.onload = async ev => {
      const html = `
        <div style="text-align:center;">
          <img src="${ev.target.result}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:8px;">
          <p>Use this as your new profile picture?</p>
        </div>`;
      const ok = await confirmModal(html);
      if (!ok) return;

      const fd = new FormData();
      fd.append('avatar', file);

      try {
        const res = await fetch(avatarForm.action, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        });
        const data = await res.json();
        if (res.ok && data.success) {
          avatarPreview.src = data.avatar_url;
          showToast('Avatar updated successfully');
        } else showToast(data.message || 'Upload failed', true);
      } catch (err) {
        console.error(err);
        showToast('Upload error', true);
      }
    };
    reader.readAsDataURL(file);
  }

  avatarInput?.addEventListener('change', e => handleAvatarFile(e.target.files[0]));

  /* -------------------- PASSWORD SHOW/HIDE + VALIDATION -------------------- */
  window.togglePassword = () => {
    ['current_password', 'password', 'password_confirmation'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.type = el.type === 'password' ? 'text' : 'password';
    });
  };

  window.validatePassword = () => {
    const val = document.getElementById('password').value;
    const rules = {
      length: val.length >= 8,
      uppercase: /[A-Z]/.test(val),
      lowercase: /[a-z]/.test(val),
      number: /\d/.test(val),
      special: /[^A-Za-z0-9]/.test(val),
    };
    Object.entries(rules).forEach(([key, pass]) => {
      const el = document.querySelector(`#passwordChecklist li:nth-child(${Object.keys(rules).indexOf(key)+1})`);
      if (el) {
        el.style.color = pass ? '#16a34a' : '#555';
        el.style.fontWeight = pass ? '600' : '400';
      }
    });
  };

  /* -------------------- AJAX FORM HANDLER (generic) -------------------- */
  async function handleAjaxForm(formSelector, successMsg) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const fd = new FormData(form);
      try {
        const res = await fetch(form.action, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast(data.message || successMsg);
          setTimeout(() => window.location.reload(), 1000);
        } else {
          showToast(data.message || 'Error saving changes', true);
        }
      } catch (err) {
        console.error(err);
        showToast('Request failed', true);
      }
    });
  }

  /* -------------------- APPLY AJAX HANDLERS -------------------- */
  handleAjaxForm('#updatePersonalForm', 'Personal information updated');
  handleAjaxForm('#updateAcademicForm', 'Academic information updated');
  handleAjaxForm('#updateLeadershipForm', 'Leadership information saved');
  handleAjaxForm('#uploadCORForm', 'Certificate of Registration uploaded');
  handleAjaxForm('#passwordChangeForm', 'Password changed successfully');
});
