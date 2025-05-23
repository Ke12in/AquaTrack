<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT reminder_time, is_active FROM reminders WHERE user_id = ?');
$stmt->execute([$user_id]);
echo json_encode($stmt->fetchAll()); 