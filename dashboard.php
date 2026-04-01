<?php
/**
 * DASHBOARD PAGE
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 */

require_once 'includes/auth.php';
require_login();

$user = get_logged_in_user();
$stats = get_user_stats($user['user_id']);

$recent_attempts = db_query(
    "SELECT * FROM quiz_attempts WHERE user_id = ? ORDER BY attempt_date DESC LIMIT 5",
    [$user['user_id']]
);

$categories = [
    ['name' => 'Vocabulary', 'icon' => 'V', 'description' => 'Learn Spanish words'],
    ['name' => 'Grammar', 'icon' => 'G', 'description' => 'Master language rules'],
    ['name' => 'Verbs', 'icon' => 'B', 'description' => 'Practice conjugations'],
    ['name' => 'Phrases', 'icon' => 'P', 'description' => 'Common expressions']
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
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, #111111 0%, #1a1a1a 60%, #111111 100%);
            border-radius: 8px;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .welcome-banner::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d4a843, transparent);
        }
        .welcome-banner h1 {
            color: #fff;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.75rem;
            margin-bottom: 0.35rem;
        }
        .welcome-banner p {
            color: rgba(255, 255, 255, 0.75);
            margin: 0;
            font-size: 1rem;
        }
        .welcome-banner .level-pill {
            display: inline-block;
            background: rgba(212, 168, 67, 0.2);
            border: 1px solid rgba(212, 168, 67, 0.4);
            color: #d4a843;
            padding: 0.3rem 0.85rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 0.75rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            background: #222222;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #333333;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
        }
        .stat-card:nth-child(1)::before { background: #d4a843; }
        .stat-card:nth-child(2)::before { background: #d4a843; }
        .stat-card:nth-child(3)::before { background: #d4a843; }
        .stat-card:nth-child(4)::before { background: #d4a843; }
        .stat-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255,255,255,0.4);
            margin-bottom: 0.5rem;
        }
        .stat-value {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .section-header h2 {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.25rem;
            color: #ffffff;
            margin: 0;
        }
        .section-header .section-line {
            flex: 1;
            height: 1px;
            background: #333333;
            margin-left: 1rem;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2.5rem;
        }
        .category-card {
            background: #222222;
            border: 2px solid #333333;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .category-card:hover {
            border-color: #d4a843;
            box-shadow: 0 4px 15px rgba(212, 168, 67, 0.12);
            transform: translateY(-2px);
            text-decoration: none;
            color: inherit;
        }
        .category-card .cat-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #d4a843, #c49a38);
            color: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 auto 1rem;
        }
        .category-card .cat-name {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.05rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }
        .category-card .cat-count {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
        }
        .category-card .cat-desc {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.3);
            margin-top: 0.5rem;
        }
        .category-card.mixed-card .cat-icon {
            background: linear-gradient(135deg, #d4a843, #c49a38);
        }

        .activity-card {
            background: #222222;
            border-radius: 8px;
            border: 1px solid #333333;
            overflow: hidden;
            margin-bottom: 2.5rem;
        }
        .activity-card .history-table {
            margin: 0;
            border: none;
            box-shadow: none;
        }

        .future-section { margin-top: 1rem; }
        .future-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }
        .future-card {
            background: #222222;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #333333;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .future-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-color: #d4a843;
            text-decoration: none;
            color: inherit;
        }
        .future-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d4a843, #d4a843);
        }
        .future-card h3 {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.05rem;
            color: #ffffff;
            margin-bottom: 0.4rem;
        }
        .future-card p {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.4);
            line-height: 1.5;
            margin-bottom: 0.75rem;
        }
        .future-card .proto-label {
            display: inline-block;
            background: #d4a843;
            color: #fff;
            padding: 0.2rem 0.6rem;
            border-radius: 3px;
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .future-card .feature-highlights {
            margin-top: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        .future-card .feature-highlights span {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.3);
        }
        .future-card.planned {
            opacity: 0.6;
            cursor: default;
        }
        .future-card.planned .proto-label {
            background: rgba(255,255,255,0.3);
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .category-grid { grid-template-columns: 1fr; }
            .welcome-banner { padding: 1.5rem; }
            .welcome-banner h1 { font-size: 1.35rem; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
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
                <div class="welcome-banner">
                    <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?></h1>
                    <p>Ready to continue your Spanish learning journey?</p>
                    <span class="level-pill">Current Level: <?php echo ucfirst($stats['current_level']); ?></span>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Quizzes Completed</div>
                        <div class="stat-value"><?php echo $stats['total_quizzes']; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Average Score</div>
                        <div class="stat-value"><?php echo $stats['average_score']; ?>%</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Last Score</div>
                        <div class="stat-value"><?php echo $stats['recent_score'] !== null ? $stats['recent_score'] . '%' : '-'; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Current Level</div>
                        <div class="stat-value" style="text-transform: capitalize;"><?php echo $stats['current_level']; ?></div>
                    </div>
                </div>
                
                <div class="section-header">
                    <h2>Choose a Category</h2>
                    <span class="section-line"></span>
                </div>
                <div class="category-grid">
                    <?php foreach ($categories as $cat): ?>
                        <a href="quiz.php?category=<?php echo urlencode($cat['name']); ?>" class="category-card">
                            <div class="cat-icon"><?php echo $cat['icon']; ?></div>
                            <div class="cat-name"><?php echo $cat['name']; ?></div>
                            <div class="cat-count"><?php echo $cat['count']; ?> questions</div>
                            <div class="cat-desc"><?php echo $cat['description']; ?></div>
                        </a>
                    <?php endforeach; ?>
                    <a href="quiz.php?new=1" class="category-card mixed-card">
                        <div class="cat-icon">M</div>
                        <div class="cat-name">Mixed Quiz</div>
                        <div class="cat-count">All categories</div>
                        <div class="cat-desc">Random questions from all topics</div>
                    </a>
                </div>
                
                <?php if (!empty($recent_attempts)): ?>
                <div class="section-header">
                    <h2>Recent Activity</h2>
                    <span class="section-line"></span>
                </div>
                <div class="activity-card">
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
                <?php endif; ?>

                <div class="future-section">
                    <div class="section-header">
                        <h2>Coming Soon</h2>
                        <span class="section-line"></span>
                    </div>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.4); margin-bottom: 1rem;">Explore prototype features planned for future versions of QuizNinja</p>
                    <div class="future-grid">
                        <a href="language_select.php" class="future-card">
                            <h3>Multi-Language Support</h3>
                            <p>Choose from multiple languages beyond Spanish. Learn French, German, Italian, and more with the same adaptive system.</p>
                            <span class="proto-label">Prototype</span>
                            <div class="feature-highlights">
                                <span>Spanish (Active) &middot; French &middot; German</span>
                                <span>Italian &middot; Portuguese &middot; Japanese</span>
                            </div>
                        </a>
                        <a href="pronunciation.php" class="future-card">
                            <h3>Audio Pronunciation</h3>
                            <p>Listen to native pronunciation, practise speaking with your microphone, and get instant feedback on your accent.</p>
                            <span class="proto-label">Prototype</span>
                            <div class="feature-highlights">
                                <span>Text-to-Speech Playback</span>
                                <span>Speech Recognition Input</span>
                                <span>Pronunciation Accuracy Scoring</span>
                            </div>
                        </a>
                        <div class="future-card planned">
                            <h3>Spaced Repetition</h3>
                            <p>Smart review scheduling that presents questions at optimal intervals to maximise long-term memory retention.</p>
                            <span class="proto-label">Planned</span>
                            <div class="feature-highlights">
                                <span>Optimised Review Intervals</span>
                                <span>Memory Strength Tracking</span>
                                <span>Personalised Study Sessions</span>
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
