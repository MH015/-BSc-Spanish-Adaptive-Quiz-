<?php
/**

 * PROGRESS PAGE

 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 * PURPOSE:
 * Provides a visual overview of the user's learning history using
 * Chart.js. Allows learners to identify patterns, track improvement,
 * and review past performance across categories and time.

 */

require_once 'includes/auth.php';
require_login();

$user = get_logged_in_user();
$stats = get_user_stats($user['user_id']);

$allAttempts = db_query(
    "SELECT * FROM quiz_attempts WHERE user_id = ? ORDER BY attempt_date ASC",
    [$user['user_id']]
);

$recentAttempts = db_query(
    "SELECT * FROM quiz_attempts WHERE user_id = ? ORDER BY attempt_date DESC LIMIT 10",
    [$user['user_id']]
);

$categoryStats = db_query(
    "SELECT category, 
            COUNT(*) as attempts, 
            ROUND(AVG(percentage), 1) as avg_score,
            MAX(percentage) as best_score
     FROM quiz_attempts 
     WHERE user_id = ? 
     GROUP BY category 
     ORDER BY avg_score DESC",
    [$user['user_id']]
);

$chartLabels = [];
$chartScores = [];
$chartColors = [];

foreach ($allAttempts as $attempt) {
    $chartLabels[] = date('M j', strtotime($attempt['attempt_date']));
    $chartScores[] = $attempt['percentage'];
    
    if ($attempt['percentage'] >= 75) {
        $chartColors[] = '#2e7d32';
    } elseif ($attempt['percentage'] >= 50) {
        $chartColors[] = '#d4a843';
    } else {
        $chartColors[] = '#c62828';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="page-wrapper">
        <nav class="navbar">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">QuizNinja</a>
                <ul class="navbar-nav">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="language_select.php">Languages</a></li>
                    <li><a href="pronunciation.php">Pronunciation</a></li>
                    <li><a href="progress.php" class="active">Progress</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    <li>
                        <div class="user-avatar" title="<?php echo htmlspecialchars($user['username']); ?>">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        
        <main class="main-content">
            <div class="container">
                <div class="progress-header">
                    <h1>Your Learning Progress</h1>
                    <p>Track your Spanish learning journey</p>
                </div>
                
                <?php if ($stats['total_quizzes'] > 0): ?>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_quizzes']; ?></div>
                        <div class="stat-label">Quizzes Completed</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-value"><?php echo $stats['average_score']; ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-value" style="text-transform: capitalize;"><?php echo $stats['current_level']; ?></div>
                        <div class="stat-label">Current Level</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['recent_score'] !== null ? $stats['recent_score'] . '%' : '-'; ?></div>
                        <div class="stat-label">Last Score</div>
                    </div>
                </div>
                
                <!-- Score History Chart -->
                <div class="card mb-6">
                    <h2>Score History</h2>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="scoreChart"></canvas>
                    </div>
                </div>
                
                <!-- Category Performance -->
                <?php if (!empty($categoryStats)): ?>
                <div class="card mb-6">
                    <h2>Performance by Category</h2>
                    <div class="category-stats">
                        <?php foreach ($categoryStats as $cat): ?>
                        <div class="category-card" style="cursor: default;">
                            <div class="category-name"><?php echo htmlspecialchars($cat['category']); ?></div>
                            <div class="category-count"><?php echo $cat['attempts']; ?> quizzes taken</div>
                            <div style="margin-top: 0.5rem;">
                                <strong>Avg: <?php echo $cat['avg_score']; ?>%</strong>
                                <span class="text-muted"> | Best: <?php echo $cat['best_score']; ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Attempts Table -->
                <?php if (!empty($recentAttempts)): ?>
                <div class="card">
                    <h2>Recent Quiz Attempts</h2>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Difficulty</th>
                                <th>Score</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAttempts as $attempt): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($attempt['attempt_date'])); ?></td>
                                <td><?php echo htmlspecialchars($attempt['category']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $attempt['difficulty_level']; ?>">
                                        <?php echo ucfirst($attempt['difficulty_level']); ?>
                                    </span>
                                </td>
                                <td><?php echo $attempt['score']; ?>/<?php echo $attempt['total_questions']; ?></td>
                                <td>
                                    <strong class="<?php echo $attempt['percentage'] >= 75 ? 'text-success' : ($attempt['percentage'] >= 50 ? 'text-warning' : 'text-error'); ?>">
                                        <?php echo $attempt['percentage']; ?>%
                                    </strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <!-- No quizzes yet -->
                <div class="card" style="text-align: center; padding: 3rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">-</div>
                    <h2>No Quizzes Yet</h2>
                    <p class="text-muted" style="margin-bottom: 1.5rem;">Complete your first quiz to start tracking your progress!</p>
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
                <?php endif; ?>
                
            </div>
        </main>
        
        <footer class="footer">
            <div class="container">
                <p>&copy; 2025 QuizNinja - Adaptive Language Learning | 6COM2018 Final Year Project</p>
            </div>
        </footer>
    </div>
    
    <?php if ($stats['total_quizzes'] > 0): ?>
    <script>
        const ctx = document.getElementById('scoreChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Quiz Score (%)',
                    data: <?php echo json_encode($chartScores); ?>,
                    backgroundColor: <?php echo json_encode($chartColors); ?>,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: value => value + '%' }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
