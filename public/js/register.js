/* register.js — Student Registration (cache-first, council-only safe) */
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

    // Leadership (council-mode supported)
    const leadershipTypeSelect = document.getElementById('leadership_type_id');
    const clusterSelect        = document.getElementById('cluster_id');
    const organizationSelect   = document.getElementById('organization_id');
    const positionSelect       = document.getElementById('position_id');

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

    // -------- Leadership (Council-first) --------
   // ------- Leadership: Council types vs CCO drilldown -------
const URL_COUNCIL_POS = routesEl?.dataset.councilPositions || '';

const oldLeadershipType = leadershipTypeSelect?.dataset.old || leadershipTypeSelect?.value || '';
const oldCluster        = clusterSelect?.dataset.old || clusterSelect?.value || '';
const oldOrg            = organizationSelect?.dataset.old || organizationSelect?.value || '';
const oldPosition       = positionSelect?.dataset.old || positionSelect?.value || '';

function setVisible(el, show) {
  if (!el) return;
  el.hidden = !show;
}
function setRequired(el, req, starEl) {
  if (!el) return;
  if (req) { el.setAttribute('required','required'); starEl?.removeAttribute('hidden'); }
  else { el.removeAttribute('required'); starEl?.setAttribute('hidden',''); }
}
function isCCOSelected() {
  const opt = leadershipTypeSelect?.selectedOptions?.[0];
  return opt && (opt.dataset.kind === 'cco'
    || /council of clubs and organizations/i.test(opt.textContent || ''));
}

function loadCouncilPositions(leadershipTypeId, selectedPos=null) {
  resetDropdown(positionSelect, 'Loading positions...');
  if (!URL_COUNCIL_POS || !leadershipTypeId) { resetDropdown(positionSelect, 'Select Position'); return; }
  fetch(`${URL_COUNCIL_POS}?leadership_type_id=${encodeURIComponent(leadershipTypeId)}`)
    .then(r=>r.json())
    .then(rows=>{
      resetDropdown(positionSelect, 'Select Position');
      const list = Array.isArray(rows) ? rows : Object.entries(rows||{}).map(([id,name])=>({id,name}));
      setOptionsFromArray(positionSelect, list, selectedPos);
    })
    .catch(()=> resetDropdown(positionSelect, 'Select Position'));
}

leadershipTypeSelect?.addEventListener('change', () => {
  const typeId = leadershipTypeSelect.value;

  // reset all three
  resetDropdown(clusterSelect, 'Select Cluster');
  resetDropdown(organizationSelect, 'Select Organization');
  resetDropdown(positionSelect, 'Select Position');

  if (!typeId) {
    // hide CCO-only fields
    setVisible(clusterWrap, false);
    setVisible(orgWrap, false);
    setRequired(clusterSelect, false, clusterStar);
    setRequired(organizationSelect, false, orgStar);
    orgOptHint?.setAttribute('hidden','');
    return;
  }

  if (isCCOSelected()) {
    // CCO → show cluster & org, both required; positions depend on org
    setVisible(clusterWrap, true);
    setVisible(orgWrap, true);
    setRequired(clusterSelect, true, clusterStar);
    setRequired(organizationSelect, true, orgStar);
    orgOptHint?.removeAttribute('hidden');

    // load CCO clusters scoped by leadership_type_id
    fetch(`${URLS.clusters}?leadership_type_id=${encodeURIComponent(typeId)}`)
      .then(r=>r.json())
      .then(pairs=>{
        const rows = Array.isArray(pairs) ? pairs : Object.entries(pairs||{}).map(([id,name])=>({id,name}));
        setOptionsFromArray(clusterSelect, rows, oldCluster || '');
        if (oldCluster) clusterSelect.dispatchEvent(new Event('change'));
      })
      .catch(()=> resetDropdown(clusterSelect,'Select Cluster'));
  } else {
    // Non-CCO → hide cluster/org, not required. Positions come straight from type.
    setVisible(clusterWrap, false);
    setVisible(orgWrap, false);
    setRequired(clusterSelect, false, clusterStar);
    setRequired(organizationSelect, false, orgStar);
    orgOptHint?.setAttribute('hidden','');

    loadCouncilPositions(typeId, oldPosition || '');
  }
});

clusterSelect?.addEventListener('change', () => {
  const clusterId = clusterSelect.value;
  resetDropdown(organizationSelect, 'Loading organizations...');
  resetDropdown(positionSelect, 'Select Position');
  if (!clusterId) { resetDropdown(organizationSelect, 'Select Organization'); return; }
  fetch(`${URLS.organizations}?cluster_id=${encodeURIComponent(clusterId)}`)
    .then(r=>r.json())
    .then(pairs=>{
      const rows = Array.isArray(pairs) ? pairs : Object.entries(pairs||{}).map(([id,name])=>({id,name}));
      setOptionsFromArray(organizationSelect, rows, oldOrg || '');
      if (oldOrg) organizationSelect.dispatchEvent(new Event('change'));
    })
    .catch(()=> resetDropdown(organizationSelect,'Select Organization'));
});

organizationSelect?.addEventListener('change', () => {
  const orgId = organizationSelect.value;
  resetDropdown(positionSelect, 'Loading positions...');
  if (!orgId) { resetDropdown(positionSelect, 'Select Position'); return; }

  if (isCCOSelected()) {
    // positions for the chosen CCO org via council-positions endpoint (with type for safety)
    const typeId = leadershipTypeSelect.value;
    fetch(`${URL_COUNCIL_POS}?leadership_type_id=${encodeURIComponent(typeId)}&organization_id=${encodeURIComponent(orgId)}`)
      .then(r=>r.json())
      .then(rows=>{
        const list = Array.isArray(rows) ? rows : Object.entries(rows||{}).map(([id,name])=>({id,name}));
        setOptionsFromArray(positionSelect, list, oldPosition || '');
      })
      .catch(()=> resetDropdown(positionSelect,'Select Position'));
  } else {
    // Non-CCO handled earlier; keep fallback for safety
    loadPositions(orgId, oldPosition || '');
  }
});

// Restore old state on load
if (oldLeadershipType) {
  leadershipTypeSelect.value = oldLeadershipType;
  leadershipTypeSelect.dispatchEvent(new Event('change'));
}

    // -------- Multi-step --------
    pageNumbers.forEach((p, i) => p.addEventListener('click', () => { if (!validateStep()) return; currentStep = i; showStep(i); }));
    function showStep(n) {
      formSteps.forEach((s, i) => s.classList.toggle('active', i === n));
      pageNumbers.forEach((p, i) => {
        p.classList.remove('active', 'completed');
        if (i < n) p.classList.add('completed');
        if (i === n) p.classList.add('active');
      });
      prevBtn.disabled = n === 0;
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
    const checks = { length: /.{8,}/, uppercase: /[A-Z]/, lowercase: /[a-z]/, number: /[0-9]/, special: /[^A-Za-z0-9]/ };
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
    const body = document.body, toggleBtn = document.getElementById('darkModeToggle'), toggleBtn2 = document.getElementById('darkModeToggleFloating');
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
