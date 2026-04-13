<?php
/**

 * QUIZ PAGE

 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 * PURPOSE:
 * Manages the entire quiz-taking experience: selects questions
 * appropriate to the user's difficulty level, displays them one
 * at a time, stores answers in the session, and handles navigation
 * between questions and final submission.
 * 
 * HOW THIS PAGE WORKS:
 * 1. Requires the user to be logged in (calls require_login()).
 * 2. Reads quiz parameters: category from the URL (?category=...)
 *    and difficulty level from the user's current_level in the DB.
 * 3. If no quiz is active in the session, starts a new one by
 *    querying the questions table for 10 random questions matching
 *    the selected category and difficulty level (ORDER BY RAND()).
 * 4. Stores the questions and user answers in $_SESSION so the
 *    quiz state persists across page loads.
 * 5. Displays one question at a time with four multiple-choice
 *    options. A progress bar shows completion (e.g. "Question 3/10").
 * 6. JavaScript handles answer selection, navigation between
 *    questions (next/previous), and visual feedback on click.
 * 7. On the final question, a "Submit Quiz" button appears.
 * 8. When submitted, the form POSTs all selected answers to
 *    results.php for scoring and adaptive difficulty adjustment.
 *
 * KEY FUNCTIONS USED:
 * - startNewQuiz()    → fetches questions from DB, stores in session
 * - require_login()   → redirects to login.php if not authenticated
 * - get_current_user() → retrieves logged-in user data from session/DB
 */

require_once 'includes/auth.php';
require_login();

$user = get_logged_in_user();

// Include enhanced adaptive algorithm for per-category difficulty
require_once 'includes/adaptive_algorithm.php';

// Check if starting a new quiz
if (isset($_GET['new']) || isset($_GET['category'])) {
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    // Use per-category difficulty if a specific category is selected
    if ($category) {
        $difficulty = get_category_difficulty($user['user_id'], $category);
    } else {
        $difficulty = $_SESSION['current_level'] ?? 'easy';
    }
    startNewQuiz($category, $difficulty, 10);
    header('Location: quiz.php');
    exit;
}

// Check if quiz is active
if (!isset($_SESSION['quiz_active']) || !$_SESSION['quiz_active']) {
    header('Location: dashboard.php');
    exit;
}

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['answer']) && isset($_POST['question_index'])) {
        $questionIndex = (int) $_POST['question_index'];
        $answer = $_POST['answer'];
        $_SESSION['quiz_answers'][$questionIndex] = $answer;
    }
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'next':
                $_SESSION['quiz_current'] = min($_SESSION['quiz_current'] + 1, count($_SESSION['quiz_questions']) - 1);
                break;
            case 'previous':
                $_SESSION['quiz_current'] = max($_SESSION['quiz_current'] - 1, 0);
                break;
            case 'goto':
                if (isset($_POST['goto_index'])) {
                    $gotoIndex = (int) $_POST['goto_index'];
                    if ($gotoIndex >= 0 && $gotoIndex < count($_SESSION['quiz_questions'])) {
                        $_SESSION['quiz_current'] = $gotoIndex;
                    }
                }
                break;
            case 'submit':
                header('Location: results.php');
                exit;
        }
    }
    
    header('Location: quiz.php');
    exit;
}

$questions = $_SESSION['quiz_questions'];
$currentIndex = $_SESSION['quiz_current'];
$answers = $_SESSION['quiz_answers'];
$quizCategory = $_SESSION['quiz_category'];
$quizDifficulty = $_SESSION['quiz_difficulty'];

$currentQuestion = $questions[$currentIndex];
$totalQuestions = count($questions);
$progressPercent = (($currentIndex + 1) / $totalQuestions) * 100;
$currentAnswer = isset($answers[$currentIndex]) ? $answers[$currentIndex] : null;
$answeredCount = count($answers);

function startNewQuiz($category, $difficulty, $limit) {
    global $pdo;
    
    unset($_SESSION['results_saved']);
    
    if ($category) {
        $sql = "SELECT * FROM questions WHERE difficulty = ? AND category = ? ORDER BY RAND() LIMIT ?";
        $params = [$difficulty, $category, $limit];
    } else {
        $sql = "SELECT * FROM questions WHERE difficulty = ? ORDER BY RAND() LIMIT ?";
        $params = [$difficulty, $limit];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $questions = $stmt->fetchAll();
    
    $_SESSION['quiz_active'] = true;
    $_SESSION['quiz_questions'] = $questions;
    $_SESSION['quiz_answers'] = [];
    $_SESSION['quiz_current'] = 0;
    $_SESSION['quiz_category'] = $category ?: 'Mixed';
    $_SESSION['quiz_difficulty'] = $difficulty;
    $_SESSION['quiz_start_time'] = time();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - QuizNinja</title>
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
                <div class="quiz-container">
                    
                    <div class="quiz-header">
                        <div class="quiz-info">
                            <h2><?php echo htmlspecialchars($quizCategory); ?> Quiz</h2>
                            <span class="quiz-meta">Question <?php echo $currentIndex + 1; ?> of <?php echo $totalQuestions; ?></span>
                        </div>
                        <span class="badge badge-<?php echo $quizDifficulty; ?>">
                            <?php echo ucfirst($quizDifficulty); ?>
                        </span>
                    </div>
                    
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progressPercent; ?>%;"></div>
                    </div>
                    <p class="progress-text"><?php echo $answeredCount; ?> of <?php echo $totalQuestions; ?> answered</p>
                    
                    <form method="POST" action="quiz.php" id="quiz-form">
                        <input type="hidden" name="question_index" value="<?php echo $currentIndex; ?>">
                        
                        <div class="question-card">
                            <div class="question-number">Question <?php echo $currentIndex + 1; ?></div>
                            <div class="question-text">
                                <?php echo htmlspecialchars($currentQuestion['question_text']); ?>
                            </div>
                            
                            <div class="answer-options">
                                <?php
                                $options = ['a', 'b', 'c', 'd'];
                                foreach ($options as $opt):
                                    $optionKey = 'option_' . $opt;
                                    $isSelected = ($currentAnswer === $opt);
                                ?>
                                <label class="answer-option <?php echo $isSelected ? 'selected' : ''; ?>">
                                    <input type="radio" name="answer" value="<?php echo $opt; ?>" 
                                           <?php echo $isSelected ? 'checked' : ''; ?> style="display:none;">
                                    <span class="answer-letter"><?php echo strtoupper($opt); ?></span>
                                    <span class="answer-text"><?php echo htmlspecialchars($currentQuestion[$optionKey]); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Navigation Buttons -->
                        <div class="quiz-nav">
                            <?php if ($currentIndex > 0): ?>
                                <button type="submit" name="action" value="previous" class="btn btn-secondary">
                                    ← Previous
                                </button>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>
                            
                            <?php if ($currentIndex < $totalQuestions - 1): ?>
                                <button type="submit" name="action" value="next" class="btn btn-primary">
                                    Next →
                                </button>
                            <?php else: ?>
                                <button type="submit" name="action" value="submit" class="btn btn-primary"
                                        onclick="return confirm('Submit your quiz?');">
                                    Submit Quiz
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                </div>
            </div>
        </main>
        
        <footer class="footer">
            <div class="container">
                <p>&copy; 2025 QuizNinja - Adaptive Language Learning | 6COM2018 Final Year Project</p>
            </div>
        </footer>
    </div>
    
    <script>
        document.querySelectorAll('.answer-option').forEach(function(option) {
            option.addEventListener('click', function() {
                document.querySelectorAll('.answer-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
            });
        });
        
        document.addEventListener('keydown', function(e) {
            const keyMap = { 'a': 0, 'b': 1, 'c': 2, 'd': 3 };
            const key = e.key.toLowerCase();
            
            if (keyMap.hasOwnProperty(key) && !e.ctrlKey && !e.metaKey) {
                const options = document.querySelectorAll('.answer-option');
                if (options[keyMap[key]]) {
                    options[keyMap[key]].click();
                }
            }
        });
    </script>
</body>
</html>
