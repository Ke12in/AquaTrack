<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}
$user_id = $_SESSION['user_id'];
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM reminders WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
}
header('Location: ../dashboard.php');
exit(); 