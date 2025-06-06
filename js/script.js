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