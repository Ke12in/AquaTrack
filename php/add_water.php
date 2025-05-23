<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}

$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = (int)$_POST['amount'];
    if ($amount > 0) {
        // Log water intake
        $stmt = $pdo->prepare('INSERT INTO water_logs (user_id, amount, logged_at) VALUES (?, ?, NOW())');
        $stmt->execute([$user_id, $amount]);

        // Check if daily goal is met
        $stmt = $pdo->prepare('SELECT daily_goal FROM user_goals WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $goal = $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT SUM(amount) FROM water_logs WHERE user_id = ? AND CAST(logged_at AS DATE) = CAST(NOW() AS DATE)');
        $stmt->execute([$user_id]);
        $today_intake = $stmt->fetchColumn();

        // Update streaks if goal met
        if ($today_intake >= $goal) {
            // Get current streak info
            $stmt = $pdo->prepare('SELECT current_streak, longest_streak, last_updated FROM streaks WHERE user_id = ?');
            $stmt->execute([$user_id]);
            $streak = $stmt->fetch();
            $today = date('Y-m-d');
            $last_updated = $streak['last_updated'] ? date('Y-m-d', strtotime($streak['last_updated'])) : null;

            if ($last_updated !== $today) {
                $current_streak = $streak['current_streak'] + 1;
                $longest_streak = max($current_streak, $streak['longest_streak']);
                $stmt = $pdo->prepare('UPDATE streaks SET current_streak = ?, longest_streak = ?, last_updated = NOW() WHERE user_id = ?');
                $stmt->execute([$current_streak, $longest_streak, $user_id]);
            }
        }
    }
}
header('Location: ../dashboard.php');
exit(); 