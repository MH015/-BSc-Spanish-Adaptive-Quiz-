<?php
/**
 * LOGIN / REGISTER PAGE
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Include database connection
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$active_tab = 'login';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['action'])) {
        
        // LOGIN
        if ($_POST['action'] === 'login') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {
                $result = login_user($email, $password);
                if ($result['success']) {
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        }
        
        // REGISTER
        if ($_POST['action'] === 'register') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
                $active_tab = 'register';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match.';
                $active_tab = 'register';
            } else {
                $result = register_user($username, $email, $password);
                if ($result['success']) {
                    $success = $result['message'];
                    $active_tab = 'login';
                } else {
                    $error = $result['message'];
                    $active_tab = 'register';
                }
            }
        }
    }
}

// Check for URL messages
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h1>🥷 QuizNinja</h1>
                <p>Learn at your own pace</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="auth-tabs">
                <button class="auth-tab <?php echo $active_tab === 'login' ? 'active' : ''; ?>" 
                        onclick="switchTab('login')">Login</button>
                <button class="auth-tab <?php echo $active_tab === 'register' ? 'active' : ''; ?>" 
                        onclick="switchTab('register')">Register</button>
            </div>
            
            <!-- Login Form -->
            <form method="POST" class="auth-form <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="login-form">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label class="form-label" for="login-email">Email Address</label>
                    <input type="email" id="login-email" name="email" class="form-control" 
                           placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" class="form-control" 
                           placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <!-- Register Form -->
            <form method="POST" class="auth-form <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="register-form">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label class="form-label" for="reg-username">Username</label>
                    <input type="text" id="reg-username" name="username" class="form-control" 
                           placeholder="Choose a username" required minlength="3">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="reg-email">Email Address</label>
                    <input type="email" id="reg-email" name="email" class="form-control" 
                           placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" class="form-control" 
                           placeholder="Create a password" required minlength="8">
                    <span class="form-hint">• At least 8 characters long</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="reg-confirm">Confirm Password</label>
                    <input type="password" id="reg-confirm" name="confirm_password" class="form-control" 
                           placeholder="Confirm your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            
            <p class="text-center text-muted text-small mt-6">
                BSc Computer Science Project<br>
                Matthew Holness • 22068679
            </p>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            // Update tabs
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            
            // Activate selected tab
            if (tab === 'login') {
                document.querySelector('.auth-tab:first-child').classList.add('active');
                document.getElementById('login-form').classList.add('active');
            } else {
                document.querySelector('.auth-tab:last-child').classList.add('active');
                document.getElementById('register-form').classList.add('active');
            }
        }
    </script>
</body>
</html>
