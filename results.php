<?php
/**

 * RESULTS PAGE

 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 *  PURPOSE:
 * Processes the completed quiz, calculates the score, runs the
 * adaptive algorithm to adjust difficulty, saves all data to the
 * database, and displays detailed feedback to the learner. This
 * is where the adaptive algorithm is triggered.
 * 
 * HOW IT WORKS:
 * 1. Retrieves quiz questions and user answers from $_SESSION.
 * 2. Grades each question by comparing selected_answer to
 *    correct_answer, building a $questionResults array.
 * 3. Calculates the percentage: ($correctCount / $totalQuestions) * 100
 * 4. Saves the quiz attempt to the quiz_attempts table (user_id,
 *    category, difficulty_level, score, percentage, attempt_date).
 * 5. Saves each individual answer to the user_answers table
 *    (attempt_id, question_id, selected_answer, is_correct).
 * 6. Calls calculate_adaptive_difficulty() from adaptive_algorithm.php
 *    passing user_id, category, percentage, and current difficulty.
 * 7. Receives the algorithm's decision (increase/maintain/decrease),
 *    the composite score, all four factor values, and feedback text.
 * 8. Displays:
 *    - Score and emoji feedback banner
 *    - Adaptive Analysis card with the four factor breakdowns,
 *      their weights, and the gold composite score
 *    - Difficulty adjustment message (what changed and why)
 *    - Question-by-question review (green = correct, red = incorrect
 *      with the correct answer shown)
 * 9. Clears quiz session data to prevent re-submission.
 */

require_once 'includes/auth.php';
require_login();

$user = get_logged_in_user();

if (!isset($_SESSION['quiz_questions']) || !isset($_SESSION['quiz_answers'])) {
    header('Location: dashboard.php?error=No quiz data found. Please start a new quiz.');
    exit;
}

$questions = $_SESSION['quiz_questions'];
$answers = $_SESSION['quiz_answers'];
$quizCategory = $_SESSION['quiz_category'];
$quizDifficulty = $_SESSION['quiz_difficulty'];
$startTime = $_SESSION['quiz_start_time'] ?? time();

$totalQuestions = count($questions);
$correctCount = 0;
$questionResults = [];

foreach ($questions as $index => $question) {
    $userAnswer = isset($answers[$index]) ? $answers[$index] : null;
    $isCorrect = ($userAnswer === $question['correct_answer']);
    if ($isCorrect) $correctCount++;
    
    $questionResults[] = [
        'question' => $question,
        'user_answer' => $userAnswer,
        'correct_answer' => $question['correct_answer'],
        'is_correct' => $isCorrect
    ];
}

$percentage = round(($correctCount / $totalQuestions) * 100);
$timeTaken = time() - $startTime;
$minutes = floor($timeTaken / 60);
$seconds = $timeTaken % 60;
$timeDisplay = sprintf('%d:%02d', $minutes, $seconds);

// Include the enhanced adaptive algorithm
require_once 'includes/adaptive_algorithm.php';

// Multi-factor adaptive difficulty adjustment
$oldLevel = $quizDifficulty;

// Save quiz attempt FIRST (so the algorithm can include this attempt in trend analysis)
if (!isset($_SESSION['results_saved'])) {
    db_execute(
        "INSERT INTO quiz_attempts (user_id, category, difficulty_level, score, total_questions, percentage, attempt_date) 
         VALUES (?, ?, ?, ?, ?, ?, NOW())",
        [$user['user_id'], $quizCategory, $quizDifficulty, $correctCount, $totalQuestions, $percentage]
    );
    
    $attemptId = $pdo->lastInsertId();
    
    // Save individual answers
    foreach ($questionResults as $result) {
        $sql = "INSERT INTO user_answers (attempt_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)";
        db_execute($sql, [
            $attemptId,
            $result['question']['question_id'],
            $result['user_answer'] ?? '',
            $result['is_correct'] ? 1 : 0
        ]);
    }

    // Run the enhanced multi-factor adaptive algorithm
    $adaptiveResult = calculate_adaptive_difficulty(
        $user['user_id'],
        $quizCategory,
        $percentage,
        $quizDifficulty
    );

    $newLevel = $adaptiveResult['new_level'];
    $feedbackType = $adaptiveResult['feedback_type'];
    $feedbackTitle = $adaptiveResult['feedback_title'];
    $feedbackMessage = $adaptiveResult['feedback_message'];
    $feedbackDetail = $adaptiveResult['feedback_detail'];
    $adaptiveFactors = $adaptiveResult['factors'];
    $compositeScore = $adaptiveResult['composite_score'];

    // Update global user level
    db_execute(
        "UPDATE users SET current_level = ? WHERE user_id = ?",
        [$newLevel, $user['user_id']]
    );

    $_SESSION['current_level'] = $newLevel;
    
    // Store for display
    $_SESSION['adaptive_result'] = $adaptiveResult;
    $_SESSION['results_saved'] = true;
} else {
    // Results already saved, retrieve from session
    $adaptiveResult = $_SESSION['adaptive_result'] ?? null;
    $newLevel = $_SESSION['current_level'] ?? $quizDifficulty;
    $feedbackType = $adaptiveResult['feedback_type'] ?? 'maintain';
    $feedbackTitle = $adaptiveResult['feedback_title'] ?? 'Results';
    $feedbackMessage = $adaptiveResult['feedback_message'] ?? '';
    $feedbackDetail = $adaptiveResult['feedback_detail'] ?? '';
    $adaptiveFactors = $adaptiveResult['factors'] ?? [];
    $compositeScore = $adaptiveResult['composite_score'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
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
                <div class="results-container">
                    
                    <div class="results-card">
                        <?php
                        if ($percentage >= 90) $emoji = '🏆';
                        elseif ($percentage >= 75) $emoji = '🎉';
                        elseif ($percentage >= 50) $emoji = '👍';
                        else $emoji = '💪';
                        ?>
                        <div class="results-emoji"><?php echo $emoji; ?></div>
                        <div class="results-score"><?php echo $percentage; ?>%</div>
                        <p class="results-message">
                            You got <strong><?php echo $correctCount; ?></strong> out of 
                            <strong><?php echo $totalQuestions; ?></strong> questions correct!
                        </p>
                        <p class="text-muted">Time: <?php echo $timeDisplay; ?></p>
                    </div>
                    
                    <div class="adaptive-feedback <?php echo $feedbackType; ?>">
                        <h3><?php echo $feedbackTitle; ?></h3>
                        <p><?php echo $feedbackMessage; ?></p>
                    </div>

                    <?php if (!empty($adaptiveFactors)): ?>
                    <div class="card mb-6">
                        <h3 style="margin-bottom: 0.75rem;">Adaptive Analysis</h3>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;"><?php echo $feedbackDetail ?? ''; ?></p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem;">
                            <div style="background: var(--bg-color, #1a1a1a); border: 1px solid var(--gray-200, #333); border-radius: 6px; padding: 0.85rem; text-align: center;">
                                <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); margin-bottom: 0.35rem;">Quiz Score</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: <?php echo ($adaptiveFactors['score'] ?? 0) >= 0 ? '#6abf6e' : '#ef6b6b'; ?>;"><?php echo ($adaptiveFactors['score'] ?? 0) >= 0 ? '+' : ''; ?><?php echo $adaptiveFactors['score'] ?? 0; ?></div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.2rem;">Weight: 50%</div>
                            </div>
                            <div style="background: var(--bg-color, #1a1a1a); border: 1px solid var(--gray-200, #333); border-radius: 6px; padding: 0.85rem; text-align: center;">
                                <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); margin-bottom: 0.35rem;">Trend</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: <?php echo ($adaptiveFactors['trend'] ?? 0) >= 0 ? '#6abf6e' : '#ef6b6b'; ?>;"><?php echo ($adaptiveFactors['trend'] ?? 0) >= 0 ? '+' : ''; ?><?php echo $adaptiveFactors['trend'] ?? 0; ?></div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.2rem;">Weight: 25%</div>
                            </div>
                            <div style="background: var(--bg-color, #1a1a1a); border: 1px solid var(--gray-200, #333); border-radius: 6px; padding: 0.85rem; text-align: center;">
                                <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); margin-bottom: 0.35rem;">Consistency</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: <?php echo ($adaptiveFactors['consistency'] ?? 0) >= 0 ? '#6abf6e' : '#ef6b6b'; ?>;"><?php echo ($adaptiveFactors['consistency'] ?? 0) >= 0 ? '+' : ''; ?><?php echo $adaptiveFactors['consistency'] ?? 0; ?></div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.2rem;">Weight: 15%</div>
                            </div>
                            <div style="background: var(--bg-color, #1a1a1a); border: 1px solid var(--gray-200, #333); border-radius: 6px; padding: 0.85rem; text-align: center;">
                                <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); margin-bottom: 0.35rem;">Category</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: <?php echo ($adaptiveFactors['category'] ?? 0) >= 0 ? '#6abf6e' : '#ef6b6b'; ?>;"><?php echo ($adaptiveFactors['category'] ?? 0) >= 0 ? '+' : ''; ?><?php echo $adaptiveFactors['category'] ?? 0; ?></div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.2rem;">Weight: 10%</div>
                            </div>
                        </div>
                        <div style="text-align: center; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--gray-200, #333);">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Composite Score: </span>
                            <span style="font-size: 0.95rem; font-weight: 700; color: #d4a843;"><?php echo $compositeScore ?? 0; ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Question Review -->
                    <div class="card mb-6">
                        <h3 class="mb-4">Review Your Answers</h3>
                        <?php foreach ($questionResults as $index => $result): ?>
                            <?php
                            $statusClass = $result['is_correct'] ? 'correct' : 'incorrect';
                            $correctKey = 'option_' . $result['correct_answer'];
                            $correctText = $result['question'][$correctKey];
                            ?>
                            <div class="answer-option <?php echo $statusClass; ?>" style="margin-bottom: var(--spacing-3); cursor: default;">
                                <span class="answer-letter"><?php echo $result['is_correct'] ? '✓' : '✗'; ?></span>
                                <div class="answer-text">
                                    <strong>Q<?php echo $index + 1; ?>:</strong> 
                                    <?php echo htmlspecialchars($result['question']['question_text']); ?>
                                    <br>
                                    <small class="text-muted">
                                        Correct answer: <?php echo strtoupper($result['correct_answer']); ?>) <?php echo htmlspecialchars($correctText); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="results-actions">
                        <a href="quiz.php?new=1&category=<?php echo urlencode($quizCategory); ?>" class="btn btn-primary">
                            🔄 Try Again
                        </a>
                        <a href="quiz.php?new=1" class="btn btn-secondary">
                            📝 New Quiz
                        </a>
                        <a href="progress.php" class="btn btn-secondary">
                            📊 View Progress
                        </a>
                        <a href="dashboard.php" class="btn btn-secondary">
                            🏠 Dashboard
                        </a>
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
