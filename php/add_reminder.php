<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}

$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reminder_time'])) {
    $reminder_time = $_POST['reminder_time'];
    if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $reminder_time)) {
        $stmt = $pdo->prepare('INSERT INTO reminders (user_id, reminder_time, is_active, created_at) VALUES (?, ?, 1, NOW())');
        $stmt->execute([$user_id, $reminder_time]);
    }
}
header('Location: ../dashboard.php');
exit(); 