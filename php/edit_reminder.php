<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['reminder_time'])) {
    $id = (int)$_POST['id'];
    $reminder_time = $_POST['reminder_time'];
    if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $reminder_time)) {
        $stmt = $pdo->prepare('UPDATE reminders SET reminder_time = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$reminder_time, $id, $user_id]);
    }
    header('Location: ../dashboard.php');
    exit();
}
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT reminder_time FROM reminders WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
    $reminder = $stmt->fetch();
    if ($reminder) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Edit Reminder</title>
            <link rel="stylesheet" href="../css/style.css">
        </head>
        <body>
            <div class="container" style="max-width:400px;margin:60px auto;background:#fff;padding:30px;border-radius:15px;box-shadow:0 2px 8px rgba(33,150,243,0.08);">
                <h2 style="color:var(--primary-color);margin-bottom:20px;">Edit Reminder</h2>
                <form action="edit_reminder.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="time" name="reminder_time" value="<?php echo htmlspecialchars($reminder['reminder_time']); ?>" required style="padding:10px;width:70%;margin-bottom:15px;">
                    <button type="submit" class="btn-login" style="width:auto;">Update</button>
                    <a href="../dashboard.php" class="btn-login" style="width:auto;background:var(--error-color);margin-left:10px;">Cancel</a>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}
header('Location: ../dashboard.php');
exit(); 