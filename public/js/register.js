document.addEventListener('DOMContentLoaded', function () {
    let currentStep = 0;

    const formSteps = document.querySelectorAll('.form-step');
    const pageNumbers = document.querySelectorAll('.page-number');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const form = document.querySelector('form');

    const studentIdInput = document.querySelector('input[name="student_id"]');
    const yearLevelSelect = document.querySelector('select[name="year_level"]');
    const expectedGradInput = document.querySelector('input[name="expected_grad"]');
    const birthDateInput = document.querySelector('input[name="birth_date"]');
    const ageInput = document.querySelector('input[name="age"]');

    expectedGradInput.readOnly = true;
    ageInput.readOnly = true;

    function updateExpectedGrad() {
        const idValue = studentIdInput?.value?.trim();
        const yearLevelValue = yearLevelSelect?.value?.trim();
        const idMatch = idValue.match(/^(\\d{4})[-\\s]?/);
        const enrollmentYear = idMatch ? parseInt(idMatch[1], 10) : null;

        if (!enrollmentYear || !yearLevelValue) {
            expectedGradInput.value = '';
            return;
        }

        let addYears = { '1st Year': 4, '2nd Year': 3, '3rd Year': 2, '4th Year': 1 }[yearLevelValue] || 0;
        expectedGradInput.value = enrollmentYear + addYears;
    }

    function updateAge() {
        if (!birthDateInput?.value) {
            ageInput.value = '';
            return;
        }
        const birthDate = new Date(birthDateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        if (today.getMonth() < birthDate.getMonth() ||
            (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
            age--;
        }
        ageInput.value = age;
    }

    studentIdInput?.addEventListener('input', updateExpectedGrad);
    yearLevelSelect?.addEventListener('change', updateExpectedGrad);
    birthDateInput?.addEventListener('change', updateAge);

    pageNumbers.forEach((page, index) => {
        page.addEventListener('click', function () {
            if (!validateStep()) return false;
            currentStep = index;
            showStep(currentStep);
            window.scrollTo(0, 0);
        });
    });

    function showStep(n) {
        formSteps.forEach((step, index) => {
            step.classList.toggle('active', index === n);
        });

        pageNumbers.forEach((page, index) => {
            page.classList.remove('active', 'completed');
            if (index < n) page.classList.add('completed');
            if (index === n) page.classList.add('active');
        });

        prevBtn.disabled = n === 0;
        prevBtn.classList.toggle('disabled', n === 0);
        nextBtn.textContent = (n === formSteps.length - 1) ? 'Submit' : 'Next';
    }

    window.nextPrev = function (n) {
        if (n === 1 && !validateStep()) return false;
        currentStep += n;

        if (currentStep >= formSteps.length) {
            form.submit();
            return false;
        }

        showStep(currentStep);
        window.scrollTo(0, 0);
    };

    function validateStep() {
        const inputs = formSteps[currentStep].querySelectorAll('input, select');
        let valid = true;
        inputs.forEach(input => {
            if (!input.checkValidity()) {
                input.classList.add('is-invalid');
                valid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        return valid;
    }

    showStep(currentStep);
});

const body = document.body;
const toggleBtn = document.getElementById('darkModeToggle');
const toggleBtnFloating = document.getElementById('darkModeToggleFloating');
const headerContainer = document.querySelector('.header-container');
const registerContainer = document.querySelector('.register-container');

function applyTheme(mode) {
    const isDark = mode === 'dark';

    body.classList.toggle('dark-mode', isDark);
    headerContainer?.classList.toggle('dark-mode', isDark);
    registerContainer?.classList.toggle('dark-mode', isDark);

    const icon = toggleBtn?.querySelector('i');
    const iconFloating = toggleBtnFloating?.querySelector('i');

    if (isDark) {
        icon?.classList.replace('fa-moon', 'fa-sun');
        iconFloating?.classList.replace('fa-moon', 'fa-sun');
    } else {
        icon?.classList.replace('fa-sun', 'fa-moon');
        iconFloating?.classList.replace('fa-sun', 'fa-moon');
    }

    localStorage.setItem('theme', mode);
}

// On load
const savedTheme = localStorage.getItem('theme') || 'light';
applyTheme(savedTheme);

// Toggle handler
function toggleTheme() {
    const newTheme = body.classList.contains('dark-mode') ? 'light' : 'dark';
    applyTheme(newTheme);
}

toggleBtn?.addEventListener('click', toggleTheme);
toggleBtnFloating?.addEventListener('click', toggleTheme);
