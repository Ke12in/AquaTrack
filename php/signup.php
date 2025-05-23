<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Debug information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));
}

if (isset($_SESSION['success'])) {
    echo "<div style='color:green; text-align:center; margin:10px 0;'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $error) {
        echo "<div style='color:red; text-align:center; margin:10px 0;'>$error</div>";
    }
    unset($_SESSION['errors']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Debug information
    error_log("Processing signup for: " . $email);

    // Validation
    $errors = [];

    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
        }
    } catch (PDOException $e) {
        error_log("Database error checking email: " . $e->getMessage());
        $errors[] = "Database error occurred";
    }

    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$fullname, $email, $hashed_password]);

            // Create user's water tracking data
            $user_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO user_goals (user_id, daily_goal, created_at) VALUES (?, 2000, NOW())");
            $stmt->execute([$user_id]);

            error_log("User registered successfully: " . $email);
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: ../index.php");
            exit();
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "Registration failed. Please try again.";
        }
    }

    if (!empty($errors)) {
        error_log("Registration errors: " . print_r($errors, true));
        $_SESSION['errors'] = $errors;
        header("Location: ../signup.html");
        exit();
    }
}
header("Location: ../index.php");
exit();
?> 