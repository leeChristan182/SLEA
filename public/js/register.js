/* register.js — Student Registration (cache-first, council/CCO-safe) */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', async function () {
    // -------- Route sources (from meta tag) --------
    const routesEl = document.getElementById('slea-routes');
    const URLS = {
      programs:        routesEl?.dataset.programs || '',
      majors:          routesEl?.dataset.majors || '',
      clusters:        routesEl?.dataset.clusters || '',
      organizations:   routesEl?.dataset.organizations || '',
      positions:       routesEl?.dataset.positions || '',
      councilPositions:routesEl?.dataset.councilPositions || '',
      academicsMap:    routesEl?.dataset.academicsMap || ''
    };

    // -------- Step/UI --------
    let currentStep = 0;
    const formSteps   = document.querySelectorAll('.form-step');
    const pageNumbers = document.querySelectorAll('.page-number');
    const prevBtn     = document.getElementById('prevBtn');
    const nextBtn     = document.getElementById('nextBtn');
    const form        = document.querySelector('form');

    // -------- Fields --------
    const studentIdInput    = document.querySelector('input[name="student_id"]');
    const yearLevelSelect   = document.querySelector('select[name="year_level"]');
    const expectedGradInput = document.querySelector('input[name="expected_grad"]');
    const birthDateInput    = document.querySelector('input[name="birth_date"]');
    const ageInput          = document.querySelector('input[name="age"]');

    const collegeSelect     = document.querySelector('select[name="college_id"], select[name="college_name"]');
    const programSelect     = document.querySelector('select[name="program_id"]');
    const majorSelect       = document.querySelector('select[name="major_id"]');

    const leadershipTypeSelect = document.getElementById('leadership_type_id');
    const clusterWrap          = document.getElementById('cluster_wrap');
    const orgWrap              = document.getElementById('org_wrap');
    const clusterSelect        = document.getElementById('cluster_id');
    const organizationSelect   = document.getElementById('organization_id');
    const positionSelect       = document.getElementById('position_id');
    const orgOptHint           = document.getElementById('org_optional_hint');
    const clusterStar          = document.getElementById('cluster_required_star');
    const orgStar              = document.getElementById('org_required_star');

    // -------- Utilities --------
    function resetDropdown(el, placeholder = 'Select an option') {
      if (!el) return;
      el.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = placeholder;
      el.appendChild(opt);
    }

    function setOptionsFromPairs(el, pairs, selected) {
      if (!el) return;
      if (!pairs) return;
      // pairs might be object map or array of {id, name}
      if (Array.isArray(pairs)) {
        pairs.forEach(row => {
          if (!row) return;
          const id = row.id ?? row.value ?? row.key ?? row[0];
          const label = row.name ?? row.label ?? row[1] ?? row.text ?? String(id);
          if (id == null) return;
          const opt = document.createElement('option');
          opt.value = String(id);
          opt.textContent = label;
          el.appendChild(opt);
        });
      } else {
        Object.entries(pairs).forEach(([id, label]) => {
          const opt = document.createElement('option');
          opt.value = String(id);
          opt.textContent = String(label);
          el.appendChild(opt);
        });
      }
      if (selected) el.value = String(selected);
    }

    function setOptionsFromArray(el, rows, selected) {
      if (!el) return;
      rows = rows || [];
      rows.forEach(r => {
        const id = String(r.id);
        const label = r.name ?? id;
        el.insertAdjacentHTML('beforeend', `<option value="${id}">${label}</option>`);
      });
      if (selected) el.value = String(selected);
    }

    async function safeFetchJson(url, { retries = 2, signal } = {}) {
      for (let i = 0; i <= retries; i++) {
        try {
          const r = await fetch(url, {
            method: 'GET',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            signal
          });
          if (!r.ok) throw new Error(`HTTP ${r.status}`);
          const data = await r.json();
          return data;
        } catch (err) {
          if (i === retries) {
            console.error('Failed to fetch', url, err);
            return null;
          }
        }
      }
    }

    // -------- Age auto-calc --------
    function computeAgeFromBirthdate(birthValue) {
      if (!birthValue) return '';
      const birthDate = new Date(birthValue);
      if (Number.isNaN(birthDate.getTime())) return '';
      const today = new Date();

      let age = today.getFullYear() - birthDate.getFullYear();
      const m = today.getMonth() - birthDate.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      return age >= 0 ? String(age) : '';
    }

    birthDateInput?.addEventListener('change', () => {
      ageInput.value = computeAgeFromBirthdate(birthDateInput.value);
    });

    // If old value exists (from validation) and age is empty, compute once
    if (birthDateInput?.value && !ageInput?.value) {
      ageInput.value = computeAgeFromBirthdate(birthDateInput.value);
    }

    // -------- Expected Grad auto-calc --------
    function computeExpectedGrad(yearLevel, studentIdVal) {
      // Basic heuristic: parse first 4 digits of student ID as admission year
      if (!yearLevel || !studentIdVal) return '';
      const match = String(studentIdVal).match(/^(\d{4})/);
      if (!match) return '';
      const admitYear = parseInt(match[1], 10);
      if (Number.isNaN(admitYear)) return '';

      const yLvl = parseInt(yearLevel, 10);
      if (Number.isNaN(yLvl) || yLvl < 1) return '';
      // Assume a 4 or 5-year program but we can't reliably know which.
      // We'll approximate expected grad as: admission + (5 - currentYearLevel).
      // e.g. admitted 2023, now 2nd year → 2023 + (5 - 2) = 2026
      const yearsNeeded = 5 - yLvl;
      const gradYear = admitYear + (yearsNeeded > 0 ? yearsNeeded : 4);
      return `${gradYear}`;
    }

    function updateExpectedGrad() {
      if (!yearLevelSelect || !studentIdInput || !expectedGradInput) return;
      const val = computeExpectedGrad(yearLevelSelect.value, studentIdInput.value);
      expectedGradInput.value = val;
    }

    yearLevelSelect?.addEventListener('change', updateExpectedGrad);
    studentIdInput?.addEventListener('input', updateExpectedGrad);

    // Initial if old values exist
    if (yearLevelSelect?.value && studentIdInput?.value && expectedGradInput && !expectedGradInput.value) {
      expectedGradInput.value = computeExpectedGrad(yearLevelSelect.value, studentIdInput.value);
    }

    // -------- Academic (Programs/Majors) --------
    const oldCollegeId = collegeSelect?.dataset.old || collegeSelect?.value || '';
    const oldProgramId = programSelect?.dataset.old || programSelect?.value || '';
    const oldMajorId   = majorSelect?.dataset.old   || majorSelect?.value   || '';

    async function loadPrograms(collegeId, selectedProgram) {
      resetDropdown(programSelect, 'Select Program');
      resetDropdown(majorSelect, 'Select Major');

      if (!collegeId || !URLS.programs) return;

      const data = await safeFetchJson(
        `${URLS.programs}?college_id=${encodeURIComponent(collegeId)}&_=${Date.now()}`
      );
      setOptionsFromPairs(programSelect, data, selectedProgram || '');
    }

    async function loadMajors(programId, selectedMajor) {
      resetDropdown(majorSelect, 'Select Major');
      if (!programId || !URLS.majors) return;

      const data = await safeFetchJson(
        `${URLS.majors}?program_id=${encodeURIComponent(programId)}&_=${Date.now()}`
      );
      setOptionsFromPairs(majorSelect, data, selectedMajor || '');
    }

    collegeSelect?.addEventListener('change', () => {
      const cid = collegeSelect.value;
      loadPrograms(cid, '');
    });

    programSelect?.addEventListener('change', () => {
      const pid = programSelect.value;
      loadMajors(pid, '');
    });

    // If we had old selections (validation fail), restore them
    if (oldCollegeId && collegeSelect) {
      collegeSelect.value = oldCollegeId;
      if (oldProgramId) {
        await loadPrograms(oldCollegeId, oldProgramId || '');
        if (oldMajorId) await loadMajors(oldProgramId, oldMajorId || '');
      } else {
        await loadPrograms(collegeSelect.value, oldProgramId || '');
        if (oldProgramId) await loadMajors(oldProgramId, oldMajorId || '');
      }
    }

    // -------- Leadership (Council vs CCO) --------
    const URL_COUNCIL_POS = URLS.councilPositions || '';

    const oldLeadershipType = leadershipTypeSelect?.dataset.old || leadershipTypeSelect?.value || '';
    const oldCluster        = clusterSelect?.dataset.old || '';
    const oldOrg            = organizationSelect?.dataset.old || '';
    const oldPosition       = positionSelect?.dataset.old || '';

    function setVisible(el, show) {
      if (!el) return;
      el.style.display = show ? '' : 'none';
    }

    function setRequired(el, required, starEl) {
      if (!el) return;
      if (required) {
        el.setAttribute('required', 'required');
        if (starEl) {
          starEl.removeAttribute('hidden');
          starEl.style.display = '';
        }
      } else {
        el.removeAttribute('required');
        if (starEl) {
          starEl.setAttribute('hidden', '');
          starEl.style.display = 'none';
        }
      }
    }

    function isCCOSelected() {
      const opt = leadershipTypeSelect?.selectedOptions?.[0];
      if (!opt) return false;
      const key = (opt.dataset.key || '').toLowerCase();
      return key === 'cco' || /council of clubs and organizations/i.test(opt.textContent || '');
    }

    /**
     * Non-CCO leadership types (USG, OSC, LC, LGU, LCM, etc.)
     * Fetch positions filtered by leadership_type_id
     */
    async function loadCouncilPositionsForType(typeId, selectedPos = '') {
      resetDropdown(positionSelect, 'Loading positions...');

      if (!URL_COUNCIL_POS || !typeId) {
        resetDropdown(positionSelect, 'Select Position');
        return;
      }

      const rows = await safeFetchJson(
        `${URL_COUNCIL_POS}?leadership_type_id=${encodeURIComponent(typeId)}&_=${Date.now()}`
      );

      resetDropdown(positionSelect, 'Select Position');
      if (!rows) return;

      const list = Array.isArray(rows) ? rows : rows;
      setOptionsFromArray(positionSelect, list, selectedPos);
    }

    /**
     * Load clusters for CCO
     */
    async function loadClustersForCCO() {
      resetDropdown(clusterSelect, 'Loading clusters...');
      const pairs = await safeFetchJson(`${URLS.clusters}?_=${Date.now()}`);
      resetDropdown(clusterSelect, 'Select Cluster');
      if (!pairs) return;

      const rows = Array.isArray(pairs)
        ? pairs
        : Object.entries(pairs || {}).map(([id, name]) => ({ id, name }));

      setOptionsFromArray(clusterSelect, rows, oldCluster || '');

      // If we had an old cluster (validation error), trigger follow-up load
      if (oldCluster) {
        clusterSelect.dispatchEvent(new Event('change'));
      }
    }

    /**
     * Load organizations for a given cluster (CCO)
     */
    async function loadOrganizationsForCluster(clusterId) {
      resetDropdown(organizationSelect, 'Loading organizations...');
      resetDropdown(positionSelect, 'Select Position');

      if (!clusterId) {
        resetDropdown(organizationSelect, 'Select Organization');
        return;
      }

      const pairs = await safeFetchJson(
        `${URLS.organizations}?cluster_id=${encodeURIComponent(clusterId)}&_=${Date.now()}`
      );

      resetDropdown(organizationSelect, 'Select Organization');
      if (!pairs) return;

      const rows = Array.isArray(pairs)
        ? pairs
        : Object.entries(pairs || {}).map(([id, name]) => ({ id, name }));

      setOptionsFromArray(organizationSelect, rows, oldOrg || '');

      if (oldOrg) {
        organizationSelect.dispatchEvent(new Event('change'));
      }
    }

    /**
     * Load positions for a specific CCO organization
     */
    async function loadPositionsForCcoOrg(orgId, selectedPos = '') {
      resetDropdown(positionSelect, 'Loading positions...');

      if (!URLS.positions || !orgId) {
        resetDropdown(positionSelect, 'Select Position');
        return;
      }

      const rows = await safeFetchJson(
        `${URLS.positions}?organization_id=${encodeURIComponent(orgId)}&_=${Date.now()}`
      );

      resetDropdown(positionSelect, 'Select Position');
      if (!rows) return;

      const list = Array.isArray(rows) ? rows : rows;
      setOptionsFromArray(positionSelect, list, selectedPos);
    }

    // --- Event handlers ---

    leadershipTypeSelect?.addEventListener('change', () => {
      const typeId = leadershipTypeSelect.value;

      // Reset dropdowns whenever type changes
      resetDropdown(clusterSelect, 'Select Cluster');
      resetDropdown(organizationSelect, 'Select Organization');
      resetDropdown(positionSelect, 'Select Position');

      if (!typeId) {
        // No leadership type selected → hide cluster/org, nothing required
        setVisible(clusterWrap, false);
        setVisible(orgWrap, false);
        setRequired(clusterSelect, false, clusterStar);
        setRequired(organizationSelect, false, orgStar);
        if (orgOptHint) orgOptHint.style.display = 'none';
        return;
      }

      if (isCCOSelected()) {
        // CCO → need Cluster + Org; positions depend on Org
        setVisible(clusterWrap, true);
        setVisible(orgWrap, true);
        setRequired(clusterSelect, true, clusterStar);
        setRequired(organizationSelect, true, orgStar);
        if (orgOptHint) orgOptHint.style.display = '';

        loadClustersForCCO();
      } else {
        // Council types (non-CCO) → only Leadership Type + Position
        setVisible(clusterWrap, false);
        setVisible(orgWrap, false);
        setRequired(clusterSelect, false, clusterStar);
        setRequired(organizationSelect, false, orgStar);
        if (orgOptHint) orgOptHint.style.display = 'none';

        loadCouncilPositionsForType(typeId, oldPosition || '');
      }
    });

    clusterSelect?.addEventListener('change', () => {
      const clusterId = clusterSelect.value;
      loadOrganizationsForCluster(clusterId);
    });

    organizationSelect?.addEventListener('change', () => {
      const orgId = organizationSelect.value;

      if (!orgId) {
        resetDropdown(positionSelect, 'Select Position');
        return;
      }

      if (isCCOSelected()) {
        loadPositionsForCcoOrg(orgId, oldPosition || '');
      }
    });

    // Restore previous selection (e.g., after validation error)
    if (oldLeadershipType) {
      leadershipTypeSelect.value = oldLeadershipType;
      leadershipTypeSelect.dispatchEvent(new Event('change'));
    }

    // -------- Multi-step --------
    function showStep(n) {
      if (!formSteps.length) return;
      formSteps.forEach((step, idx) => {
        step.style.display = idx === n ? 'block' : 'none';
        pageNumbers[idx]?.classList.toggle('active', idx === n);
      });

      prevBtn.disabled = n === 0;
      nextBtn.textContent = n === (formSteps.length - 1) ? 'Submit' : 'Next';
    }

    function validateStep(n) {
      const current = formSteps[n];
      if (!current) return true;
      const requiredFields = Array.from(current.querySelectorAll('[required]'));
      let valid = true;
      requiredFields.forEach(field => {
        if (!field.checkValidity()) {
          field.classList.add('is-invalid');
          valid = false;
        } else {
          field.classList.remove('is-invalid');
        }
      });
      return valid;
    }

    window.nextPrev = function (direction) {
      if (direction === 1 && !validateStep(currentStep)) {
        return;
      }
      currentStep += direction;
      if (currentStep >= formSteps.length) {
        // Last step; submit
        form?.submit();
        return;
      }
      showStep(currentStep);
    };

    showStep(currentStep);

    // Clear invalid on input
    form?.addEventListener('input', e => {
      const target = e.target;
      if (target.matches('.is-invalid')) {
        if (target.checkValidity()) {
          target.classList.remove('is-invalid');
        }
      }
    });

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

        const icon = el.querySelector('i');
        if (icon) {
          icon.classList.toggle('fa-circle-check', good);
          icon.classList.toggle('fa-circle-xmark', !good);
        }
      });
    });

    // -------- Dark mode --------
    const body = document.body,
      toggleBtn = document.getElementById('darkModeToggle'),
      toggleBtn2 = document.getElementById('darkModeToggleFloating');

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
  });
})();
