// Login functionality
async function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('/api/auth', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'login',
                email,
                password
            })
        });

        const data = await response.json();

        if (response.ok) {
            // Store user data in localStorage (excluding sensitive info)
            localStorage.setItem('user', JSON.stringify({
                id: data._id,
                fullname: data.fullname,
                email: data.email,
                height: data.height,
                weight: data.weight,
                activityLevel: data.activityLevel,
                soundNotifications: data.soundNotifications,
                browserNotifications: data.browserNotifications,
                dailyReminders: data.dailyReminders,
                theme: data.theme,
                profileImage: data.profileImage
            }));

            // Redirect to dashboard
            window.location.href = 'dashboard.html';
        } else {
            showNotification(data.error || 'Login failed', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showNotification('An error occurred during login', 'error');
    }
}

// Signup functionality
async function handleSignup(event) {
    event.preventDefault();
    
    const fullname = document.getElementById('fullname').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('/api/auth', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'signup',
                fullname,
                email,
                password
            })
        });

        const data = await response.json();

        if (response.ok) {
            showNotification('Account created successfully! Please login.', 'success');
            // Redirect to login page after a short delay
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        } else {
            showNotification(data.error || 'Signup failed', 'error');
        }
    } catch (error) {
        console.error('Signup error:', error);
        showNotification('An error occurred during signup', 'error');
    }
}

// Add event listeners
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
}); 