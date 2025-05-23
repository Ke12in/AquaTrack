<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daily_goal'])) {
    $goal = (int)$_POST['daily_goal'];
    if ($goal >= 500 && $goal <= 10000) {
        $stmt = $pdo->prepare('UPDATE user_goals SET daily_goal = ? WHERE user_id = ?');
        $stmt->execute([$goal, $user_id]);
    }
}
header('Location: ../dashboard.php');
exit(); 