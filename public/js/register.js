/* register.js — Student Registration (cache-first, council/CCO-safe, DOM-optional) */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', async function () {
    // -------- Route sources (from meta tag) --------
    const routesEl = document.getElementById('slea-routes');
    const URLS = {
      programs:         routesEl?.dataset.programs || '',
      majors:           routesEl?.dataset.majors || '',
      clusters:         routesEl?.dataset.clusters || '',
      organizations:    routesEl?.dataset.organizations || '',
      positions:        routesEl?.dataset.positions || '',
      councilPositions: routesEl?.dataset.councilPositions || '',
      academicsMap:     routesEl?.dataset.academicsMap || ''
    };

    // -------- Step/UI --------
    let currentStep = 0;
    const formSteps   = document.querySelectorAll('.form-step');
    const pageNumbers = document.querySelectorAll('.page-number');
    const prevBtn     = document.getElementById('prevBtn');
    const nextBtn     = document.getElementById('nextBtn');
    const form        = document.querySelector('form');

    // -------- Fields (basic) --------
    const studentIdInput    = document.querySelector('input[name="student_id"]');
    const yearLevelSelect   = document.querySelector('select[name="year_level"]');
    const expectedGradInput = document.querySelector('input[name="expected_grad"]');
    const birthDateInput    = document.querySelector('input[name="birth_date"]');
    const ageInput          = document.querySelector('input[name="age"]');

    expectedGradInput && (expectedGradInput.readOnly = true);
    ageInput && (ageInput.readOnly = true);

    // -------- Helpers --------
    function resetDropdown(el, placeholder = 'Select') {
      if (!el) return;
      el.innerHTML = `<option value="">${placeholder}</option>`;
      el.disabled = false;
    }

    function setOptions(el, rows, selected) {
      if (!el) return;
      (rows || []).forEach(r => {
        const id = String(r.id);
        const label = r.name ?? r.program_name ?? r.major_name ?? id;
        el.insertAdjacentHTML('beforeend', `<option value="${id}">${label}</option>`);
      });
      if (selected !== undefined && selected !== null && String(selected) !== '') {
        el.value = String(selected);
      }
    }

    // expects [{id, name}] (or similar)
    function setOptionsFromArray(el, rows, selected) {
      if (!el) return;
      rows = rows || [];
      rows.forEach(r => {
        const id = String(r.id);
        const label = r.name ?? id;
        el.insertAdjacentHTML('beforeend', `<option value="${id}">${label}</option>`);
      });
      if (selected !== undefined && selected !== null && String(selected) !== '') {
        el.value = String(selected);
      }
    }

    // normalize: accepts array OR object map { id: name }
    function normalizeIdNameList(rows) {
      if (!rows) return [];
      if (Array.isArray(rows)) return rows;
      if (typeof rows === 'object') {
        return Object.entries(rows).map(([id, name]) => ({ id, name }));
      }
      return [];
    }

    async function safeFetchJson(url, { retries = 2, signal } = {}) {
      if (!url) return null;
      for (let i = 0; i <= retries; i++) {
        try {
          const r = await fetch(url, { headers: { 'Accept': 'application/json' }, signal });
          if (!r.ok) throw new Error(`HTTP ${r.status}`);
          return await r.json();
        } catch (e) {
          if (i === retries) return null;
          await new Promise(res => setTimeout(res, 150 * (i + 1)));
        }
      }
      return null;
    }

    // -------- Auto Age / Expected Grad --------
    function updateExpectedGrad() {
      if (!expectedGradInput || !studentIdInput || !yearLevelSelect) return;

      const m = (studentIdInput.value.trim()).match(/^(\d{4})/);
      const entryYear = m ? parseInt(m[1], 10) : null;

      if (!entryYear) {
        expectedGradInput.value = '';
        return;
      }

      // ✅ match backend default duration
      const DEFAULT_DURATION = 4;
      expectedGradInput.value = String(entryYear + DEFAULT_DURATION);

      // Optional: infer year level
      const currentYear = new Date().getFullYear();
      let inferredYearLevel = currentYear - entryYear + 1;

      const numericOptions = [...yearLevelSelect.options]
        .map(o => parseInt(o.value, 10))
        .filter(n => !Number.isNaN(n));

      if (numericOptions.length) {
        const minLevel = Math.min(...numericOptions);
        const maxLevel = Math.max(...numericOptions);
        if (inferredYearLevel < minLevel) inferredYearLevel = minLevel;
        if (inferredYearLevel > maxLevel) inferredYearLevel = maxLevel;
      }

      const inferred = String(inferredYearLevel);
      [...yearLevelSelect.options].forEach(o => {
        o.selected = (o.value === inferred);
      });
    }

    function updateAge() {
      if (!ageInput || !birthDateInput?.value) { if (ageInput) ageInput.value = ''; return; }
      const b = new Date(birthDateInput.value);
      if (isNaN(b)) { ageInput.value = ''; return; }
      const t = new Date();
      let age = t.getFullYear() - b.getFullYear();
      if (t.getMonth() < b.getMonth() || (t.getMonth() === b.getMonth() && t.getDate() < b.getDate())) age--;
      ageInput.value = String(age);
    }

    studentIdInput?.addEventListener('input', updateExpectedGrad);
    yearLevelSelect?.addEventListener('change', updateExpectedGrad);
    birthDateInput?.addEventListener('change', updateAge);

    // =========================
    // ACADEMICS (optional init)
    // =========================
    const collegeSelect = document.querySelector('select[name="college_id"], select[name="college_name"]');
    const programSelect = document.querySelector('select[name="program_id"], select[name="program"]');
    const majorSelect   = document.querySelector('select[name="major_id"], select[name="major_name"]');

    async function initAcademicsIfPresent() {
      if (!collegeSelect || !programSelect || !majorSelect) return; // ✅ safely skip if not used

      const isIdMode = !!document.querySelector('select[name="college_id"]');
      const oldProgramId = programSelect?.dataset.old || '';
      const oldMajorId   = majorSelect?.dataset.old || '';

      let programsByCollege = {};
      let majorsByProgram   = {};
      let mapLoaded = false;

      if (URLS.academicsMap) {
        const map = await safeFetchJson(`${URLS.academicsMap}?_=${Date.now()}`);
        if (map?.programsByCollege && map?.majorsByProgram) {
          programsByCollege = map.programsByCollege || {};
          majorsByProgram   = map.majorsByProgram || {};
          mapLoaded = true;
        }
      }

      let progCtrl, majCtrl, progSeq = 0, majSeq = 0;

      async function loadPrograms(collegeId, selectId = '') {
        const seq = ++progSeq;
        resetDropdown(programSelect, 'Loading programs...');
        resetDropdown(majorSelect, 'Select Major');

        if (isIdMode && mapLoaded) {
          resetDropdown(programSelect, 'Select Program');
          setOptions(programSelect, programsByCollege[collegeId] || [], selectId);
          return;
        }

        if (!URLS.programs || !collegeId) {
          resetDropdown(programSelect, 'Select Program');
          return;
        }

        progCtrl?.abort();
        progCtrl = new AbortController();
        const rows = await safeFetchJson(
          `${URLS.programs}?college_id=${encodeURIComponent(collegeId)}&_=${Date.now()}`,
          { signal: progCtrl.signal }
        );
        if (seq !== progSeq) return;

        resetDropdown(programSelect, 'Select Program');
        setOptions(programSelect, (rows || []).map(x => ({ id: x.id, name: x.name || x.program_name })), selectId);
      }

      async function loadMajors(programId, selectId = '') {
        const seq = ++majSeq;
        resetDropdown(majorSelect, 'Loading majors...');

        if (isIdMode && mapLoaded) {
          resetDropdown(majorSelect, 'Select Major');
          setOptions(majorSelect, majorsByProgram[programId] || [], selectId);
          return;
        }

        if (!URLS.majors || !programId) {
          resetDropdown(majorSelect, 'Select Major');
          return;
        }

        majCtrl?.abort();
        majCtrl = new AbortController();
        const rows = await safeFetchJson(
          `${URLS.majors}?program_id=${encodeURIComponent(programId)}&_=${Date.now()}`,
          { signal: majCtrl.signal }
        );
        if (seq !== majSeq) return;

        resetDropdown(majorSelect, 'Select Major');
        setOptions(majorSelect, (rows || []).map(x => ({ id: x.id, name: x.name || x.major_name })), selectId);
      }

      collegeSelect.addEventListener('change', () => {
        const cid = collegeSelect.value;
        if (mapLoaded && isIdMode) {
          resetDropdown(programSelect, 'Select Program');
          setOptions(programSelect, programsByCollege[cid] || [], '');
          resetDropdown(majorSelect, 'Select Major');
        } else {
          loadPrograms(cid, '');
        }
      });

      programSelect.addEventListener('change', () => {
        const pid = programSelect.value;
        if (mapLoaded && isIdMode) {
          resetDropdown(majorSelect, 'Select Major');
          setOptions(majorSelect, majorsByProgram[pid] || [], '');
        } else {
          loadMajors(pid, '');
        }
      });

      // Initial boot
      if (collegeSelect.value) {
        if (mapLoaded && isIdMode) {
          resetDropdown(programSelect, 'Select Program');
          setOptions(programSelect, programsByCollege[collegeSelect.value] || [], oldProgramId || '');
          resetDropdown(majorSelect, 'Select Major');
          setOptions(majorSelect, majorsByProgram[oldProgramId] || [], oldMajorId || '');
        } else {
          await loadPrograms(collegeSelect.value, oldProgramId || '');
          if (oldProgramId) await loadMajors(oldProgramId, oldMajorId || '');
        }
      }
    }

    // =========================
    // LEADERSHIP (optional init)
    // =========================
    const leadershipTypeSelect = document.getElementById('leadership_type_id');
    const clusterSelect        = document.getElementById('cluster_id');
    const organizationSelect   = document.getElementById('organization_id');
    const positionSelect       = document.getElementById('position_id');

    const clusterWrap  = document.getElementById('cluster_wrap');
    const orgWrap      = document.getElementById('org_wrap');
    const clusterStar  = document.getElementById('cluster_required_star');
    const orgStar      = document.getElementById('org_required_star');
    const orgOptHint   = document.getElementById('org_optional_hint');

    async function initLeadershipIfPresent() {
      if (!leadershipTypeSelect || !clusterSelect || !organizationSelect || !positionSelect) return; // ✅ skip if not used

      const URL_COUNCIL_POS = URLS.councilPositions || '';

      // old values (for validation errors)
      let oldLeadershipType = leadershipTypeSelect?.dataset.old || leadershipTypeSelect?.value || '';
      let oldCluster        = clusterSelect?.dataset.old || '';
      let oldOrg            = organizationSelect?.dataset.old || '';
      let oldPosition       = positionSelect?.dataset.old || '';

      function setVisible(el, show) {
        if (!el) return;
        el.style.display = show ? '' : 'none';
      }

      function setRequired(el, required, starEl) {
        if (!el) return;
        if (required) {
          el.setAttribute('required', 'required');
          if (starEl) { starEl.removeAttribute('hidden'); starEl.style.display = ''; }
        } else {
          el.removeAttribute('required');
          if (starEl) { starEl.setAttribute('hidden', ''); starEl.style.display = 'none'; }
        }
      }

      function setDisabled(el, disabled) {
        if (!el) return;
        el.disabled = disabled;
        el.style.backgroundColor = disabled ? '#e9ecef' : '';
        el.style.cursor = disabled ? 'not-allowed' : '';
      }

      function isCCOSelected() {
        const opt = leadershipTypeSelect?.selectedOptions?.[0];
        if (!opt) return false;
        const key = (opt.dataset.key || '').toLowerCase();
        return key === 'cco' || /council of clubs and organizations/i.test(opt.textContent || '');
      }

      function isSCOSelected() {
        const opt = leadershipTypeSelect?.selectedOptions?.[0];
        if (!opt) return false;
        const key = (opt.dataset.key || '').toLowerCase();
        return key === 'sco' || /student clubs and organizations/i.test(opt.textContent || '');
      }

      function toggleScrollableForm(enableScroll) {
        const scrollableContent = document.querySelector('.step-3-scrollable-content');
        const formStep = document.querySelector('.form-step-scrollable');
        if (!scrollableContent || !formStep) return;

        if (enableScroll) {
          scrollableContent.style.overflowY = 'auto';
          scrollableContent.style.maxHeight = 'calc(100vh - 380px)';
          formStep.classList.add('form-step-scrollable-active');
        } else {
          scrollableContent.style.overflowY = 'hidden';
          scrollableContent.style.maxHeight = 'none';
          formStep.classList.remove('form-step-scrollable-active');
        }
      }

      async function loadPositionsByLeadershipType(typeId, selectedPos = '') {
        if (!positionSelect) return;

        if (!typeId) {
          resetDropdown(positionSelect, 'Select Leadership Type first');
          return;
        }

        resetDropdown(positionSelect, 'Loading positions...');

        const positionsUrl = URL_COUNCIL_POS || URLS.positions || '';
        if (!positionsUrl) {
          resetDropdown(positionSelect, 'Select Position');
          return;
        }

        const url = `${positionsUrl}?leadership_type_id=${encodeURIComponent(typeId)}&_=${Date.now()}`;
        const rows = await safeFetchJson(url);

        const list = normalizeIdNameList(rows);
        resetDropdown(positionSelect, list.length ? 'Select Position' : 'No positions available');
        if (!list.length) return;

        setOptionsFromArray(positionSelect, list, selectedPos);

        // after first restore, stop forcing old values
        oldPosition = '';
      }

      async function loadOrgPositions(orgId, selectedPos = '') {
        resetDropdown(positionSelect, 'Loading positions...');
        if (!URLS.positions || !orgId) {
          resetDropdown(positionSelect, 'Select Position');
          return;
        }

        const rows = await safeFetchJson(
          `${URLS.positions}?organization_id=${encodeURIComponent(orgId)}&_=${Date.now()}`
        );

        const list = normalizeIdNameList(rows);
        resetDropdown(positionSelect, list.length ? 'Select Position' : 'No positions available');
        if (!list.length) return;

        setOptionsFromArray(positionSelect, list, selectedPos);
        oldPosition = '';
      }

      async function loadClusters() {
        resetDropdown(clusterSelect, 'Loading clusters...');
        const pairs = await safeFetchJson(`${URLS.clusters}?_=${Date.now()}`);

        const list = normalizeIdNameList(pairs);
        resetDropdown(clusterSelect, 'Select Cluster');
        if (!list.length) return;

        setOptionsFromArray(clusterSelect, list, oldCluster || '');
        if (oldCluster) clusterSelect.dispatchEvent(new Event('change'));
        oldCluster = '';
      }

      async function loadOrganizations(clusterId) {
        resetDropdown(organizationSelect, 'Loading organizations...');
        if (!isSCOSelected()) resetDropdown(positionSelect, 'Select Position');

        if (!clusterId) {
          resetDropdown(organizationSelect, 'Select Organization');
          return;
        }

        const pairs = await safeFetchJson(
          `${URLS.organizations}?cluster_id=${encodeURIComponent(clusterId)}&_=${Date.now()}`
        );

        const list = normalizeIdNameList(pairs);
        resetDropdown(organizationSelect, 'Select Organization');
        if (!list.length) return;

        setOptionsFromArray(organizationSelect, list, oldOrg || '');
        if (oldOrg) organizationSelect.dispatchEvent(new Event('change'));
        oldOrg = '';
      }

      // CCO: hide cluster/org and submit empty values (backend stores NULL)
      function handleCCOSelection() {
        setVisible(clusterWrap, false);
        setVisible(orgWrap, false);
        setRequired(clusterSelect, false, clusterStar);
        setRequired(organizationSelect, false, orgStar);

        resetDropdown(clusterSelect, 'N/A');
        clusterSelect.innerHTML = '<option value="" selected>N/A</option>';

        resetDropdown(organizationSelect, 'N/A');
        organizationSelect.innerHTML = '<option value="" selected>N/A</option>';

        setDisabled(clusterSelect, false);
        setDisabled(organizationSelect, false);

        toggleScrollableForm(false);

        const typeId = leadershipTypeSelect.value;
        loadPositionsByLeadershipType(typeId, oldPosition || '');
      }

      function handleSCOSelection() {
        setDisabled(clusterSelect, false);
        setDisabled(organizationSelect, false);

        setVisible(clusterWrap, true);
        setVisible(orgWrap, true);
        setRequired(clusterSelect, true, clusterStar);
        setRequired(organizationSelect, true, orgStar);
        if (orgOptHint) orgOptHint.style.display = 'none';

        toggleScrollableForm(true);

        loadClusters();

        const typeId = leadershipTypeSelect.value;
        loadPositionsByLeadershipType(typeId, oldPosition || '');
      }

      function handleNonCCOSelection() {
        setVisible(clusterWrap, false);
        setVisible(orgWrap, false);
        setRequired(clusterSelect, false, clusterStar);
        setRequired(organizationSelect, false, orgStar);
        if (orgOptHint) orgOptHint.style.display = 'none';

        resetDropdown(clusterSelect, 'Select Cluster');
        resetDropdown(organizationSelect, 'Select Organization');

        setDisabled(clusterSelect, false);
        setDisabled(organizationSelect, false);

        toggleScrollableForm(false);

        const typeId = leadershipTypeSelect.value;
        loadPositionsByLeadershipType(typeId, oldPosition || '');
      }

      leadershipTypeSelect.addEventListener('change', () => {
        const typeId = leadershipTypeSelect.value;

        resetDropdown(clusterSelect, 'Select Cluster');
        resetDropdown(organizationSelect, 'Select Organization');
        resetDropdown(positionSelect, 'Select Position');

        if (!typeId) {
          setVisible(clusterWrap, false);
          setVisible(orgWrap, false);
          setRequired(clusterSelect, false, clusterStar);
          setRequired(organizationSelect, false, orgStar);
          setDisabled(clusterSelect, false);
          setDisabled(organizationSelect, false);
          if (orgOptHint) orgOptHint.style.display = 'none';
          resetDropdown(positionSelect, 'Select Leadership Type first');
          toggleScrollableForm(false);
          return;
        }

        if (isCCOSelected()) handleCCOSelection();
        else if (isSCOSelected()) handleSCOSelection();
        else handleNonCCOSelection();
      });

      clusterSelect.addEventListener('change', () => {
        const clusterId = clusterSelect.value;
        if (!isCCOSelected() && clusterId) loadOrganizations(clusterId);

        // keep SCO positions loaded by type (not org)
        if (isSCOSelected() && leadershipTypeSelect.value) {
          loadPositionsByLeadershipType(leadershipTypeSelect.value, oldPosition || '');
        }
      });

      organizationSelect.addEventListener('change', () => {
        const orgId = organizationSelect.value;

        if (isSCOSelected() && leadershipTypeSelect.value) {
          loadPositionsByLeadershipType(leadershipTypeSelect.value, oldPosition || '');
          return;
        }

        if (!isCCOSelected() && !isSCOSelected() && orgId) {
          loadOrgPositions(orgId, oldPosition || '');
        }
      });

      // restore old state once
      if (oldLeadershipType) {
        leadershipTypeSelect.value = oldLeadershipType;
        leadershipTypeSelect.dispatchEvent(new Event('change'));
        oldLeadershipType = '';
      } else {
        toggleScrollableForm(false);
      }
    }

    // -------- Multi-step --------
    pageNumbers.forEach((p, i) =>
      p.addEventListener('click', () => {
        if (!validateStep()) return;
        currentStep = i;
        showStep(i);
      })
    );

    function showStep(n) {
      formSteps.forEach((s, i) => s.classList.toggle('active', i === n));
      pageNumbers.forEach((p, i) => {
        p.classList.remove('active', 'completed');
        if (i < n) p.classList.add('completed');
        if (i === n) p.classList.add('active');
      });

      if (prevBtn) prevBtn.style.display = (n === 0) ? 'none' : '';

      if (nextBtn) nextBtn.textContent = (n === formSteps.length - 1) ? 'Submit' : 'Next';
    }

    function validateStep() {
      let ok = true;
      const step = formSteps[currentStep];
      if (!step) return true;

      step.querySelectorAll('input,select').forEach(i => {
        if (!i.checkValidity()) { i.classList.add('is-invalid'); ok = false; }
        else i.classList.remove('is-invalid');
      });
      return ok;
    }

    window.nextPrev = function (n) {
      if (n === 1 && !validateStep()) return false;
      currentStep += n;

      if (currentStep >= formSteps.length) {
        submitForm();
        return false;
      }

      showStep(currentStep);
      return false;
    };

    showStep(currentStep);

    // -------- Password live check --------
    const passwordInput = document.getElementById('password');
    const checks = {
      length: /.{8,}/,
      uppercase: /[A-Z]/,
      lowercase: /[a-z]/,
      number: /[0-9]/,
      special: /[^A-Za-z0-9]/
    };

    passwordInput?.addEventListener('input', () => {
      Object.keys(checks).forEach(k => {
        const el = document.getElementById(k); if (!el) return;
        const good = checks[k].test(passwordInput.value);
        el.classList.toggle('text-success', good);
        el.classList.toggle('text-danger', !good);
        const ico = el.querySelector('i');
        if (ico) {
          ico.classList.toggle('fa-circle-check', good);
          ico.classList.toggle('fa-circle-xmark', !good);
        }
      });
    });

    // -------- Password visibility toggle --------
    document.querySelectorAll('.toggle-password').forEach(icon => {
      icon.addEventListener('click', () => {
        const targetId = icon.dataset.target;
        const target = document.getElementById(targetId);
        if (!target) return;

        const isPassword = target.type === 'password';
        target.type = isPassword ? 'text' : 'password';

        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
        icon.setAttribute('title', isPassword ? 'Hide password' : 'Show password');
      });
    });

    // -------- Dark mode --------
    const body = document.body;
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleBtn2 = document.getElementById('darkModeToggleFloating');

    function applyTheme(mode) {
      const d = mode === 'dark';
      body.classList.toggle('dark-mode', d);
      toggleBtn?.querySelector('i')?.classList.replace(d ? 'fa-moon' : 'fa-sun', d ? 'fa-sun' : 'fa-moon');
      toggleBtn2?.querySelector('i')?.classList.replace(d ? 'fa-moon' : 'fa-sun', d ? 'fa-sun' : 'fa-moon');
      localStorage.setItem('theme', mode);
    }

    applyTheme(localStorage.getItem('theme') || 'light');
    const flip = () => applyTheme(body.classList.contains('dark-mode') ? 'light' : 'dark');
    toggleBtn?.addEventListener('click', flip);
    toggleBtn2?.addEventListener('click', flip);

    // -------- Form Submission with Modal --------
    async function submitForm() {
      if (!form || !nextBtn) return;

      nextBtn.disabled = true;
      nextBtn.textContent = 'Submitting...';

      try {
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
          // ✅ no redirect: 'manual' (we want clean JSON)
        });

        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json')
          ? await response.json().catch(() => null)
          : null;

        // 422 validation
        if (response.status === 422 && data?.errors) {
          handleFormErrors(data.errors);

          // jump to the step containing the first invalid
          currentStep = 0;
          formSteps.forEach((step, index) => {
            if (step.querySelector('.is-invalid')) currentStep = index;
          });
          showStep(currentStep);
          return;
        }

        // ✅ success path (preferred)
        if (response.ok && (data?.ok === true || data?.success === true)) {
          showSuccessModal();
          return;
        }

        // fallback: if backend still returns redirect HTML but status is ok
        if (response.ok && !data) {
          showSuccessModal();
          return;
        }

        alert(data?.message || 'Registration failed. Please try again.');
      } catch (error) {
        console.error('Registration error:', error);
        alert('An error occurred during registration. Please try again.');
      } finally {
        nextBtn.disabled = false;
        // ✅ restore correct label for current step
        nextBtn.textContent = (currentStep === formSteps.length - 1) ? 'Submit' : 'Next';
      }
    }

    function handleFormErrors(errors) {
      if (!form) return;

      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

      Object.keys(errors).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) return;

        input.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = errors[field]?.[0] || 'Invalid value';
        input.parentNode?.appendChild(errorDiv);
      });

      const firstError = form.querySelector('.is-invalid');
      firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function showSuccessModal() {
      const modal = document.getElementById('successModal');
      if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      } else {
        // fallback if modal missing
        redirectToLogin();
      }
    }

    function hideSuccessModal() {
      const modal = document.getElementById('successModal');
      if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
      }
    }

    function redirectToLogin() {
      const loginLink = document.querySelector('a[href*="login"]');
      window.location.href = loginLink?.href || '/login';
    }

    const modalOkayBtn = document.getElementById('modalOkayBtn');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const successModal = document.getElementById('successModal');

    modalOkayBtn?.addEventListener('click', () => { hideSuccessModal(); redirectToLogin(); });
    modalCloseBtn?.addEventListener('click', () => { hideSuccessModal(); redirectToLogin(); });

    successModal?.addEventListener('click', (e) => {
      if (e.target === successModal) {
        hideSuccessModal();
        redirectToLogin();
      }
    });

    // -------- Init optional blocks --------
    await initAcademicsIfPresent();
    await initLeadershipIfPresent();
  });
})();
