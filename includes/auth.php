<?php
/**
 * AUTHENTICATION FUNCTIONS
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 * PURPOSE:
 * Centralises all user authentication logic: registration, login,
 * logout, session management, and access control. Called by every
 * protected page via require_login() to enforce authentication.
 * 
 * HOW IT WORKS:
 * - register_user(): Validates input (username length, email format,
 *   password minimum 8 chars), checks for duplicate emails, hashes
 *   the password with bcrypt via password_hash(), and inserts a new
 *   user record with 'easy' as the default difficulty level.
 *
 * - login_user(): Retrieves the user by email, verifies the password
 *   against the stored bcrypt hash using password_verify(), then
 *   creates a session. Calls session_regenerate_id(true) to prevent
 *   session fixation attacks. Stores user_id, username, email,
 *   current_level, and last_activity in $_SESSION.
 *
 * - logout_user(): Unsets all session variables, deletes the session
 *   cookie, and destroys the session.
 *
 * - require_login(): Called at the top of every protected page.
 *   Checks if the user is logged in and if the session has timed
 *   out (30-minute inactivity limit). Redirects to login.php if
 *   either check fails.
 *
 * - get_user_data(): Returns the full user record from the database
 *   for the currently logged-in user.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_connect.php';

define('SESSION_TIMEOUT', 1800);

function register_user($username, $email, $password) {
    global $pdo;
    
    $username = trim($username);
    $email = trim(strtolower($email));
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['success' => false, 'message' => 'Username must be between 3 and 50 characters.', 'user_id' => null];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.', 'user_id' => null];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.', 'user_id' => null];
    }
    
    $existing = db_query_single("SELECT user_id FROM users WHERE email = ?", [$email]);
    
    if ($existing) {
        return ['success' => false, 'message' => 'An account with this email already exists.', 'user_id' => null];
    }
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    $sql = "INSERT INTO users (username, email, password_hash, current_level, created_at) 
            VALUES (?, ?, ?, 'easy', NOW())";
    
    $success = db_execute($sql, [$username, $email, $password_hash]);
    
    if ($success) {
        return ['success' => true, 'message' => 'Registration successful! You can now log in.', 'user_id' => (int) db_last_insert_id()];
    } else {
        return ['success' => false, 'message' => 'Registration failed. Please try again.', 'user_id' => null];
    }
}

function login_user($email, $password) {
    $email = trim(strtolower($email));
    
    $user = db_query_single(
        "SELECT user_id, username, email, password_hash, current_level FROM users WHERE email = ?", 
        [$email]
    );
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['current_level'] = $user['current_level'];
    $_SESSION['logged_in'] = true;
    $_SESSION['last_activity'] = time();
    
    return ['success' => true, 'message' => 'Login successful!'];
}

function logout_user() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

function is_logged_in() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }
    
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            logout_user();
            return false;
        }
        $_SESSION['last_activity'] = time();
    }
    
    return true;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php?error=Please log in to access this page.');
        exit;
    }
}

function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'current_level' => $_SESSION['current_level']
    ];
}

function update_user_level($user_id, $new_level) {
    $valid_levels = ['easy', 'medium', 'hard'];
    if (!in_array($new_level, $valid_levels)) {
        return false;
    }
    
    $success = db_execute(
        "UPDATE users SET current_level = ? WHERE user_id = ?",
        [$new_level, $user_id]
    );
    
    if ($success && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
        $_SESSION['current_level'] = $new_level;
    }
    
    return $success;
}

function get_user_stats($user_id) {
    $total_quizzes = db_count('quiz_attempts', 'user_id = ?', [$user_id]);
    
    $avg_result = db_query_single(
        "SELECT AVG(percentage) as avg_score FROM quiz_attempts WHERE user_id = ?",
        [$user_id]
    );
    $avg_score = $avg_result ? round($avg_result['avg_score'], 1) : 0;
    
    $recent = db_query_single(
        "SELECT percentage, category, difficulty_level FROM quiz_attempts 
         WHERE user_id = ? ORDER BY attempt_date DESC LIMIT 1",
        [$user_id]
    );
    
    $current_level = $_SESSION['current_level'] ?? 'easy';
    
    return [
        'total_quizzes' => $total_quizzes,
        'average_score' => $avg_score,
        'current_level' => $current_level,
        'recent_score' => $recent ? $recent['percentage'] : null,
        'recent_category' => $recent ? $recent['category'] : null,
        'recent_difficulty' => $recent ? $recent['difficulty_level'] : null
    ];
}
?>
