<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}
require_once 'php/config.php';

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$email = $_SESSION['email'];

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch user goal
$stmt = $pdo->prepare('SELECT daily_goal FROM user_goals WHERE user_id = ?');
$stmt->execute([$user_id]);
$goal = $stmt->fetchColumn();

// Handle goal update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_goal'])) {
    $new_goal = (int)$_POST['daily_goal'];
    if ($new_goal >= 500 && $new_goal <= 5000) {
        $stmt = $pdo->prepare('UPDATE user_goals SET daily_goal = ? WHERE user_id = ?');
        $stmt->execute([$new_goal, $user_id]);
        header('Location: profile.php?success=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquaTrack - Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            padding: 40px;
        }
        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .profile-section {
            background: var(--light-gray);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .profile-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .btn-update {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
        }
        .success-message {
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-message i {
            font-size: 1.2em;
        }
        body.dark-mode {
            background: linear-gradient(135deg, #232526 0%, #414345 100%) !important;
            color: #f5f5f5;
        }
        .profile-container.dark-mode {
            background: #232526 !important;
            color: #f5f5f5;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .profile-section.dark-mode {
            background: #333842 !important;
            color: #f5f5f5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .profile-section.dark-mode h3 {
            color: #90caf9 !important;
        }
        .form-group.dark-mode input {
            background: #232526;
            color: #f5f5f5;
            border-color: #444;
        }
        .btn-darkmode {
            background: #232526;
            color: #90caf9;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        .btn-darkmode:hover {
            background: #333842;
        }
        .btn-darkmode.active {
            background: #90caf9;
            color: #232526;
        }
        .btn-update {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-update:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div>
                <h2><i class="fas fa-user-circle"></i> Profile</h2>
                <p>Manage your account settings</p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="dashboard.php" class="btn-login" style="width:auto; padding:10px 20px; background:var(--accent-color);">Dashboard</a>
                <button id="darkModeToggle" class="btn-darkmode" title="Toggle dark mode"><i class="fas fa-moon"></i></button>
                <a href="php/logout.php" class="btn-login" style="width:auto; padding:10px 20px;">Logout</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            Settings updated successfully!
        </div>
        <?php endif; ?>

        <div class="profile-section">
            <h3><i class="fas fa-user"></i> Personal Information</h3>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" value="<?php echo htmlspecialchars($fullname); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
            </div>
        </div>

        <div class="profile-section">
            <h3><i class="fas fa-tint"></i> Water Intake Settings</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="daily_goal">Daily Water Goal (ml)</label>
                    <input type="number" id="daily_goal" name="daily_goal" min="500" max="5000" step="100" 
                           value="<?php echo $goal; ?>" required>
                </div>
                <button type="submit" name="update_goal" class="btn-update">Update Goal</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dark mode toggle
        const darkModeBtn = document.getElementById('darkModeToggle');
        const body = document.body;
        const profileContainer = document.querySelector('.profile-container');
        const profileSections = document.querySelectorAll('.profile-section');
        const formGroups = document.querySelectorAll('.form-group');

        function setDarkMode(isDark) {
            body.classList.toggle('dark-mode', isDark);
            profileContainer.classList.toggle('dark-mode', isDark);
            profileSections.forEach(section => section.classList.toggle('dark-mode', isDark));
            formGroups.forEach(group => group.classList.toggle('dark-mode', isDark));
            if (darkModeBtn) {
                darkModeBtn.classList.toggle('active', isDark);
                darkModeBtn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            }
            localStorage.setItem('darkMode', isDark ? 'on' : 'off');
        }

        // Load saved preference
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode === 'on') {
            setDarkMode(true);
        }

        // Toggle dark mode on button click
        if (darkModeBtn) {
            darkModeBtn.addEventListener('click', function() {
                const isDark = !body.classList.contains('dark-mode');
                setDarkMode(isDark);
            });
        }
    });
    </script>
</body>
</html> 