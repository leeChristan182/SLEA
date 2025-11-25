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
    const programSelect     = document.querySelector('select[name="program_id"], select[name="program"]');
    const majorSelect       = document.querySelector('select[name="major_id"], select[name="major_name"]');

    // Leadership (council/CCO)
    const leadershipTypeSelect = document.getElementById('leadership_type_id');
    const clusterSelect        = document.getElementById('cluster_id');
    const organizationSelect   = document.getElementById('organization_id');
    const positionSelect       = document.getElementById('position_id');

    // Wrappers / indicators (Step 3)
    const clusterWrap  = document.getElementById('cluster_wrap');
    const orgWrap      = document.getElementById('org_wrap');
    const clusterStar  = document.getElementById('cluster_required_star');
    const orgStar      = document.getElementById('org_required_star');
    const orgOptHint   = document.getElementById('org_optional_hint');

    const councilFlagInput = document.querySelector('input[name="is_council"]');
    const isCouncilMode = !!(councilFlagInput && String(councilFlagInput.value) === '1');

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
      if (selected) el.value = String(selected);
    }

    // convenience wrapper: expects [{id, name}]
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
      if (!entryYear) { expectedGradInput.value = ''; return; }
      const totalYears = 4;
      expectedGradInput.value = entryYear + totalYears;

      const currentYear = new Date().getFullYear();
      const inferred = Math.min(currentYear - entryYear + 1, totalYears).toString();
      [...yearLevelSelect.options].forEach(o => { if (o.value === inferred) o.selected = true; });
    }

    function updateAge() {
      if (!ageInput || !birthDateInput?.value) { if (ageInput) ageInput.value = ''; return; }
      const b = new Date(birthDateInput.value); if (isNaN(b)) { ageInput.value = ''; return; }
      const t = new Date();
      let age = t.getFullYear() - b.getFullYear();
      if (t.getMonth() < b.getMonth() || (t.getMonth() === b.getMonth() && t.getDate() < b.getDate())) age--;
      ageInput.value = age;
    }

    studentIdInput?.addEventListener('input', updateExpectedGrad);
    yearLevelSelect?.addEventListener('change', updateExpectedGrad);
    birthDateInput?.addEventListener('change', updateAge);

    // -------- Academics (cache-first map) --------
    const isIdMode = !!document.querySelector('select[name="college_id"]');
    const oldProgramId = programSelect?.dataset.old || '';
    const oldMajorId   = majorSelect?.dataset.old || '';

    let programsByCollege = {}; // { collegeId: [{id,name}] }
    let majorsByProgram   = {}; // { programId: [{id,name}] }
    let mapLoaded = false;

    if (URLS.academicsMap) {
      const map = await safeFetchJson(`${URLS.academicsMap}?_=${Date.now()}`);
      if (map?.programsByCollege && map?.majorsByProgram) {
        programsByCollege = map.programsByCollege || {};
        majorsByProgram   = map.majorsByProgram || {};
        mapLoaded = true;
      }
    }

    // Abort-guards for noisy users
    let progCtrl, majCtrl, progSeq = 0, majSeq = 0;

    async function loadPrograms(collegeId, selectId = '') {
      const seq = ++progSeq;
      resetDropdown(programSelect, 'Loading programs...');
      resetDropdown(majorSelect, 'Select Major');

      // cache-first when possible
      if (isIdMode && mapLoaded) {
        const rows = programsByCollege[collegeId] || [];
        resetDropdown(programSelect, 'Select Program');
        setOptions(programSelect, rows, selectId);
        return;
      }

      if (!URLS.programs || !collegeId) {
        resetDropdown(programSelect, 'Select Program');
        return;
      }

      progCtrl?.abort();
      progCtrl = new AbortController();
      const rows = await safeFetchJson(`${URLS.programs}?college_id=${encodeURIComponent(collegeId)}&_=${Date.now()}`, { signal: progCtrl.signal });
      if (seq !== progSeq) return; // stale response
      resetDropdown(programSelect, 'Select Program');
      setOptions(programSelect, (rows || []).map(x => ({ id: x.id, name: x.name || x.program_name })), selectId);
    }

    async function loadMajors(programId, selectId = '') {
      const seq = ++majSeq;
      resetDropdown(majorSelect, 'Loading majors...');

      // cache-first when possible
      if (isIdMode && mapLoaded) {
        const rows = majorsByProgram[programId] || [];
        resetDropdown(majorSelect, 'Select Major');
        setOptions(majorSelect, rows, selectId);
        return;
      }

      if (!URLS.majors || !programId) {
        resetDropdown(majorSelect, 'Select Major');
        return;
      }

      majCtrl?.abort();
      majCtrl = new AbortController();
      const rows = await safeFetchJson(`${URLS.majors}?program_id=${encodeURIComponent(programId)}&_=${Date.now()}`, { signal: majCtrl.signal });
      if (seq !== majSeq) return; // stale response
      resetDropdown(majorSelect, 'Select Major');
      setOptions(majorSelect, (rows || []).map(x => ({ id: x.id, name: x.name || x.major_name })), selectId);
    }

    collegeSelect?.addEventListener('change', () => {
      const cid = collegeSelect.value;
      if (mapLoaded && isIdMode) {
        resetDropdown(programSelect, 'Select Program');
        setOptions(programSelect, programsByCollege[cid] || [], '');
        resetDropdown(majorSelect, 'Select Major');
      } else {
        loadPrograms(cid, '');
      }
    });

    programSelect?.addEventListener('change', () => {
      const pid = programSelect.value;
      if (mapLoaded && isIdMode) {
        resetDropdown(majorSelect, 'Select Major');
        setOptions(majorSelect, majorsByProgram[pid] || [], '');
      } else {
        loadMajors(pid, '');
      }
    });

    // Initial boot for Academics
    if (collegeSelect?.value) {
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

    function setDisabled(el, disabled) {
      if (!el) return;
      el.disabled = disabled;
      if (disabled) {
        el.setAttribute('readonly', 'readonly');
        el.style.backgroundColor = '#e9ecef';
        el.style.cursor = 'not-allowed';
      } else {
        el.removeAttribute('readonly');
        el.style.backgroundColor = '';
        el.style.cursor = '';
      }
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

    // Load positions by leadership_type_id
    async function loadPositionsByLeadershipType(typeId, selectedPos = '') {
      if (!positionSelect) return;

      resetDropdown(positionSelect, 'Select Leadership Type first');
      if (!typeId) {
        resetDropdown(positionSelect, 'Select Leadership Type first');
        return;
      }

      resetDropdown(positionSelect, 'Loading positions...');

      // Use councilPositions route if available, otherwise fallback to positions route
      const positionsUrl = URL_COUNCIL_POS || URLS.positions || '';
      if (!positionsUrl) {
        resetDropdown(positionSelect, 'Select Position');
        return;
      }

      // Build URL with leadership_type_id parameter
      const url = `${positionsUrl}?leadership_type_id=${encodeURIComponent(typeId)}&_=${Date.now()}`;

      const rows = await safeFetchJson(url);
      resetDropdown(positionSelect, 'Select Position');

      if (!rows || !Array.isArray(rows) || rows.length === 0) {
        resetDropdown(positionSelect, 'No positions available');
        return;
      }

      setOptionsFromArray(positionSelect, rows, selectedPos);
    }

    // CCO club/org positions via organization (for non-CCO organizations)
    async function loadOrgPositions(orgId, selectedPos = '') {
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

    async function loadClusters() {
      resetDropdown(clusterSelect, 'Loading clusters...');
      const pairs = await safeFetchJson(`${URLS.clusters}?_=${Date.now()}`);
      resetDropdown(clusterSelect, 'Select Cluster');
      if (!pairs) return;
      const rows = Array.isArray(pairs)
        ? pairs
        : Object.entries(pairs || {}).map(([id, name]) => ({ id, name }));
      setOptionsFromArray(clusterSelect, rows, oldCluster || '');
      if (oldCluster) {
        clusterSelect.dispatchEvent(new Event('change'));
      }
    }

    async function loadOrganizations(clusterId) {
      resetDropdown(organizationSelect, 'Loading organizations...');
      // Only reset positions if NOT SCO (SCO positions are loaded by leadership type, not org)
      if (!isSCOSelected()) {
        resetDropdown(positionSelect, 'Select Position');
      }
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

    // Handle CCO special case: Hide Cluster and Organization (set to N/A in backend)
    function handleCCOSelection() {
      // Hide Cluster and Organization fields completely
      setVisible(clusterWrap, false);
      setVisible(orgWrap, false);
      setRequired(clusterSelect, false, clusterStar);
      setRequired(organizationSelect, false, orgStar);

      // Set values to N/A for backend (fields are hidden but values are submitted)
      resetDropdown(clusterSelect, 'Select Cluster');
      clusterSelect.innerHTML = '<option value="N/A" selected>N/A</option>';
      resetDropdown(organizationSelect, 'Select Organization');
      organizationSelect.innerHTML = '<option value="N/A" selected>N/A</option>';

      // Ensure fields are not disabled so values are submitted
      setDisabled(clusterSelect, false);
      setDisabled(organizationSelect, false);

      // Disable scrolling for CCO (non-SCO layout - 5 fields)
      toggleScrollableForm(false);

      // Load CCO positions
      const typeId = leadershipTypeSelect.value;
      loadPositionsByLeadershipType(typeId, oldPosition || '');
    }

    // Handle SCO selection: Show and enable Cluster and Organization
    function handleSCOSelection() {
      // Re-enable Cluster and Organization
      setDisabled(clusterSelect, false);
      setDisabled(organizationSelect, false);

      // Show Cluster and Organization fields for SCO
      setVisible(clusterWrap, true);
      setVisible(orgWrap, true);
      setRequired(clusterSelect, true, clusterStar);
      setRequired(organizationSelect, true, orgStar);
      if (orgOptHint) orgOptHint.style.display = 'none';

      // Enable scrolling for SCO (7 fields layout)
      toggleScrollableForm(true);

      // Load clusters
      loadClusters();

      // Load positions for the selected leadership type (SCO positions)
      const typeId = leadershipTypeSelect.value;
      loadPositionsByLeadershipType(typeId, oldPosition || '');
    }

    // Handle non-CCO, non-SCO selection: Hide Cluster and Organization
    function handleNonCCOSelection() {
      // Hide Cluster and Organization fields completely
      setVisible(clusterWrap, false);
      setVisible(orgWrap, false);
      setRequired(clusterSelect, false, clusterStar);
      setRequired(organizationSelect, false, orgStar);
      if (orgOptHint) orgOptHint.style.display = 'none';

      // Clear values (fields won't be submitted for non-SCO, non-CCO)
      resetDropdown(clusterSelect, 'Select Cluster');
      resetDropdown(organizationSelect, 'Select Organization');

      // Re-enable fields (in case they were disabled)
      setDisabled(clusterSelect, false);
      setDisabled(organizationSelect, false);

      // Disable scrolling for non-SCO (5 fields layout - single viewport)
      toggleScrollableForm(false);

      // Load positions for the selected leadership type
      const typeId = leadershipTypeSelect.value;
      loadPositionsByLeadershipType(typeId, oldPosition || '');
    }

    // Toggle scrollable form container based on SCO selection
    function toggleScrollableForm(enableScroll) {
      const scrollableContent = document.querySelector('.step-3-scrollable-content');
      const formStep = document.querySelector('.form-step-scrollable');

      if (!scrollableContent || !formStep) return;

      if (enableScroll) {
        // SCO: Enable scrolling
        scrollableContent.style.overflowY = 'auto';
        scrollableContent.style.maxHeight = 'calc(100vh - 380px)';
        formStep.classList.add('form-step-scrollable-active');
      } else {
        // Non-SCO: Disable scrolling (single viewport)
        scrollableContent.style.overflowY = 'hidden';
        scrollableContent.style.maxHeight = 'none';
        formStep.classList.remove('form-step-scrollable-active');
      }
    }

    // Single change handler for Leadership Type
    leadershipTypeSelect?.addEventListener('change', () => {
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
        toggleScrollableForm(false); // Default: no scrolling
        return;
      }

      if (isCCOSelected()) {
        // CCO special case: N/A for Cluster and Organization
        handleCCOSelection();
      } else if (isSCOSelected()) {
        // SCO: Show and enable Cluster and Organization (required)
        handleSCOSelection();
      } else {
        // Non-CCO, non-SCO: Hide Cluster and Organization, load positions by type
        handleNonCCOSelection();
      }
    });

    clusterSelect?.addEventListener('change', () => {
      const clusterId = clusterSelect.value;
      // Only load organizations if not CCO and cluster is selected
      // For SCO, load organizations normally (positions are already loaded by leadership type)
      if (!isCCOSelected() && clusterId) {
        loadOrganizations(clusterId);
      }
      // For SCO, ensure positions remain loaded (they're loaded by leadership type, not org)
      if (isSCOSelected() && leadershipTypeSelect.value) {
        const typeId = leadershipTypeSelect.value;
        loadPositionsByLeadershipType(typeId, oldPosition || '');
      }
    });

    organizationSelect?.addEventListener('change', () => {
      const orgId = organizationSelect.value;
      // For SCO, positions are loaded by leadership type, not by organization
      // Ensure positions remain loaded for SCO (they're loaded by leadership type, not org)
      if (isSCOSelected() && leadershipTypeSelect.value) {
        const typeId = leadershipTypeSelect.value;
        loadPositionsByLeadershipType(typeId, oldPosition || '');
        return; // Don't load positions by organization for SCO
      }
      // Only load positions via organization for other types (not CCO, not SCO)
      if (!isCCOSelected() && !isSCOSelected() && orgId) {
        loadOrgPositions(orgId, oldPosition || '');
      }
    });

    // Restore old state on load (for validation errors)
    if (oldLeadershipType) {
      leadershipTypeSelect.value = oldLeadershipType;
      leadershipTypeSelect.dispatchEvent(new Event('change'));
    } else {
      // Initialize: no scrolling by default (non-SCO layout)
      toggleScrollableForm(false);
    }

    // -------- Multi-step --------
    pageNumbers.forEach((p, i) =>
      p.addEventListener('click', () => { if (!validateStep()) return; currentStep = i; showStep(i); })
    );

    function showStep(n) {
      formSteps.forEach((s, i) => s.classList.toggle('active', i === n));
      pageNumbers.forEach((p, i) => {
        p.classList.remove('active', 'completed');
        if (i < n) p.classList.add('completed');
        if (i === n) p.classList.add('active');
      });
      // Hide Previous button on first page (step 0), show on steps 1, 2, 3
      if (prevBtn) {
        if (n === 0) {
          prevBtn.style.display = 'none';
        } else {
          prevBtn.style.display = '';
          prevBtn.disabled = false;
        }
      }
      nextBtn.textContent = n === formSteps.length - 1 ? 'Submit' : 'Next';
    }

    window.nextPrev = function (n) {
      if (n === 1 && !validateStep()) return false;
      currentStep += n;
      if (currentStep >= formSteps.length) { form.submit(); return false; }
      showStep(currentStep);
    };

    function validateStep() {
      let ok = true;
      formSteps[currentStep].querySelectorAll('input,select').forEach(i => {
        if (!i.checkValidity()) { i.classList.add('is-invalid'); ok = false; }
        else i.classList.remove('is-invalid');
      });
      return ok;
    }

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
