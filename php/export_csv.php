<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}
$user_id = $_SESSION['user_id'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="water_logs.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Amount (ml)']);

$stmt = $pdo->prepare('SELECT logged_at, amount FROM water_logs WHERE user_id = ? ORDER BY logged_at');
$stmt->execute([$user_id]);
while ($row = $stmt->fetch()) {
    fputcsv($output, [date('Y-m-d H:i', strtotime($row['logged_at'])), $row['amount']]);
}
fclose($output);
exit(); 