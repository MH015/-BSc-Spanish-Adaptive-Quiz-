<?php
/**

 * LANGUAGE SELECTION PAGE (Prototype - Future Feature)

 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 * Module: 6COM2018

 * PURPOSE:
 * This page demonstrates a potential future enhancement where
 * users could choose from multiple languages beyond Spanish.
 * Currently implemented as a visual prototype to showcase the
 * application's extensibility and planned feature roadmap.
 * FUTURE DEVELOPMENT:
 * - Each language would link to its own question bank
 * - User preferences stored in the database
 * - Progress tracked independently per language

 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // In future: check if language already selected for session
}

// Include database connection
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Require user to be logged in
require_login();

// Get current user data
$user = get_logged_in_user();

/**
 * LANGUAGE CONFIGURATION
 * ----------------------
 * Each language entry contains:
 * - code:        ISO 639-1 language code
 * - name:        Display name in English
 * - native_name: Name in the target language
 * - flag:        Emoji flag for visual identification
 * - status:      'active' = fully implemented, 'coming_soon' = prototype placeholder
 * - questions:   Number of questions available (0 for coming soon)
 * - description: Brief description of the course content
 * FUTURE: This array would be replaced by a database query
 * against a 'languages' table, allowing dynamic language additions
 * without code changes.
 */
$languages = [
    [
        'code'        => 'es',
        'name'        => 'Spanish',
        'native_name' => 'Español',
        'flag'        => '🇪🇸',
        'status'      => 'active',
        'questions'   => 96,
        'description' => 'Learn everyday Spanish vocabulary across greetings, food, travel, and common phrases.'
    ],
    [
        'code'        => 'fr',
        'name'        => 'French',
        'native_name' => 'Français',
        'flag'        => '🇫🇷',
        'status'      => 'coming_soon',
        'questions'   => 0,
        'description' => 'Master French essentials from basic greetings to conversational phrases.'
    ],
    [
        'code'        => 'de',
        'name'        => 'German',
        'native_name' => 'Deutsch',
        'flag'        => '🇩🇪',
        'status'      => 'coming_soon',
        'questions'   => 0,
        'description' => 'Build your German vocabulary with structured adaptive learning.'
    ],
    [
        'code'        => 'it',
        'name'        => 'Italian',
        'native_name' => 'Italiano',
        'flag'        => '🇮🇹',
        'status'      => 'coming_soon',
        'questions'   => 0,
        'description' => 'Explore Italian through themed categories and adaptive difficulty.'
    ],
    [
        'code'        => 'pt',
        'name'        => 'Portuguese',
        'native_name' => 'Português',
        'flag'        => '🇧🇷',
        'status'      => 'coming_soon',
        'questions'   => 0,
        'description' => 'Learn Brazilian Portuguese with real-world vocabulary and phrases.'
    ],
    [
        'code'        => 'ja',
        'name'        => 'Japanese',
        'native_name' => '日本語',
        'flag'        => '🇯🇵',
        'status'      => 'coming_soon',
        'questions'   => 0,
        'description' => 'Start your Japanese journey from hiragana to everyday expressions.'
    ]
];

/**
 * HANDLE LANGUAGE SELECTION
 * -------------------------
 * When a user clicks an active language card, the selected
 * language code is stored in the session. In a full implementation,
 * this would also be persisted to the database via an UPDATE query
 * on the users table (e.g., preferred_language column).
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_language'])) {
    $selected = $_POST['language_code'] ?? 'es';
    $_SESSION['selected_language'] = $selected;
    
    // Future: UPDATE users SET preferred_language = ? WHERE user_id = ?
    
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Language - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /**
         * LANGUAGE SELECTION STYLES
         * -------------------------
         * These styles extend the existing QuizNinja theme to create
         * a visually distinct language selection experience. The card
         * layout uses CSS Grid for responsive arrangement across
         * different screen sizes.
         */

        /* Page header with gradient accent */
        .language-header {
            text-align: center;
            padding: 2rem 0 1rem;
        }

        .language-header h1 {
            font-size: 2rem;
            color: var(--text-primary, #1a1a2e);
            margin-bottom: 0.5rem;
        }

        .language-header p {
            color: var(--text-muted, #6b7280);
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Prototype banner - informs users this is a future feature */
        .prototype-banner {
            background: linear-gradient(135deg, var(--primary-color, #0d7377) 0%, var(--accent-color, #d4a843) 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-lg, 12px);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .prototype-banner .badge-proto {
            background: rgba(255, 255, 255, 0.25);
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        /* Responsive grid layout for language cards */
        .language-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        /* Individual language card styling */
        .language-card {
            background: var(--white, #ffffff);
            border-radius: var(--radius-xl, 16px);
            padding: 1.75rem;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        /* Active language card (Spanish) - selectable */
        .language-card.active:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg, 0 10px 25px rgba(0,0,0,0.15));
            border-color: var(--primary-color, var(--primary-color, #0d7377));
        }

        /* Coming soon cards - greyed out with overlay */
        .language-card.coming-soon {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .language-card.coming-soon:hover {
            transform: none;
        }

        /* Coming soon ribbon overlay */
        .coming-soon-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--gray-200, #e5e7eb);
            color: var(--text-muted, #6b7280);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Flag emoji display */
        .language-flag {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        /* Language name in English */
        .language-name {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-primary, #1a1a2e);
            margin-bottom: 0.15rem;
        }

        /* Language name in its native script */
        .language-native {
            font-size: 1rem;
            color: var(--primary-color, var(--primary-color, #0d7377));
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        /* Course description text */
        .language-desc {
            font-size: 0.9rem;
            color: var(--text-muted, #6b7280);
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        /* Question count indicator */
        .language-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted, #6b7280);
        }

        .language-meta .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success-color, var(--success-color, #2e7d32));
        }

        .language-meta .dot.inactive {
            background: var(--gray-300, #d1d5db);
        }

        /* Currently selected indicator */
        .current-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color, #0d7377), var(--accent-color, #d4a843));
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        /* Future features info section */
        .future-info {
            background: var(--white, #ffffff);
            border-radius: var(--radius-xl, 16px);
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));
        }

        .future-info h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--text-primary, #1a1a2e);
        }

        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--gray-50, #f9fafb);
            border-radius: var(--radius-lg, 12px);
        }

        .feature-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .feature-text h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary, #1a1a2e);
            margin-bottom: 0.2rem;
        }

        .feature-text p {
            font-size: 0.8rem;
            color: var(--text-muted, #6b7280);
            line-height: 1.4;
        }

        /* ── Prototype Detail Box ── */
        .prototype-detail-box {
            background: var(--white, #ffffff);
            border-radius: var(--radius-xl, 16px);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));
            border-left: 5px solid var(--primary-color, #0d7377);
        }

        .prototype-detail-box h3 {
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
            color: var(--text-primary, #1a1a2e);
        }

        .prototype-detail-box > p {
            color: var(--text-muted, #6b7280);
            font-size: 0.92rem;
            line-height: 1.65;
            margin-bottom: 1.5rem;
        }

        .proto-columns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .proto-col h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-primary, #1a1a2e);
        }

        .proto-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .proto-list-item {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted, #6b7280);
            line-height: 1.45;
        }

        .proto-check {
            color: var(--success-color, #2e7d32);
            font-weight: 700;
            flex-shrink: 0;
        }

        .proto-arrow {
            color: var(--primary-color, #0d7377);
            font-weight: 700;
            flex-shrink: 0;
        }

        .proto-list-item code {
            background: rgba(13, 115, 119, 0.08);
            color: var(--primary-color, #0d7377);
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .proto-schema {
            background: var(--gray-50, #f9fafb);
            border-radius: var(--radius-lg, 12px);
            padding: 1.25rem;
            margin-top: 0.5rem;
        }

        .proto-schema h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
            color: var(--text-primary, #1a1a2e);
        }

        .proto-schema-desc {
            font-size: 0.83rem;
            color: var(--text-muted, #6b7280);
            margin-bottom: 0.75rem;
        }

        .proto-code {
            background: #1e1e2e;
            color: #cdd6f4;
            padding: 1rem 1.25rem;
            border-radius: 8px;
            font-size: 0.78rem;
            line-height: 1.55;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            white-space: pre;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">

        <nav class="navbar">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">QuizNinja</a>
                <ul class="navbar-nav">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="language_select.php" class="active">Languages</a></li>
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

                <!-- Page Header -->
                <div class="language-header">
                    <h1>Choose Your Language</h1>
                    <p>Select a language to begin your adaptive learning journey with QuizNinja</p>
                </div>

                <!-- Prototype Indicator Banner -->
                <div class="prototype-banner">
                    <span class="badge-proto">PROTOTYPE</span>
                    This page demonstrates planned multi-language support. Currently, Spanish is fully implemented with 96 questions across 4 categories.
                </div>

                <div class="prototype-detail-box">
                    <h3>About This Prototype</h3>
                    <p>
                        This page is a <strong>functional prototype</strong> demonstrating how QuizNinja's architecture 
                        could scale to support multiple languages. It is intentionally included to showcase the 
                        application's extensibility as part of the project evaluation. The current implementation 
                        focuses on Spanish as a proof of concept, while the interface below illustrates how 
                        additional languages would be presented to users in a future release.
                    </p>

                    <div class="proto-columns">
                        <div class="proto-col">
                            <h4>What This Prototype Demonstrates</h4>
                            <div class="proto-list">
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Responsive language selection UI with card-based layout</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Active vs coming soon visual states for each language</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Session-based language preference storage via <code>$_SESSION['selected_language']</code></span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Form-based selection that redirects to the dashboard</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Consistent navigation and styling with existing QuizNinja pages</span>
                                </div>
                            </div>
                        </div>

                        <div class="proto-col">
                            <h4>What a Production Version Would Require</h4>
                            <div class="proto-list">
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>A <code>languages</code> database table to store available languages dynamically</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>A <code>preferred_language</code> column in the <code>users</code> table for persistence</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>Separate question banks per language (e.g. 96+ French questions)</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>Independent progress tracking and adaptive difficulty per language</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>Content moderation and native speaker review for each language</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="proto-schema">
                        <h4>Proposed Database Schema Extension</h4>
                        <p class="proto-schema-desc">The following schema changes would be required to support multi-language functionality in a production release:</p>
                        <pre class="proto-code">
-- New table: stores available languages
CREATE TABLE languages (
    language_id   INT AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(5) NOT NULL,      -- ISO 639-1 code (e.g. 'es', 'fr')
    name          VARCHAR(50) NOT NULL,     -- English name
    native_name   VARCHAR(50) NOT NULL,     -- Name in target language
    status        ENUM('active','coming_soon') DEFAULT 'coming_soon',
    question_count INT DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Modified: add language reference to questions table
ALTER TABLE questions
    ADD COLUMN language_id INT DEFAULT 1,
    ADD FOREIGN KEY (language_id) REFERENCES languages(language_id);

-- Modified: add language preference to users table
ALTER TABLE users
    ADD COLUMN preferred_language INT DEFAULT 1,
    ADD FOREIGN KEY (preferred_language) REFERENCES languages(language_id);</pre>
                    </div>
                </div>

                <div class="language-grid">
                    <?php foreach ($languages as $lang): ?>
                        <?php if ($lang['status'] === 'active'): ?>
                            <!-- Active Language Card (clickable form) -->
                            <form method="POST" action="language_select.php">
                                <input type="hidden" name="select_language" value="1">
                                <input type="hidden" name="language_code" value="<?php echo $lang['code']; ?>">
                                <button type="submit" class="language-card active" style="width:100%; text-align:left; border:2px solid var(--primary-color, var(--primary-color, #0d7377));">
                                    <span class="language-flag"><?php echo $lang['flag']; ?></span>
                                    <div class="language-name"><?php echo htmlspecialchars($lang['name']); ?></div>
                                    <div class="language-native"><?php echo htmlspecialchars($lang['native_name']); ?></div>
                                    <div class="language-desc"><?php echo htmlspecialchars($lang['description']); ?></div>
                                    <div class="language-meta">
                                        <span class="dot"></span>
                                        <?php echo $lang['questions']; ?> questions available
                                    </div>
                                    <span class="current-badge">✓ Available Now</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Coming Soon Language Card (disabled) -->
                            <div class="language-card coming-soon">
                                <span class="coming-soon-badge">Coming Soon</span>
                                <span class="language-flag"><?php echo $lang['flag']; ?></span>
                                <div class="language-name"><?php echo htmlspecialchars($lang['name']); ?></div>
                                <div class="language-native"><?php echo htmlspecialchars($lang['native_name']); ?></div>
                                <div class="language-desc"><?php echo htmlspecialchars($lang['description']); ?></div>
                                <div class="language-meta">
                                    <span class="dot inactive"></span>
                                    In development
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="future-info">
                    <h3>Planned Language Learning Features</h3>
                    <div class="feature-list">
                        <div class="feature-item">
                            <span class="feature-icon" style="font-size:1.2rem; color: var(--primary-color, #0d7377); font-weight: 700;">01</span>
                            <div class="feature-text">
                                <h4>Audio Pronunciation</h4>
                                <p>Hear native speaker audio for every question and answer to improve listening skills.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" style="font-size:1.2rem; color: var(--primary-color, #0d7377); font-weight: 700;">02</span>
                            <div class="feature-text">
                                <h4>Speech Recognition</h4>
                                <p>Practise speaking with real-time pronunciation feedback using the Web Speech API.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" style="font-size:1.2rem; color: var(--primary-color, #0d7377); font-weight: 700;">03</span>
                            <div class="feature-text">
                                <h4>Cross-Language Progress</h4>
                                <p>Track your progress across multiple languages with comparative analytics.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" style="font-size:1.2rem; color: var(--primary-color, #0d7377); font-weight: 700;">04</span>
                            <div class="feature-text">
                                <h4>Leaderboards</h4>
                                <p>Compete with other learners per language with weekly and all-time rankings.</p>
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
