document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // Form validation
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;

            if (validateLoginForm(email, password)) {
                this.submit();
            }
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const fullname = this.querySelector('input[name="fullname"]').value;
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            const confirmPassword = this.querySelector('input[name="confirm_password"]').value;

            if (validateSignupForm(fullname, email, password, confirmPassword)) {
                this.submit();
            }
        });
    }

    // Input field animations
    const inputs = document.querySelectorAll('.input-group input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
});

// Validation functions
function validateLoginForm(email, password) {
    let isValid = true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
        showError('Please enter a valid email address');
        isValid = false;
    }

    if (password.length < 6) {
        showError('Password must be at least 6 characters long');
        isValid = false;
    }

    return isValid;
}

function validateSignupForm(fullname, email, password, confirmPassword) {
    let isValid = true;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (fullname.length < 2) {
        showError('Please enter your full name');
        isValid = false;
    }

    if (!emailRegex.test(email)) {
        showError('Please enter a valid email address');
        isValid = false;
    }

    if (password.length < 6) {
        showError('Password must be at least 6 characters long');
        isValid = false;
    }

    if (password !== confirmPassword) {
        showError('Passwords do not match');
        isValid = false;
    }

    return isValid;
}

// Error message display
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = 'var(--error-color)';
    errorDiv.style.fontSize = '14px';
    errorDiv.style.marginTop = '5px';
    errorDiv.style.animation = 'fadeIn 0.3s ease-out';

    const form = document.querySelector('form');
    const existingError = form.querySelector('.error-message');
    
    if (existingError) {
        existingError.remove();
    }
    
    form.insertBefore(errorDiv, form.firstChild);

    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}

// Browser notification for reminders
function checkReminders() {
    if (!('Notification' in window)) return;
    if (Notification.permission !== 'granted') return;
    fetch('php/get_reminders.php')
        .then(res => res.json())
        .then(reminders => {
            const now = new Date();
            const nowStr = now.toTimeString().slice(0,5);
            reminders.forEach(rem => {
                if (rem.is_active && rem.reminder_time === nowStr) {
                    new Notification('AquaTrack Reminder', {
                        body: 'Time to drink some water! 💧',
                        icon: '/favicon.ico'
                    });
                }
            });
        });
}
if ('Notification' in window) {
    if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
        Notification.requestPermission();
    }
    setInterval(checkReminders, 60000); // check every minute
} 