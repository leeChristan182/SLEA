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
  const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
  const avatarInput = document.getElementById('avatarUpload');
  const avatarForm = document.getElementById('avatarForm');
  const profilePicture = document.getElementById('profilePicture');

  const avatarPreview = document.getElementById('avatarPreview') || profilePicture;

  if (uploadPhotoBtn && avatarInput) {
    uploadPhotoBtn.addEventListener('click', (e) => {
      e.preventDefault();
      avatarInput.click();
    });
  }

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
          if (avatarPreview) avatarPreview.src = data.avatar_url + '?v=' + Date.now();
          if (profilePicture) profilePicture.src = data.avatar_url + '?v=' + Date.now();
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
    const val = document.getElementById('password')?.value || '';
    const rules = {
      length: val.length >= 8,
      uppercase: /[A-Z]/.test(val),
      lowercase: /[a-z]/.test(val),
      number: /\d/.test(val),
      special: /[^A-Za-z0-9]/.test(val),
    };
    const order = Object.keys(rules);
    Object.entries(rules).forEach(([key, pass]) => {
      const idx = order.indexOf(key) + 1;
      const el = document.querySelector(`#passwordChecklist li:nth-child(${idx})`);
      if (el) {
        el.style.color = pass ? '#16a34a' : '#333';
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

  /* ============================================================
   *  LEADERSHIP MODAL (mirror registration leadership behavior)
   * ============================================================ */
  const routesMeta = document.getElementById('slea-routes');
  const clustersUrl =
    routesMeta?.dataset.clusters || '/api/clusters';
  const orgsUrl =
    routesMeta?.dataset.organizations || '/api/organizations';
  const councilPositionsUrl =
    // prefer council-positions if defined, else fall back to positions
    routesMeta?.dataset.councilPositions ||
    routesMeta?.dataset.positions ||
    '/api/council-positions';

  const ltSelect = document.getElementById('modal_leadership_type_id');
  const clusterWrap = document.getElementById('modal_cluster_wrap');
  const orgWrap = document.getElementById('modal_org_wrap');
  const clusterSelect = document.getElementById('modal_cluster_id');
  const orgSelect = document.getElementById('modal_organization_id');
  const posSelect = document.getElementById('modal_position_id');
  const clusterStar = document.getElementById('modal_cluster_required_star');
  const orgStar = document.getElementById('modal_org_required_star');
  const orgOptionalHint = document.getElementById('modal_org_optional_hint');

  function clearOptions(select, placeholder = 'Select') {
    if (!select) return;
    select.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = placeholder;
    select.appendChild(opt);
  }

  async function loadClusters(leadershipTypeId) {
    if (!clusterSelect) return;
    clearOptions(clusterSelect, 'Select Cluster');

    if (!leadershipTypeId) return;
    try {
      const res = await fetch(`${clustersUrl}?leadership_type_id=${encodeURIComponent(leadershipTypeId)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();
      // data might be object {id:name} or array; normalize
      if (Array.isArray(data)) {
        data.forEach(row => {
          const opt = document.createElement('option');
          opt.value = row.id;
          opt.textContent = row.name || row.label || row.id;
          clusterSelect.appendChild(opt);
        });
      } else {
        Object.entries(data || {}).forEach(([id, name]) => {
          const opt = document.createElement('option');
          opt.value = id;
          opt.textContent = name;
          clusterSelect.appendChild(opt);
        });
      }
    } catch (err) {
      console.error('loadClusters error', err);
    }
  }

  async function loadOrgs(clusterId, leadershipTypeId) {
    if (!orgSelect) return;
    clearOptions(orgSelect, 'Select Organization');
    if (!clusterId) return;
    try {
      const url = `${orgsUrl}?cluster_id=${encodeURIComponent(clusterId)}${
        leadershipTypeId ? `&leadership_type_id=${encodeURIComponent(leadershipTypeId)}` : ''
      }`;
      const res = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();
      if (Array.isArray(data)) {
        data.forEach(row => {
          const opt = document.createElement('option');
          opt.value = row.id;
          opt.textContent = row.name || row.label || row.id;
          orgSelect.appendChild(opt);
        });
      } else {
        Object.entries(data || {}).forEach(([id, name]) => {
          const opt = document.createElement('option');
          opt.value = id;
          opt.textContent = name;
          orgSelect.appendChild(opt);
        });
      }
    } catch (err) {
      console.error('loadOrgs error', err);
    }
  }

  async function loadPositions(leadershipTypeId, organizationId = null) {
    if (!posSelect) return;
    clearOptions(posSelect, 'Select Position');
    if (!leadershipTypeId && !organizationId) return;

    try {
      const params = new URLSearchParams();
      if (leadershipTypeId) params.set('leadership_type_id', leadershipTypeId);
      if (organizationId) params.set('organization_id', organizationId);

      const res = await fetch(`${councilPositionsUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();
      (data || []).forEach(row => {
        const opt = document.createElement('option');
        opt.value = row.id;
        opt.textContent = row.name || row.alias || row.label || row.id;
        posSelect.appendChild(opt);
      });
    } catch (err) {
      console.error('loadPositions error', err);
    }
  }

  function handleLeadershipTypeChange() {
    if (!ltSelect) return;
    const option = ltSelect.selectedOptions[0];
    if (!option) return;

    const requiresOrg = option.dataset.requiresOrg === '1';
    const key = option.dataset.key || '';
    const isCCO = key === 'cco';

    // Show/hide cluster + org
    if (requiresOrg || isCCO) {
      if (clusterWrap) clusterWrap.style.display = '';
      if (orgWrap) orgWrap.style.display = '';
      if (clusterStar) clusterStar.style.display = '';
      if (orgStar) orgStar.style.display = isCCO ? '' : 'none';
      if (orgOptionalHint) orgOptionalHint.style.display = isCCO ? 'none' : '';
    } else {
      if (clusterWrap) clusterWrap.style.display = 'none';
      if (orgWrap) orgWrap.style.display = 'none';
      if (clusterStar) clusterStar.style.display = 'none';
      if (orgStar) orgStar.style.display = 'none';
      if (orgOptionalHint) orgOptionalHint.style.display = 'none';
      if (clusterSelect) clusterSelect.value = '';
      if (orgSelect) orgSelect.value = '';
    }

    // Load clusters (if needed) and positions
    const typeId = ltSelect.value || '';
    if (requiresOrg || isCCO) {
      loadClusters(typeId);
    }
    loadPositions(typeId, null);
  }

  ltSelect?.addEventListener('change', handleLeadershipTypeChange);

  clusterSelect?.addEventListener('change', () => {
    const cid = clusterSelect.value || '';
    const typeId = ltSelect?.value || '';
    if (!cid) {
      clearOptions(orgSelect, 'Select Organization');
      loadPositions(typeId, null);
      return;
    }
    loadOrgs(cid, typeId).then(() => {
      // Reset positions when org list changes
      loadPositions(typeId, orgSelect?.value || null);
    });
  });

  orgSelect?.addEventListener('change', () => {
    const typeId = ltSelect?.value || '';
    const orgId = orgSelect.value || '';
    loadPositions(typeId, orgId || null);
  });

  // Reset modal on open
  const leadershipModal = document.getElementById('addLeadershipModal');
  if (leadershipModal) {
    leadershipModal.addEventListener('shown.bs.modal', () => {
      const form = document.getElementById('updateLeadershipForm');
      if (form) form.reset();
      clearOptions(clusterSelect, 'Select Cluster');
      clearOptions(orgSelect, 'Select Organization');
      clearOptions(posSelect, 'Select Position');
      if (clusterWrap) clusterWrap.style.display = 'none';
      if (orgWrap) orgWrap.style.display = 'none';
      if (clusterStar) clusterStar.style.display = 'none';
      if (orgStar) orgStar.style.display = 'none';
      if (orgOptionalHint) orgOptionalHint.style.display = 'none';
    });
  }
});
