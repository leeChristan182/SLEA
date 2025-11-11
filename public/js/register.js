/* register.js — Complete Student Registration Logic */
document.addEventListener('DOMContentLoaded', function () {
    // ---------------------------
    // Form Elements
    // ---------------------------
    let currentStep = 0;

    const formSteps = document.querySelectorAll('.form-step');
    const pageNumbers = document.querySelectorAll('.page-number');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const form = document.querySelector('form');

    // Personal / Academic Inputs
    const studentIdInput = document.querySelector('input[name="student_id"]');
    const yearLevelSelect = document.querySelector('select[name="year_level"]');
    const expectedGradInput = document.querySelector('input[name="expected_grad"]');
    const birthDateInput = document.querySelector('input[name="birth_date"]');
    const ageInput = document.querySelector('input[name="age"]');

    const collegeSelect = document.querySelector('select[name="college_name"]');
    const programSelect = document.querySelector('select[name="program"]');
    const majorSelect = document.querySelector('select[name="major_name"]');

    // Leadership Inputs
    const leadershipTypeSelect = document.querySelector('select[name="leadership_type_id"]');
    const clusterSelect = document.querySelector('select[name="cluster_id"]');
    const orgSelect = document.querySelector('select[name="organization_id"]');
    const positionSelect = document.querySelector('select[name="position_id"]');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    expectedGradInput.readOnly = true;
    ageInput.readOnly = true;

    // ---------------------------
    // Auto-fill Age and Expected Graduation
    // ---------------------------
    function updateExpectedGrad() {
        const idValue = studentIdInput.value.trim();
        const idMatch = idValue.match(/^(\d{4})/);
        const entryYear = idMatch ? parseInt(idMatch[1], 10) : null;
        const currentYear = new Date().getFullYear();

        if (!entryYear) { expectedGradInput.value = ''; yearLevelSelect.value = ''; return; }

        const totalYears = 4;
        const levelNumber = Math.min(currentYear - entryYear + 1, totalYears);
        expectedGradInput.value = entryYear + totalYears;

        const levelMap = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year'};
        Array.from(yearLevelSelect.options).forEach(opt => {
            opt.selected = opt.text.trim() === (levelMap[levelNumber] || '');
        });
    }

    function updateAge() {
        if (!birthDateInput.value) { ageInput.value = ''; return; }
        const birthDate = new Date(birthDateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        if (today.getMonth() < birthDate.getMonth() ||
            (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) age--;
        ageInput.value = age;
    }

    studentIdInput?.addEventListener('input', updateExpectedGrad);
    yearLevelSelect?.addEventListener('change', updateExpectedGrad);
    birthDateInput?.addEventListener('change', updateAge);

    // ---------------------------
    // Helper Functions
    // ---------------------------
    function fetchOptions(url, callback) {
        fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken } })
            .then(res => res.json())
            .then(data => callback(data))
            .catch(() => callback([]));
    }

    function resetDropdown(dropdown, placeholder = 'Select') {
        dropdown.innerHTML = `<option value="">${placeholder}</option>`;
        dropdown.disabled = false;
    }

    // ---------------------------
    // College → Program → Major
    // ---------------------------
// ---------------------------
// College → Program → Major (using college_name)
// ---------------------------
let programsCache = {};

collegeSelect?.addEventListener('change', function () {
    const collegeName = this.options[this.selectedIndex]?.text?.trim();
    resetDropdown(programSelect, 'Loading programs...');
    resetDropdown(majorSelect, 'Select Major');
    programsCache = {};

    if (!collegeName) return;

    fetch(`/ajax/get-programs?college_name=${encodeURIComponent(collegeName)}`)
        .then(res => res.json())
        .then(data => {
            programsCache = data; // data is { "Program Name": ["Major1", "Major2"] }
            resetDropdown(programSelect, 'Select Program');

            Object.keys(data).forEach(p => {
                programSelect.innerHTML += `<option value="${p}">${p}</option>`;
            });

            // Restore old program selection if present
            if (programSelect.dataset.old) {
                programSelect.value = programSelect.dataset.old;
                programSelect.dispatchEvent(new Event('change'));
            }
        })
        .catch(err => {
            console.error(err);
            resetDropdown(programSelect, 'Error loading programs');
        });
});

programSelect?.addEventListener('change', function () {
    const selectedProgram = this.value;
    resetDropdown(majorSelect, 'Select Major');

    if (!selectedProgram || !programsCache[selectedProgram]) return;

    programsCache[selectedProgram].forEach(m => {
        majorSelect.innerHTML += `<option value="${m}">${m}</option>`;
    });

    // Restore old major selection if present
    if (majorSelect.dataset.old) majorSelect.value = majorSelect.dataset.old;
});

    // ---------------------------
    // Leadership Type → Cluster → Organization → Position
    // ---------------------------
    const oldLeadershipType = leadershipTypeSelect.dataset.old || leadershipTypeSelect.value;
    const oldCluster = clusterSelect.dataset.old || clusterSelect.value;
    const oldOrg = orgSelect.dataset.old || orgSelect.value;
    const oldPosition = positionSelect.dataset.old || positionSelect.value;

    function loadPositions(typeId, selected = null) {
        resetDropdown(positionSelect, 'Loading positions...');
        fetchOptions(`/ajax/get-positions?leadership_type_id=${typeId}`, positions => {
            resetDropdown(positionSelect, 'Select Position');
            positions.forEach(p => {
                const sel = p.id == selected ? 'selected' : '';
                positionSelect.innerHTML += `<option value="${p.id}" ${sel}>${p.name}</option>`;
            });
        });
    }

    function loadClusters(typeId, selectedCluster = null, selectedOrg = null) {
        resetDropdown(clusterSelect, 'Loading clusters...');
        resetDropdown(orgSelect, 'Select Organization');
        fetchOptions(`/ajax/get-clusters?leadership_type_id=${typeId}`, clusters => {
            resetDropdown(clusterSelect, 'Select Cluster');
            clusters.forEach(c => {
                const sel = c.id == selectedCluster ? 'selected' : '';
                clusterSelect.innerHTML += `<option value="${c.id}" ${sel}>${c.name}</option>`;
            });
            if (selectedCluster) loadOrganizations(selectedCluster, selectedOrg);
        });
    }

    function loadOrganizations(clusterId, selected = null) {
        resetDropdown(orgSelect, 'Loading organizations...');
        fetchOptions(`/ajax/get-organizations?cluster_id=${clusterId}`, orgs => {
            resetDropdown(orgSelect, 'Select Organization');
            orgs.forEach(o => {
                const sel = o.id == selected ? 'selected' : '';
                orgSelect.innerHTML += `<option value="${o.id}" ${sel}>${o.name}</option>`;
            });
        });
    }

    leadershipTypeSelect?.addEventListener('change', () => {
        const typeId = leadershipTypeSelect.value;
        const typeName = leadershipTypeSelect.options[leadershipTypeSelect.selectedIndex]?.text.trim();

        if (['University Student Goverment(USG)','Obrero Student Council(OSC)','Local Council (LC)','Local Government Unit (LGU)'].includes(typeName)) {
            resetDropdown(clusterSelect, 'N/A');
            resetDropdown(orgSelect, 'N/A');
        } else {
            loadClusters(typeId);
        }
        loadPositions(typeId);
    });

    clusterSelect?.addEventListener('change', () => {
        const clusterId = clusterSelect.value;
        if (clusterId) loadOrganizations(clusterId);
        else resetDropdown(orgSelect, 'Select Organization');
    });

    // Restore old leadership selections
    if (oldLeadershipType) {
        leadershipTypeSelect.value = oldLeadershipType;
        const typeName = leadershipTypeSelect.options[leadershipTypeSelect.selectedIndex]?.text.trim();
        if (!['University Student Goverment(USG)','Obrero Student Council(OSC)','Local Council (LC)','Local Government Unit (LGU)'].includes(typeName)) {
            loadClusters(oldLeadershipType, oldCluster, oldOrg);
        }
        loadPositions(oldLeadershipType, oldPosition);
    }

    // ---------------------------
    // Multi-step Navigation
    // ---------------------------
    pageNumbers.forEach((page, i) => {
        page.addEventListener('click', () => { if (!validateStep()) return false; currentStep=i; showStep(currentStep); });
    });

    function showStep(n) {
        formSteps.forEach((s,i) => s.classList.toggle('active', i===n));
        pageNumbers.forEach((p,i) => { p.classList.remove('active','completed'); if(i<n) p.classList.add('completed'); if(i===n) p.classList.add('active'); });
        prevBtn.disabled = n===0;
        nextBtn.textContent = n===formSteps.length-1 ? 'Submit' : 'Next';
    }

    window.nextPrev = function(n) {
        if(n===1 && !validateStep()) return false;
        currentStep += n;
        if(currentStep>=formSteps.length){ form.submit(); return false; }
        showStep(currentStep);
    }

    function validateStep() {
        let valid=true;
        const inputs=formSteps[currentStep].querySelectorAll('input,select');
        inputs.forEach(i=> { if(!i.checkValidity()){ i.classList.add('is-invalid'); valid=false } else i.classList.remove('is-invalid'); });
        return valid;
    }

    showStep(currentStep);

    // ---------------------------
    // Password Requirements Live Check
    // ---------------------------
    const passwordInput = document.getElementById('password');
    const checks = {
        length: /.{8,}/,
        uppercase: /[A-Z]/,
        lowercase: /[a-z]/,
        number: /[0-9]/,
        special: /[^A-Za-z0-9]/
    };

    passwordInput?.addEventListener('input', () => {
        Object.keys(checks).forEach(key => {
            const el = document.getElementById(key);
            if(checks[key].test(passwordInput.value)){
                el.classList.replace('text-danger','text-success');
                el.querySelector('i').classList.replace('fa-circle-xmark','fa-circle-check');
            } else {
                el.classList.replace('text-success','text-danger');
                el.querySelector('i').classList.replace('fa-circle-check','fa-circle-xmark');
            }
        });
    });

    // ---------------------------
    // Dark Mode
    // ---------------------------
    const body = document.body;
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleBtnFloating = document.getElementById('darkModeToggleFloating');
    const headerContainer = document.querySelector('.header-container');
    const registerContainer = document.querySelector('.register-container');

    function applyTheme(mode) {
        const isDark = mode==='dark';
        body.classList.toggle('dark-mode', isDark);
        headerContainer?.classList.toggle('dark-mode', isDark);
        registerContainer?.classList.toggle('dark-mode', isDark);

        const icon = toggleBtn?.querySelector('i');
        const iconFloating = toggleBtnFloating?.querySelector('i');

        if(isDark){ icon?.classList.replace('fa-moon','fa-sun'); iconFloating?.classList.replace('fa-moon','fa-sun'); }
        else { icon?.classList.replace('fa-sun','fa-moon'); iconFloating?.classList.replace('fa-sun','fa-moon'); }

        localStorage.setItem('theme', mode);
    }

    applyTheme(localStorage.getItem('theme') || 'light');
    toggleBtn?.addEventListener('click', () => applyTheme(body.classList.contains('dark-mode')?'light':'dark'));
    toggleBtnFloating?.addEventListener('click', () => applyTheme(body.classList.contains('dark-mode')?'light':'dark'));
});
