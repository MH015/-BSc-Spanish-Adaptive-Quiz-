<?php
/**

 * RESULTS PAGE

 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679

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

// Adaptive difficulty adjustment
$oldLevel = $quizDifficulty;
if ($percentage >= 75) {
    $newLevel = ($quizDifficulty === 'easy') ? 'medium' : (($quizDifficulty === 'medium') ? 'hard' : 'hard');
    $feedbackType = 'success';
    $feedbackTitle = '🎉 Level Up!';
    $feedbackMessage = "Excellent work! Your difficulty has been increased from " . ucfirst($oldLevel) . " to " . ucfirst($newLevel) . ".";
} elseif ($percentage >= 50) {
    $newLevel = $quizDifficulty;
    $feedbackType = 'maintain';
    $feedbackTitle = '👍 Staying Steady';
    $feedbackMessage = "Good effort! You'll continue at the " . ucfirst($newLevel) . " level to build confidence.";
} else {
    $newLevel = ($quizDifficulty === 'hard') ? 'medium' : (($quizDifficulty === 'medium') ? 'easy' : 'easy');
    $feedbackType = 'decrease';
    $feedbackTitle = '💪 Keep Practising!';
    $feedbackMessage = "Don't worry! Your difficulty has been adjusted from " . ucfirst($oldLevel) . " to " . ucfirst($newLevel) . " to help you improve.";
}

$_SESSION['current_level'] = $newLevel;

// Save to database (only once)
if (!isset($_SESSION['results_saved'])) {
    // Update user level
    db_execute(
        "UPDATE users SET current_level = ? WHERE user_id = ?",
        [$newLevel, $user['user_id']]
    );
    
    // Save quiz attempt
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
    
    $_SESSION['results_saved'] = true;
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
