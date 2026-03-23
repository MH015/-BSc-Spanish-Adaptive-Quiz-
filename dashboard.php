<?php
/**
 * 
 * DASHBOARD PAGE
 * 
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 * 
 */

require_once 'includes/auth.php';
require_login();

$user = get_logged_in_user();
$stats = get_user_stats($user['user_id']);

// Get recent attempts
$recent_attempts = db_query(
    "SELECT * FROM quiz_attempts WHERE user_id = ? ORDER BY attempt_date DESC LIMIT 5",
    [$user['user_id']]
);

// Get question counts by category
$categories = [
    ['name' => 'Vocabulary', 'icon' => '📖', 'description' => 'Learn Spanish words'],
    ['name' => 'Grammar', 'icon' => '✏️', 'description' => 'Master language rules'],
    ['name' => 'Verbs', 'icon' => '🔄', 'description' => 'Practice conjugations'],
    ['name' => 'Phrases', 'icon' => '💬', 'description' => 'Common expressions']
];

foreach ($categories as &$cat) {
    $count = db_query_single(
        "SELECT COUNT(*) as total FROM questions WHERE category = ?",
        [$cat['name']]
    );
    $cat['count'] = $count ? $count['total'] : 0;
}
unset($cat);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <nav class="navbar">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">QuizNinja</a>
                <ul class="navbar-nav">
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="language_select.php">Languages</a></li>
                    <li><a href="pronunciation.php">Pronunciation</a></li>
                    <li><a href="progress.php">Progress</a></li>
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
                <div class="dashboard-header">
                    <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>! 👋</h1>
                    <p>Ready to continue your Spanish learning journey?</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value" style="text-transform: capitalize;">
                            <?php echo $stats['current_level']; ?>
                        </div>
                        <div class="stat-label">Current Level</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-value"><?php echo $stats['total_quizzes']; ?></div>
                        <div class="stat-label">Quizzes Completed</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-value">
                            <?php echo $stats['recent_score'] !== null ? $stats['recent_score'] . '%' : '-'; ?>
                        </div>
                        <div class="stat-label">Last Score</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['average_score']; ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                </div>
                
                <!-- Category Selection -->
                <h2>Choose a Category</h2>
                <div class="category-grid">
                    <?php foreach ($categories as $cat): ?>
                        <a href="quiz.php?category=<?php echo urlencode($cat['name']); ?>" class="category-card">
                            <div class="category-icon"><?php echo $cat['icon']; ?></div>
                            <div class="category-name"><?php echo $cat['name']; ?></div>
                            <div class="category-count"><?php echo $cat['count']; ?> questions</div>
                        </a>
                    <?php endforeach; ?>
                    
                    <a href="quiz.php?new=1" class="category-card">
                        <div class="category-icon">🎲</div>
                        <div class="category-name">Mixed Quiz</div>
                        <div class="category-count">All categories</div>
                    </a>
                </div>
                
                <!-- Recent Attempts -->
                <?php if (!empty($recent_attempts)): ?>
                <div class="mt-6">
                    <h2>Recent Activity</h2>
                    <div class="card">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Difficulty</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_attempts as $attempt): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($attempt['attempt_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($attempt['category']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $attempt['difficulty_level']; ?>">
                                            <?php echo ucfirst($attempt['difficulty_level']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo $attempt['percentage']; ?>%</strong>
                                        <span class="text-muted">(<?php echo $attempt['score']; ?>/<?php echo $attempt['total_questions']; ?>)</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================================
                     FUTURE FEATURES SHOWCASE SECTION (Prototype)
                     ============================================================ -->
                <style>
                    .future-section { margin-top: 2.5rem; }
                    .future-section h2 { font-size: 1.3rem; color: var(--text-primary, #1a1a2e); margin-bottom: 0.35rem; }
                    .future-section .subtitle { color: var(--text-muted, #6b7280); font-size: 0.9rem; margin-bottom: 1.25rem; }
                    .future-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; }
                    .future-card {
                        background: var(--white, #ffffff); border-radius: var(--radius-xl, 16px); padding: 1.75rem;
                        box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1)); text-decoration: none; color: inherit;
                        transition: all 0.3s ease; border: 2px solid transparent; position: relative; overflow: hidden;
                    }
                    .future-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg, 0 10px 25px rgba(0,0,0,0.15)); border-color: var(--primary-color, var(--primary-color, #0d7377)); text-decoration: none; }
                    .future-card .card-icon { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; }
                    .future-card h3 { font-size: 1.15rem; font-weight: 700; color: var(--text-primary, #1a1a2e); margin-bottom: 0.4rem; }
                    .future-card p { font-size: 0.9rem; color: var(--text-muted, #6b7280); line-height: 1.5; margin-bottom: 0.75rem; }
                    .future-card .proto-label {
                        display: inline-block; background: linear-gradient(135deg, var(--primary-color, #0d7377), var(--accent-color, #d4a843)); color: white;
                        padding: 0.2rem 0.65rem; border-radius: 999px; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px;
                    }
                    .future-card .feature-highlights { margin-top: 0.75rem; display: flex; flex-direction: column; gap: 0.35rem; }
                    .future-card .feature-highlights span { font-size: 0.8rem; color: var(--text-muted, #6b7280); }
                    .future-card::before {
                        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
                        background: linear-gradient(90deg, var(--primary-color, #0d7377), var(--accent-color, #d4a843), var(--accent-light, #fdf6e3));
                    }
                </style>

                <div class="future-section">
                    <h2>🚀 Coming Soon</h2>
                    <p class="subtitle">Explore prototype features planned for future versions of QuizNinja</p>
                    
                    <div class="future-grid">
                        <a href="language_select.php" class="future-card">
                            <span class="card-icon">🌍</span>
                            <h3>Multi-Language Support</h3>
                            <p>Choose from multiple languages beyond Spanish. Learn French, German, Italian, and more with the same adaptive system.</p>
                            <span class="proto-label">PROTOTYPE</span>
                            <div class="feature-highlights">
                                <span>🇪🇸 Spanish (Active) • 🇫🇷 French • 🇩🇪 German</span>
                                <span>🇮🇹 Italian • 🇧🇷 Portuguese • 🇯🇵 Japanese</span>
                            </div>
                        </a>

                        <a href="pronunciation.php" class="future-card">
                            <span class="card-icon">🔊</span>
                            <h3>Audio Pronunciation</h3>
                            <p>Listen to native pronunciation, practise speaking with your microphone, and get instant feedback on your accent.</p>
                            <span class="proto-label">PROTOTYPE</span>
                            <div class="feature-highlights">
                                <span>🔊 Text-to-Speech Playback</span>
                                <span>🎙️ Speech Recognition Input</span>
                                <span>📊 Pronunciation Accuracy Scoring</span>
                            </div>
                        </a>

                        <div class="future-card" style="cursor: default; opacity: 0.65;">
                            <span class="card-icon">🧠</span>
                            <h3>Spaced Repetition</h3>
                            <p>Smart review scheduling that presents questions at optimal intervals to maximise long-term memory retention.</p>
                            <span class="proto-label" style="background: var(--gray-400, #9ca3af);">PLANNED</span>
                            <div class="feature-highlights">
                                <span>⏰ Optimised Review Intervals</span>
                                <span>📈 Memory Strength Tracking</span>
                                <span>🎯 Personalised Study Sessions</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
        
        <footer class="footer">
            <div class="container">
                <p>&copy; 2025 QuizNinja - Adaptive Language Learning | 6COM2018 Final Year Project</p>
            </div>
        </footer>
    </div>
</body>
</html>
