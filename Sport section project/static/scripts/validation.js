// Progressive-enhancement client-side form validation.
// Server-side checks in register.php / login.php / profile_edit.php stay authoritative;
// this only gives instant feedback and reuses the same .form-error / .is-invalid classes
// the server already renders.
document.addEventListener('DOMContentLoaded', () => {
    const required = label => value => (value.trim() === '' ? `${label} is required.` : null);
    const email = value => (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim()) ? null : 'Please enter a valid email address.');
    const minLength = (n, label) => value => (value !== '' && value.length < n ? `${label} must be at least ${n} characters.` : null);
    const matches = (otherName, message) => (value, form) => {
        const other = form.querySelector(`[name="${otherName}"]`);
        return other && value !== other.value ? message : null;
    };

    function setFieldError(input, message) {
        input.classList.toggle('is-invalid', Boolean(message));
        const group = input.closest('.form-group');
        if (!group) return;

        let error = group.querySelector('.form-error');
        if (message) {
            if (!error) {
                error = document.createElement('span');
                error.className = 'form-error';
                group.appendChild(error);
            }
            error.textContent = message;
        } else if (error) {
            error.remove();
        }
    }

    function attachValidation(formId, fieldRules) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', event => {
            let isValid = true;

            fieldRules.forEach(([name, rules]) => {
                const input = form.querySelector(`[name="${name}"]`);
                if (!input) return;

                let message = null;
                for (const rule of rules) {
                    message = rule(input.value, form);
                    if (message) break;
                }

                setFieldError(input, message);
                if (message) isValid = false;
            });

            if (!isValid) event.preventDefault();
        });

        // Clear a field's error as soon as the visitor starts fixing it
        form.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) setFieldError(input, null);
            });
        });
    }

    attachValidation('register-form', [
        ['first_name', [required('First name')]],
        ['last_name', [required('Last name')]],
        ['email', [required('Email'), email]],
        ['password', [required('Password'), minLength(8, 'Password')]],
        ['password_confirm', [required('Password confirmation'), matches('password', 'Passwords do not match.')]],
    ]);

    attachValidation('login-form', [
        ['email', [required('Email'), email]],
        ['password', [required('Password')]],
    ]);

    attachValidation('profile-edit-form', [
        ['name', [required('Name')]],
        ['email', [required('Email'), email]],
        ['new_password', [minLength(8, 'New password')]],
        ['new_password_confirm', [matches('new_password', 'New passwords do not match.')]],
    ]);
});
