<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}
require_once 'php/config.php';

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Fetch user goal
$stmt = $pdo->prepare('SELECT daily_goal FROM user_goals WHERE user_id = ?');
$stmt->execute([$user_id]);
$goal = $stmt->fetchColumn();

// Fetch today's water intake
$stmt = $pdo->prepare('SELECT SUM(amount) FROM water_logs WHERE user_id = ? AND CAST(logged_at AS DATE) = CAST(NOW() AS DATE)');
$stmt->execute([$user_id]);
$today_intake = $stmt->fetchColumn();
$today_intake = $today_intake ? $today_intake : 0;

// Calculate progress
$progress = $goal > 0 ? min(100, round(($today_intake / $goal) * 100)) : 0;

// Fetch streaks
$stmt = $pdo->prepare('SELECT current_streak, longest_streak FROM streaks WHERE user_id = ?');
$stmt->execute([$user_id]);
$streaks = $stmt->fetch();
$current_streak = $streaks['current_streak'] ?? 0;
$longest_streak = $streaks['longest_streak'] ?? 0;

// Motivational messages array
$messages = [
    "Stay hydrated, stay healthy!",
    "A glass of water a day keeps fatigue away!",
    "Your body is 60% water. Drink up!",
    "Small sips, big difference!",
    "Hydration fuels your focus!",
    "Every drop counts!",
    "Cheers to a healthier you!",
];
$motivation = $messages[array_rand($messages)];
$messages_json = json_encode($messages);

// Fetch last 7 days water intake
$history = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM water_logs WHERE user_id = ? AND CAST(logged_at AS DATE) = ?");
    $stmt->execute([$user_id, $date]);
    $total = $stmt->fetchColumn();
    $history[] = [
        'date' => $date,
        'total' => $total ? (int)$total : 0
    ];
}

$history_labels = [];
$history_values = [];
foreach ($history as $day) {
    $history_labels[] = date('D', strtotime($day['date']));
    $history_values[] = $day['total'];
}
$history_labels_json = json_encode($history_labels);
$history_values_json = json_encode($history_values);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AquaTrack - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body.dark-mode {
            background: linear-gradient(135deg, #232526 0%, #414345 100%) !important;
            color: #f5f5f5;
        }
        .dashboard-container.dark-mode {
            background: #232526 !important;
            color: #f5f5f5;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .dashboard-card.dark-mode {
            background: #333842 !important;
            color: #f5f5f5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .dashboard-card.dark-mode h3 {
            color: #90caf9 !important;
        }
        .progress-ring-text.dark-mode {
            color: #90caf9 !important;
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
        .reminder-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 15px;
            padding-right: 10px;
        }
        .reminder-list::-webkit-scrollbar {
            width: 6px;
        }
        .reminder-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .reminder-list::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }
        .reminder-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            margin-bottom: 8px;
            background: rgba(33, 150, 243, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .reminder-item:hover {
            background: rgba(33, 150, 243, 0.15);
        }
        .reminder-time {
            font-weight: 600;
            color: var(--primary-color);
        }
        .reminder-status {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .reminder-actions {
            display: flex;
            gap: 8px;
        }
        .reminder-actions a {
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }
        .reminder-actions a.edit {
            background: var(--primary-color);
            color: white;
        }
        .reminder-actions a.delete {
            background: var(--error-color);
            color: white;
        }
        .reminder-actions a:hover {
            opacity: 0.9;
        }
        .add-reminder-form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .add-reminder-form input[type="time"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            flex: 1;
        }
        .add-reminder-form button {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .add-reminder-form button:hover {
            opacity: 0.9;
        }
        .dark-mode .reminder-item {
            background: rgba(144, 202, 249, 0.1);
        }
        .dark-mode .reminder-item:hover {
            background: rgba(144, 202, 249, 0.15);
        }
        .dark-mode .reminder-time {
            color: #90caf9;
        }
        .dark-mode .add-reminder-form input[type="time"] {
            background: #232526;
            color: #f5f5f5;
            border-color: #444;
        }
        .dashboard-container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            padding: 40px 30px;
        }
        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .progress-ring {
            width: 140px;
            height: 140px;
            margin: 0 auto 20px auto;
            position: relative;
        }
        .progress-ring svg {
            transform: rotate(-90deg);
        }
        .progress-ring-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .dashboard-sections {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        .dashboard-card {
            flex: 1 1 250px;
            background: var(--light-gray);
            border-radius: 15px;
            padding: 25px 20px;
            box-shadow: 0 2px 8px rgba(33,150,243,0.05);
            min-width: 250px;
        }
        .dashboard-card h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        .streaks {
            display: flex;
            gap: 20px;
            font-size: 1.1rem;
        }
        .add-water-form {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .add-water-form input[type="number"] {
            width: 80px;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .add-water-form button {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            cursor: pointer;
            font-weight: 600;
        }
        @media (max-width: 900px) {
            .dashboard-sections {
                flex-direction: column;
            }
        }
        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .history-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }
        .history-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }
        .history-table tr:last-child td {
            border-bottom: none;
        }
        .history-table tr:hover td {
            background: rgba(33, 150, 243, 0.05);
        }
        .history-chart-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin: 20px 0;
        }
        .export-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .export-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .dark-mode .history-table th {
            background: #333842;
        }
        .dark-mode .history-table td {
            border-bottom-color: #444;
        }
        .dark-mode .history-table tr:hover td {
            background: rgba(144, 202, 249, 0.1);
        }
        .dark-mode .history-chart-container {
            background: #333842;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dark mode toggle
        const darkModeBtn = document.getElementById('darkModeToggle');
        const body = document.body;
        const dashboardContainer = document.querySelector('.dashboard-container');
        const dashboardCards = document.querySelectorAll('.dashboard-card');
        const progressRingTexts = document.querySelectorAll('.progress-ring-text');

        function setDarkMode(isDark) {
            body.classList.toggle('dark-mode', isDark);
            dashboardContainer.classList.toggle('dark-mode', isDark);
            dashboardCards.forEach(card => card.classList.toggle('dark-mode', isDark));
            progressRingTexts.forEach(el => el.classList.toggle('dark-mode', isDark));
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

        // Auto-changing tips
        const messages = <?php echo $messages_json; ?>;
        const motivationText = document.getElementById('motivation-text');
        const nextTipBtn = document.getElementById('nextTipBtn');
        let currentTipIndex = 0;

        function showNextTip() {
            currentTipIndex = (currentTipIndex + 1) % messages.length;
            motivationText.textContent = messages[currentTipIndex];
        }

        // Change tip every 10 seconds
        setInterval(showNextTip, 10000);

        // Manual tip change
        if (nextTipBtn) {
            nextTipBtn.addEventListener('click', showNextTip);
        }
    });
    </script>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($fullname); ?>! <i class="fas fa-tint"></i></h2>
                <p>Stay hydrated and track your progress!</p>
                <div style="margin-top:10px; color:var(--primary-color); font-weight:600; font-size:1.1em;">
                    <i class="fas fa-lightbulb"></i> <span id="motivation-text"><?php echo $motivation; ?></span>
                    <button id="nextTipBtn" style="margin-left:10px; background:var(--accent-color); color:#fff; border:none; border-radius:6px; padding:2px 10px; cursor:pointer; font-size:0.95em;">Next Tip</button>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="profile.php" class="btn-login" style="width:auto; padding:10px 20px; background:var(--accent-color);">Profile</a>
                <button id="darkModeToggle" class="btn-darkmode" title="Toggle dark mode"><i class="fas fa-moon"></i></button>
                <a href="php/logout.php" class="btn-login" style="width:auto; padding:10px 20px;">Logout</a>
            </div>
        </div>
        <div class="dashboard-sections">
            <!-- Progress Ring & Daily Goal -->
            <div class="dashboard-card" style="text-align:center;">
                <h3>Today's Progress</h3>
                <div class="progress-ring">
                    <svg width="140" height="140">
                        <circle cx="70" cy="70" r="60" stroke="#eee" stroke-width="15" fill="none" />
                        <circle cx="70" cy="70" r="60" stroke="var(--primary-color)" stroke-width="15" fill="none"
                            stroke-dasharray="377" stroke-dashoffset="<?php echo 377 - ($progress/100)*377; ?>" />
                    </svg>
                    <div class="progress-ring-text"><?php echo $progress; ?>%</div>
                </div>
                <div style="margin-bottom:10px;">
                    <strong><?php echo $today_intake; ?> ml</strong> / <?php echo $goal; ?> ml
                </div>
                <form class="add-water-form" action="php/add_water.php" method="POST">
                    <input type="number" name="amount" min="50" max="2000" step="50" placeholder="ml" required>
                    <button type="submit">Add</button>
                </form>
                <form action="php/update_goal.php" method="POST" style="margin-top:15px;">
                    <input type="number" name="daily_goal" min="500" max="10000" step="50" value="<?php echo $goal; ?>" required style="width:100px; padding:8px; border-radius:8px; border:1px solid #ccc;">
                    <button type="submit" style="background:var(--secondary-color); color:#fff; border:none; border-radius:8px; padding:8px 16px; cursor:pointer; font-weight:600;">Update Goal</button>
                </form>
            </div>
            <!-- Streaks -->
            <div class="dashboard-card">
                <h3>Streaks</h3>
                <div class="streaks">
                    <div><i class="fas fa-fire"></i> Current: <strong><?php echo $current_streak; ?></strong></div>
                    <div><i class="fas fa-crown"></i> Longest: <strong><?php echo $longest_streak; ?></strong></div>
                </div>
            </div>
            <!-- Reminders -->
            <div class="dashboard-card">
                <h3><i class="fas fa-bell"></i> Reminders</h3>
                <form class="add-reminder-form" action="php/add_reminder.php" method="POST">
                    <input type="time" name="reminder_time" required>
                    <button type="submit"><i class="fas fa-plus"></i> Add</button>
                </form>
                <div class="reminder-list">
                    <?php
                    $stmt = $pdo->prepare('SELECT id, reminder_time, is_active FROM reminders WHERE user_id = ? ORDER BY reminder_time');
                    $stmt->execute([$user_id]);
                    while ($reminder = $stmt->fetch()) {
                        echo '<div class="reminder-item">';
                        echo '<div class="reminder-time"><i class="far fa-clock"></i> ' . htmlspecialchars($reminder['reminder_time']) . '</div>';
                        echo '<div class="reminder-status">';
                        echo $reminder['is_active'] ? 
                            '<span style="color: #4CAF50;"><i class="fas fa-circle"></i> Active</span>' : 
                            '<span style="color: #f44336;"><i class="fas fa-circle"></i> Inactive</span>';
                        echo '</div>';
                        echo '<div class="reminder-actions">';
                        echo '<a href="php/edit_reminder.php?id=' . $reminder['id'] . '" class="edit"><i class="fas fa-edit"></i></a>';
                        echo '<a href="php/delete_reminder.php?id=' . $reminder['id'] . '" class="delete" onclick="return confirm(\'Delete this reminder?\')"><i class="fas fa-trash"></i></a>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            <!-- Water Intake History -->
            <div class="dashboard-card">
                <h3><i class="fas fa-chart-bar"></i> Last 7 Days</h3>
                <div style="width:100%;overflow-x:auto;">
                    <table class="history-table">
                        <tr>
                            <th>Date</th>
                            <th>Total (ml)</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($history as $day): 
                            $status = '';
                            $statusColor = '';
                            if ($day['total'] >= $goal) {
                                $status = 'Goal Achieved';
                                $statusColor = '#4CAF50';
                            } elseif ($day['total'] >= ($goal * 0.7)) {
                                $status = 'Good Progress';
                                $statusColor = '#2196F3';
                            } else {
                                $status = 'Needs Improvement';
                                $statusColor = '#FFA726';
                            }
                        ?>
                        <tr>
                            <td>
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('D, M j', strtotime($day['date'])); ?>
                            </td>
                            <td>
                                <i class="fas fa-tint"></i>
                                <?php echo $day['total']; ?> ml
                            </td>
                            <td style="color: <?php echo $statusColor; ?>">
                                <i class="fas fa-circle"></i>
                                <?php echo $status; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="history-chart-container">
                    <canvas id="historyChart" height="150"></canvas>
                </div>
                <form action="php/export_csv.php" method="POST">
                    <button type="submit" class="export-btn">
                        <i class="fas fa-download"></i>
                        Export Data (CSV)
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dark mode toggle
        const darkModeBtn = document.getElementById('darkModeToggle');
        const body = document.body;
        const dashboardContainer = document.querySelector('.dashboard-container');
        const dashboardCards = document.querySelectorAll('.dashboard-card');
        const progressRingTexts = document.querySelectorAll('.progress-ring-text');

        function setDarkMode(isDark) {
            body.classList.toggle('dark-mode', isDark);
            dashboardContainer.classList.toggle('dark-mode', isDark);
            dashboardCards.forEach(card => card.classList.toggle('dark-mode', isDark));
            progressRingTexts.forEach(el => el.classList.toggle('dark-mode', isDark));
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

        // Auto-changing tips
        const messages = <?php echo $messages_json; ?>;
        const motivationText = document.getElementById('motivation-text');
        const nextTipBtn = document.getElementById('nextTipBtn');
        let currentTipIndex = 0;

        function showNextTip() {
            currentTipIndex = (currentTipIndex + 1) % messages.length;
            motivationText.textContent = messages[currentTipIndex];
        }

        // Change tip every 10 seconds
        setInterval(showNextTip, 10000);

        // Manual tip change
        if (nextTipBtn) {
            nextTipBtn.addEventListener('click', showNextTip);
        }
    });
    // Chart.js for water intake history
    const ctx = document.getElementById('historyChart').getContext('2d');
    const historyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $history_labels_json; ?>,
            datasets: [{
                label: 'Water Intake (ml)',
                data: <?php echo $history_values_json; ?>,
                backgroundColor: 'rgba(33, 150, 243, 0.7)',
                borderColor: 'rgba(33, 150, 243, 1)',
                borderWidth: 2,
                borderRadius: 8,
                maxBarThickness: 32
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { 
                    display: false 
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        drawBorder: false
                    },
                    ticks: { 
                        color: '#1976d2',
                        font: { 
                            weight: 'bold'
                        },
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: { 
                        color: '#1976d2',
                        font: { 
                            weight: 'bold'
                        },
                        padding: 10
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
    </script>
</body>
</html> 