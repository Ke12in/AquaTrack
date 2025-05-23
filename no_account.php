<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Account Found - AquaTrack</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .no-account-container {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            padding: 40px 30px;
            text-align: center;
        }
        .no-account-container h2 {
            color: var(--primary-color);
            margin-bottom: 18px;
        }
        .no-account-container p {
            color: #555;
            margin-bottom: 24px;
        }
        .no-account-container a, .no-account-container button {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = 'signup.html';
        }, 5000);
    </script>
</head>
<body>
    <div class="no-account-container">
        <h2><i class="fas fa-user-slash"></i> No Account Found</h2>
        <p>
            We couldn't find an account with those credentials.<br>
            Would you like to sign up for a new account?
        </p>
        <a href="signup.html">Sign Up</a>
        <p style="margin-top:18px;font-size:0.95em;color:#888;">You will be redirected to the sign up page in a few seconds...</p>
    </div>
</body>
</html> 